<?php
namespace DelightEDU\Controllers\Admin\Admin;

use DelightEDU\Models\StaffModel;
use DelightEDU\Models\StaffRole;
use DelightEDU\Roles\PermissionsRegistry;
use DelightEDU\Assets\Admin\Helpers;

class StaffController {
    private $model;

    public function __construct() {
        $this->model = new StaffModel();
    }

    public function get_staff_schema() {
        return [
            'profile_picture_id'    => ['filter' => 'absint',  'format' => '%d'],
            'first_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'middle_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'last_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'email'         => ['filter' => 'sanitize_email',      'format' => '%s'],
            'phone'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'gender'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'marital_status'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'date_of_birth'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'role_id'       => ['filter' => 'absint',              'format' => '%d'],
            'position'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'working_hours'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'is_teacher'       => ['filter' => 'absint',              'format' => '%d'],
            'salary_amount' => ['filter' => 'floatval',            'format' => '%f'],
            'joining_date'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'class_id'       => ['filter' => 'absint',              'format' => '%d'],
        ];
    }
    

    public function render_staff_page() {
        $staff_members = $this->model->get_all();
        $role_model    = new StaffRole();
        $roles         = $role_model->get_all();

        // 1. Pull grouped permissions from your Registry
        $permission_groups = PermissionsRegistry::get_all();

        // 2. Fetch role-to-capability mapping for the JS "Auto-check" feature
        global $wpdb;
        $role_caps_raw = $wpdb->get_results("SELECT role_id, capability FROM {$wpdb->prefix}dedu_role_capabilities");
        $role_mapping = [];
        foreach ($role_caps_raw as $row) {
            $role_mapping[$row->role_id][] = $row->capability;
        }

        // 3. Get Classes for the "Form Master" dropdown
        global $wpdb;
        $table_classes = $wpdb->prefix . 'dedu_classes';
        $classes = $wpdb->get_results("SELECT id, class_name FROM $table_classes ORDER BY numeric_name ASC");
        $all_sections = $wpdb->get_results("SELECT id, section_name, class_id FROM {$wpdb->prefix}dedu_sections");
        // Group sections by class_id for fast JS lookup
        $sections_by_class = [];
        foreach ($all_sections as $sec) {
            $sections_by_class[$sec->class_id][] = $sec;
        }

        // 4. Define metadata for the form (Gender/Status/Marital Status)
        // This keeps the Template clean
        $form_meta = [
            'genders' => ['male' => 'Male', 'female' => 'Female'],
            'marital_statuses' => ['single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed'],
            'working_hours' => ['full-time' => 'Full-time', 'part-time' => 'Part-time']
        ];

        // 5. Pass everything to the template
        include DEDU_PATH . 'templates/admin/admin/staff-list-form-toggle.php';
    }

    public function ajax_get_staff_details() {
        // Security check
        check_ajax_referer('dedu_staff_nonce', 'nonce');

        if (!current_user_can('manage_options')) { // Or your custom 'edit_staff' perm
            wp_send_json_error('Unauthorized');
        }

        $staff_id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        
        // Fetch the main record
        $staff = $this->model->get_staff_by_id($staff_id);
        
        if (!$staff) {
            wp_send_json_error('Staff member not found');
        }

        // Fetch this staff's specific permissions from dedu_staff_capabilities
        $permissions = $this->model->get_staff_permissions($staff_id);
        
        $photo_url = isset($staff['profile_picture_id']) ? wp_get_attachment_url($staff['profile_picture_id']) : $default_avatar_url;
        $staff['photo_url'] = $photo_url;

        wp_send_json_success([
            'staff' => $staff,
            'permissions' => $permissions // Array of slugs: ['view_students', 'add_staff']
        ]);
    }

    //create and update method
    public function handle_save_staff() {
        // 1. Security Check
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'dedu_staff_nonce')) {
            wp_die('Security check failed');
        }
        
        $model = $this->model;
        $staff_id = isset($_POST['staff_db_id']) ? absint($_POST['staff_db_id']) : 0;

        if ($staff_id > 0) {
            //UPDATE EXISTING
            $result = $model->update($staff_id);
            $redirect_msg = 'staff_updated';
        } else {
            // CREATE NEW
            $result = $model->create();
            $redirect_msg = 'staff_created';
        }

        // 2. Redirect back to the directory with a success message
        $redirect_url = admin_url('admin.php?page=dedu-staff&message=' . $redirect_msg);
        wp_redirect($redirect_url);
        exit;
    }

    public function handle_delete_staff() {
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        // 1. Security Check (Nonce)
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'dedu_delete_staff_' . $id ) ) {
            wp_die( 'Security check failed.' );
        }

        // 2. Authorization
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }
        $referer = wp_get_referer();

        $staff_model = new StaffModel();
        if ( $staff_model->delete( $id ) ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', 'staff_deleted', $referer ) : admin_url( 'admin.php?page=dedu-roles&message=staff_deleted' );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg('error','delete_failed', $referer) );
        }
        exit;
    }
}