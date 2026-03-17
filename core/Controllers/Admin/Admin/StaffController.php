<?php
namespace DelightEDU\Controllers\Admin\Admin;

use DelightEDU\Models\StaffModel;
use DelightEDU\Models\StaffRole;
use DelightEDU\Roles\PermissionsRegistry;

class StaffController {
    private $model;

    public function __construct() {
        $this->model = new StaffModel();
        add_action( 'admin_post_dedu_save_staff', [ $this, 'handle_save_staff' ] );
    }

    public function get_staff_schema() {
        return [
            'first_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'middle_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'last_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'profile_picture'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'email'         => ['filter' => 'sanitize_email',      'format' => '%s'],
            'password'         => ['filter' => 'wp_hash_password',      'format' => '%s'],
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
    public function sanitize_data() {
        $schema = $this->get_staff_schema();
        $data_to_save = [];
        $format_array = [];

         // Handle File Upload
        $profile_picture_url = '';
        if (!empty($_FILES['staff_photo']['name'])) {
            $profile_picture_url = $this->upload_photo_get_its_url('staff_photo');
        }

        foreach ($schema as $column => $rules) {
            if ($column ==='profile_picture' && $profile_picture_url) {
                $data_to_save[$column] = $profile_picture_url;
                $format_array[] = $rules['format']; 
                continue;
            }
            if ($column ==='role_id' && !isset($_POST[$column]) ) {
                $data_to_save[$column] = call_user_func($rules['filter'], $_POST[$column]);
                $format_array[] = $rules['format']; 
                continue;
            }
            if (isset($_POST[$column])) {
                $data_to_save[$column] = call_user_func($rules['filter'], $_POST[$column]);
                $format_array[] = $rules['format']; // Automatically adds the right %s, %d, or %f
            }
        }
        return [$data_to_save, $format_array];
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
        // $model = new StaffModel();
        
        // Fetch the main record
        $staff = $this->model->get_staff_by_id($staff_id);
        
        if (!$staff) {
            wp_send_json_error('Staff member not found');
        }

        // Fetch this staff's specific overrides from dedu_staff_capabilities
        $overrides = $this->model->get_staff_permissions($staff_id);

        wp_send_json_success([
            'staff' => $staff,
            'permissions' => $overrides // Array of slugs: ['view_students', 'add_staff']
        ]);
    }

    public function handle_save_staff() {
        // 1. Security Check
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'dedu_staff_nonce')) {
            wp_die('Security check failed');
        }

        $data = $this->sanitize_data();
    
        $model = new StaffModel();
        $staff_id = isset($_POST['staff_db_id']) ? absint($_POST['staff_db_id']) : 0;

        if ($staff_id > 0) {
            //UPDATE EXISTING
            $result = $model->update($staff_id, $data[0]);
            $redirect_msg = 'staff_updated';
        } else {
            // CREATE NEW
            $result = $model->create($data);
            $redirect_msg = 'staff_created';
        }

        // 2. Redirect back to the directory with a success message
        $redirect_url = admin_url('admin.php?page=dedu-staff&message=' . $redirect_msg);
        wp_redirect($redirect_url);
        exit;
    }
}