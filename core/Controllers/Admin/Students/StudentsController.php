<?php
namespace DelightEDU\Controllers\Admin\Students;
use DelightEDU\Models\StudentModel;

class StudentsController {

    private $model;

    public function __construct() {
        $this->model = new StudentModel();
    }

    public function render_students_page() {
        $students = $this->model->get_all();

        // Get Classes for the "Form" dropdown
        global $wpdb;
        $table_classes = $wpdb->prefix . 'dedu_classes';
        $classes = $wpdb->get_results("SELECT id, class_name FROM $table_classes ORDER BY numeric_name ASC");
        $all_sections = $wpdb->get_results("SELECT id, section_name, class_id FROM {$wpdb->prefix}dedu_sections");
        // Group sections by class_id for fast JS lookup
        $sections_by_class = [];
        foreach ($all_sections as $sec) {
            $sections_by_class[$sec->class_id][] = $sec;
        }

        // Define metadata for the form (Gender/Status/Marital Status)
        // This keeps the Template clean
        $form_meta = [
            'genders' => ['male' => 'Male', 'female' => 'Female'],
            'marital_statuses' => ['single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed'],
            'working_hours' => ['full-time' => 'Full-time', 'part-time' => 'Part-time'],
            'blood_group' => ['O+' => 'O+', 'O-' => 'O-', 'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-'],
            'relationship' => ['father' => 'Father', 'mother' => 'Mother', 'others' => 'Others']
        ];

        // 5. Pass everything to the template
        include DEDU_PATH . 'templates/admin/students/students-list-form-toggle.php';
    }

    public function handle_save_student() {
        // 1. Security Check
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'dedu_student_nonce')) {
            wp_die('Security check failed');
        }
        
        $model = $this->model;
        $student_id = isset($_POST['student_db_id']) ? absint($_POST['student_db_id']) : 0;

        if ($student_id > 0) {
            //UPDATE EXISTING
            $success = $model->update($student_id);
            $message = 'student_updated';
        } else {
            // CREATE NEW
            $success = $model->create();
            $message = 'student_created';
        }

        // This gets the URL the user was on before hitting "Save"
        $referer = wp_get_referer();

        if ( $success ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', $message, $referer ) : admin_url( 'admin.php?page=dedu-student&message=' . $message );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg( 'error', 'save_failed', $referer ) );
        }
        exit;
    }

    public function handle_delete_student(){
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        // 1. Security Check (Nonce)
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'dedu_delete_student_' . $id ) ) {
            wp_die( 'Security check failed.' );
        }

        // 2. Authorization
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }
        global $wpdb;
        $referer = wp_get_referer();

        $student_model = new StudentModel();
        
        

        if ( $student_model->delete( $id ) ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', 'student_deleted', $referer ) : admin_url( 'admin.php?page=dedu-roles&message=student_deleted' );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg('error','delete_failed', $referer) );
        }
        exit;
    }

    public function ajax_get_student_details() {
        // Security check
        check_ajax_referer('dedu_student_nonce', 'nonce');

        if (!current_user_can('manage_options')) { // Or your custom 'edit_student' perm
            wp_send_json_error('Unauthorized');
        }

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        
        // Fetch the main record
        $student = $this->model->get_student_by_id($id);
        
        if (!$student) {
            wp_send_json_error('student member not found');
        }

        global $wpdb;
        $table_link = $wpdb->prefix . 'dedu_parents_student_mapping';
        $parents = $wpdb->get_col($wpdb->prepare(
            "SELECT parent_id FROM $table_link WHERE student_id = %d", 
            $id 
        ));
        
        $photo_url = isset($student['profile_picture_id']) ? wp_get_attachment_url($student['profile_picture_id']) : '';
        $student['photo_url'] = $photo_url;

        wp_send_json_success([ 
            'student' => $student, 
            'parents' => $parents 
        ]);
    }

}