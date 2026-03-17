<?php
namespace DelightEDU\Controllers\Admin;

use DelightEDU\Models\StaffRole;
use DelightEDU\Models\Classes; 
use DelightEDU\Models\SubjectModel;
use DelightEDU\Models\StaffModel;

class PostHandler {

    public function __construct() {
        // This hook matches the <input type="hidden" name="action" value="dedu_save_role">
        add_action( 'admin_post_dedu_save_role', [ $this, 'save_staff_role' ] );
        add_action( 'admin_post_dedu_delete_role', [ $this, 'delete_staff_role' ] );
        add_action('admin_post_dedu_bulk_action_roles', [$this, 'handle_bulk_actions']);

        // Updated Academic Hook: Handling both Class and its Sections at once
        add_action( 'admin_post_dedu_save_class_complex', [ $this, 'save_class_with_sections' ] );
        add_action( 'admin_post_dedu_delete_class', [ $this, 'delete_class' ] );
        add_action('wp_ajax_dedu_save_class_ajax', [$this, 'handle_save_class_ajax']);
        // If you want it to work for non-admins (usually not for deletes, but for completeness)
        // add_action( 'admin_post_nopriv_dedu_delete_class', [ $this, 'delete_class' ] );

        // 2. Added Subject Hooks
        add_action( 'admin_post_dedu_save_master_subject', [ $this, 'handle_save_master_subject' ] );
        add_action( 'admin_post_dedu_delete_master_subject', [ $this, 'delete_master_subject' ] );
        add_action( 'admin_post_dedu_assign_subject_to_class', [ $this, 'handle_assign_subject_to_class' ] );
        add_action('admin_post_dedu_bulk_save_curriculum', [$this, 'handle_bulk_save_class_subjects']);

        // add_action( 'admin_post_dedu_save_staff', [ $this, 'handle_save_staff' ] );
        add_action( 'admin_post_dedu_delete_staff', [ $this, 'handle_delete_staff' ] );
    }

    private function upload_photo_get_its_url($file_key) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload($file_key, 0); 

        if (!is_wp_error($attachment_id)) {
            return wp_get_attachment_url($attachment_id);
        }
        
        return false; // Return false so we know it failed
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

    public function save_staff_role() {
        // 1. Security Check
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'dedu_role_nonce' ) ) {
            wp_die( 'Security check failed.' );
        }

        // 2. Permission Check
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'You do not have permission to do this.' );
        }

        $role_id   = isset( $_POST['role_id'] ) ? absint( $_POST['role_id'] ) : 0;
        $role_name = sanitize_text_field( $_POST['role_name'] );
        $caps      = isset( $_POST['capabilities'] ) ? (array) $_POST['capabilities'] : [];

        $role_model = new StaffRole();

        if ( $role_id > 0 ) {
            // UPDATE existing
            $success = $role_model->update( $role_id, $role_name, $caps );
            $message = 'role_updated';
        } else {
            // CREATE new
            $success = $role_model->create( $role_name, $caps );
            $message = 'role_created';
        }
        // This gets the URL the user was on before hitting "Save"
        $referer = wp_get_referer();

        if ( $success ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', $message, $referer ) : admin_url( 'admin.php?page=dedu-roles&message=' . $message );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg( 'error', 'save_failed', $referer ) );
        }

        
        exit;
    }

    public function delete_staff_role() {
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        // 1. Security Check (Nonce)
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'dedu_delete_role_' . $id ) ) {
            wp_die( 'Security check failed.' );
        }

        // 2. Authorization
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }
        $referer = wp_get_referer();

        $role_model = new StaffRole();
        if ( $role_model->delete( $id ) ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', 'role_deleted', $referer ) : admin_url( 'admin.php?page=dedu-roles&message=' . 'role_deleted' );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg('error','delete_failed', $referer) );
        }
        exit;
    }

    public function handle_bulk_actions() {
        check_admin_referer('dedu_bulk_roles_action', 'dedu-role-nonce');

        $action   = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
        $role_ids = isset($_POST['role_ids']) ? explode(',', $_POST['role_ids']) : [];
        $role_ids = array_map('absint', $role_ids);

        if (empty($role_ids)) {
            wp_redirect(admin_url('admin.php?page=dedu-staff-roles&status=error'));
            exit;
        }

        $role_model = new StaffRole();

        if ('delete' === $action) {
            $count = 0;
            foreach ($role_ids as $id) {
                if ($role_model->delete($id)) {
                    $count++;
                }
            }
            wp_redirect(admin_url("admin.php?page=dedu-staff-roles&message=bulk_deleted&count=$count"));
            exit;
        }

        // Redirect back if no valid action
        wp_redirect(admin_url('admin.php?page=dedu-staff-roles'));
        exit;
    }

    public function save_class_with_sections() {
        // 1. Security & Permission Check
        if ( ! isset( $_POST['dedu_nonce'] ) || ! wp_verify_nonce( $_POST['dedu_nonce'], 'dedu_class_complex_action' ) ) {
            wp_die( 'Security check failed.' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }

        $class_id   = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;
        $class_name = sanitize_text_field( $_POST['class_name'] );
        $numeric_name = absint( $_POST['numeric_name']);
        $section_names = isset($_POST['sections']) ? (array) $_POST['sections'] : [];
        $categories    = isset($_POST['section_category']) ? (array) $_POST['section_category'] : [];

        $class_model = new Classes();
        
        if ( $class_id > 0 ) {
            // UPDATE existing
            $success = $class_model->update( $class_id, $class_name, $numeric_name, $section_names, $categories );
            $message = 'class_updated';
        } else {
            // CREATE new
            $success = $class_model->create( $class_name, $numeric_name, $section_names, $categories );
            $message = 'class_created';
        }

        $referer = wp_get_referer();

        if ( $success ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', $message, $referer ) : admin_url( 'admin.php?page=dedu-classes-sections&message=' . $message );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg( 'error', 'save_failed', $referer ) );
        }    

        // if ( $success ) {
        //     wp_redirect( admin_url( 'admin.php?page=dedu-classes-sections&status=success' ) );
        // } else {
        //     wp_redirect( admin_url( 'admin.php?page=dedu-classes-sections&status=error' ) );
        // }
        exit;
    }

    public function delete_class() {
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        // 1. Security Check (Nonce)
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'dedu_delete_class_' . $id ) ) {
            wp_die( 'Security check failed.' );
        }

        // 2. Authorization
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }
        $referer = wp_get_referer();

        $base_url = $referer ? $referer : admin_url( 'admin.php?page=dedu-roles' ); // Fallback
        $class_model = new Classes();

        if ( $class_model->delete( $id ) ) {
            wp_redirect( add_query_arg( 'message', 'class_deleted', $base_url ) );
        } else {
            wp_redirect( add_query_arg( 'error', 'delete_failed', $base_url ) );
        }
        exit;
    }

    public function handle_save_class_ajax() {
        // 1. Security Check
        check_ajax_referer('dedu_class_complex_action', 'dedu_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions.']);
        }

        $class_model = new \DelightEDU\Models\Classes();
        $section_model = new \DelightEDU\Models\Section();

        // 2. Create the Class
        $class_id = $class_model->create([
            'class_name'   => $_POST['class_name'],
            'numeric_name' => $_POST['numeric_name']
        ]);

        if ($class_id) {
            // 3. Create the Sections
            $section_names = isset($_POST['sections']) ? (array) $_POST['sections'] : [];
            $categories    = isset($_POST['cate']) ? (array) $_POST['cate'] : [];

            foreach ($section_names as $index => $name) {
                if (!empty($name)) {
                    $section_model->create([
                        'section_name' => $name,
                        'cate'         => isset($categories[$index]) ? $categories[$index] : '',
                        'class_id'     => $class_id
                    ]);
                }
            }

            // 4. GENERATE THE UPDATED TABLE HTML
            // This avoids manual string building in JavaScript
            $all_classes = $class_model->get_all_with_sections();
            
            ob_start();
            // We include a partial file that only contains the <tr> loops
            include DEDU_PATH . 'templates/admin/partials/class-table-rows.php';
            $table_html = ob_get_clean();

            wp_send_json_success(['html' => $table_html]);
        }

        wp_send_json_error(['message' => 'Failed to save class.']);
    }

     

    /**
     * 3. NEW SUBJECT METHODS
     */

    public function handle_save_master_subject() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'dedu_master_subject_nonce' ) ) {
            wp_die( 'Security check failed.' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }

        $name = sanitize_text_field($_POST['subject_name']);
        $type = sanitize_text_field($_POST['subject_type']);
        $master_subject_id   = isset( $_POST['master_subject_id'] ) ? absint( $_POST['master_subject_id'] ) : 0;
        
        $subject_model = new SubjectModel();
        if ($master_subject_id > 0) {
            $success = $subject_model->update_master_subject($master_subject_id, $name, $type);
            $message = 'subject_updated';
            // $status = $success ? 'subject_updated' : 'update_failed';
        } else {
            $success = $subject_model->create_master_subject($name, $type);
            $message = 'subject_created';
            // $status = $success ? 'subject_created' : 'save_failed';
        }
        
        $referer = wp_get_referer();        
        if ( $success ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', $message, $referer ) : admin_url( 'admin.php?page=dedu-subjects&message=' . $message );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg( 'error', 'save_failed', $referer ) );
        }
        exit;
    }

    public function delete_master_subject() {
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        // 1. Security Check (Nonce)
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'dedu_delete_master_subject_' . $id ) ) {
            wp_die( 'Security check failed.' );
        }

        // 2. Authorization
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }
        $referer = wp_get_referer();

        $subject_model = new SubjectModel();
        if ( $subject_model->delete_master_subject( $id ) ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', 'subject_deleted', $referer ) : admin_url( 'admin.php?page=dedu-subjects&message=' . 'subject_deleted' );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg('error','delete_failed', $referer) );
        }
        exit;
    }

    public function handle_assign_subject_to_class() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'dedu_assign_subject_nonce' ) ) {
            wp_die( 'Security check failed.' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }

        $class_id   = absint($_POST['class_id']);
        $subject_id = absint($_POST['subject_id']);
        $code       = sanitize_text_field($_POST['subject_code']);
        $year       = dedu_get_current_year(); // Global helper

        $subject_model = new SubjectModel();
        $success = $subject_model->link_to_class($class_id, $subject_id, $year, $code);

        $referer = wp_get_referer();
        $status = $success ? 'subject_assigned' : 'assignment_failed';

        wp_redirect( add_query_arg( 'message', $status, $referer ) );
        exit;
    }


    public function handle_bulk_save_class_subjects() {
        // 1. Security & Permissions
        check_admin_referer('dedu_bulk_curriculum_nonce');
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access.');
        }

        global $wpdb;

        // 2. Setup Variables
        $class_id       = absint($_POST['class_id']);
        $subjects_input = isset($_POST['subjects']) ? $_POST['subjects'] : [];
        $current_year   = \dedu_get_current_year();
        
        $table_main     = $wpdb->prefix . 'dedu_class_subjects';
        $table_sections = $wpdb->prefix . 'dedu_subject_sections';
        $table_teachers = $wpdb->prefix . 'dedu_subject_teachers';

        /**
         * 3. PREPARATION: Get currently assigned subject IDs for this class.
         * This helps us know which ones to DELETE if they were removed from the form.
         */
        $existing_assignments = $wpdb->get_results($wpdb->prepare(
            "SELECT id, subject_id FROM $table_main WHERE class_id = %d AND academic_year = %s",
            $class_id, $current_year
        ), OBJECT_K); // Keyed by the 'id' of the record

        $processed_ids = []; // We will store IDs we keep/update here

        // 4. THE LOOP: Process each subject from the form
        if (is_array($subjects_input)) {
            foreach ($subjects_input as $sub) {
                $subject_id = isset($sub['id']) ? absint($sub['id']) : 0;
                if ($subject_id === 0) continue;

                // Check if this subject is already in the database for this class/year
                $curriculum_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $table_main WHERE class_id = %d AND subject_id = %d AND academic_year = %s",
                    $class_id, $subject_id, $current_year
                ));

                if ($curriculum_id) {
                    // UPDATE existing record
                    $wpdb->update($table_main, 
                        ['subject_code' => sanitize_text_field($sub['code'] ?? '')],
                        ['id' => $curriculum_id],
                        ['%s'], ['%d']
                    );
                } else {
                    // INSERT new record
                    $wpdb->insert($table_main, [
                        'class_id'      => $class_id,
                        'subject_id'    => $subject_id,
                        'subject_code'  => sanitize_text_field($sub['code'] ?? ''),
                        'academic_year' => $current_year
                    ], ['%d', '%d', '%s', '%s']);
                    $curriculum_id = $wpdb->insert_id;
                }

                $processed_ids[] = $curriculum_id;

                /**
                 * 5. PIVOT TABLES: Sync Teachers and Sections
                 * For simplicity and to avoid duplicates, we clear pivots for THIS specific subject 
                 * and re-add them. This is safer than clearing the whole class.
                 */
                $wpdb->delete($table_sections, ['curriculum_id' => $curriculum_id], ['%d']);
                $wpdb->delete($table_teachers, ['curriculum_id' => $curriculum_id], ['%d']);

                // Insert Sections
                if (!empty($sub['sections']) && is_array($sub['sections'])) {
                    foreach ($sub['sections'] as $sec_id) {
                        $wpdb->insert($table_sections, [
                            'curriculum_id' => $curriculum_id,
                            'section_id'    => absint($sec_id)
                        ], ['%d', '%d']);
                    }
                }

                // Insert Teachers
                if (!empty($sub['teachers']) && is_array($sub['teachers'])) {
                    foreach ($sub['teachers'] as $staff_id) {
                        $wpdb->insert($table_teachers, [
                            'curriculum_id' => $curriculum_id,
                            'staff_id'      => absint($staff_id)
                        ], ['%d', '%d']);
                    }
                }
            }
        }

        /**
         * 6. CLEANUP: Delete any subjects that were NOT in the form submission
         * (Meaning the user clicked the 'X' button to remove them)
         */
        if (!empty($processed_ids)) {
            $placeholders = implode(',', array_fill(0, count($processed_ids), '%d'));
            
            // Find orphans (subjects belonging to this class/year NOT in our processed list)
            $orphans = $wpdb->get_col($wpdb->prepare(
                "SELECT id FROM $table_main WHERE class_id = %d AND academic_year = %s AND id NOT IN ($placeholders)",
                array_merge([$class_id, $current_year], $processed_ids)
            ));

            if (!empty($orphans)) {
                $orphan_placeholders = implode(',', array_fill(0, count($orphans), '%d'));
                // Clear their pivots first (Good database hygiene)
                $wpdb->query($wpdb->prepare("DELETE FROM $table_sections WHERE curriculum_id IN ($orphan_placeholders)", ...$orphans));
                $wpdb->query($wpdb->prepare("DELETE FROM $table_teachers WHERE curriculum_id IN ($orphan_placeholders)", ...$orphans));
                // Delete the main records
                $wpdb->query($wpdb->prepare("DELETE FROM $table_main WHERE id IN ($orphan_placeholders)", ...$orphans));
            }
        } 

        // 7. Success Redirect back to the list
        wp_redirect(admin_url('admin.php?page=dedu-subjects-assign&message=class_subjects_assigned'));
        exit;
    }

    // public function handle_save_staff() {
    //     // 1. Security Check
    //     if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'dedu_staff_nonce')) {
    //         wp_die('Security check failed');
    //     }

    //     $data = $this->sanitize_data();
    
    //     $model = new StaffModel();
    //     $staff_id = isset($_POST['staff_db_id']) ? absint($_POST['staff_db_id']) : 0;

    //     if ($staff_id > 0) {
    //         //UPDATE EXISTING
    //         $result = $model->update($staff_id, $data[0]);
    //         $redirect_msg = 'staff_updated';
    //     } else {
    //         // CREATE NEW
    //         $result = $model->create($data);
    //         $redirect_msg = 'staff_created';
    //     }

    //     // 2. Redirect back to the directory with a success message
    //     $redirect_url = admin_url('admin.php?page=dedu-staff&message=' . $redirect_msg);
    //     wp_redirect($redirect_url);
    //     exit;
    // }

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