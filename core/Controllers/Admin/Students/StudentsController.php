<?php
namespace DelightEDU\Controllers\Admin\Students;
use DelightEDU\Models\StudentModel;

class StudentsController {

    private $model;

    public function __construct() {
        $this->model = new StudentModel();
    }

    public function render_students_page() {
        $student_members = $this->model->get_all();

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

    public function save_student_enrollment($post_data) {
        // 1. Create WP User for the Student
        $student_wp_id = $this->create_wp_account($post_data['email'], 'dedu_student');

        // 2. Save to dedu_students table
        $student_id = $this->student_model->create([
            'wp_user_id'   => $student_wp_id,
            'admission_no' => $post_data['admission_no'],
            'class_id'     => $post_data['class_id'],
        ]);

        // 3. Handle the Parent (The "Student-First" Link)
        if ( $post_data['parent_type'] === 'new' ) {
            // Create new parent first...
            $parent_id = $this->parent_model->create_with_wp_user($post_data['parent_info']);
        } else {
            // Use existing parent ID from a dropdown
            $parent_id = $post_data['existing_parent_id'];
        }

        // 4. Update the relationship table
        $this->rel_model->link($student_id, $parent_id, $post_data['relationship']);
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
            $result = $model->update($student_id);
            $redirect_msg = 'student_updated';
        } else {
            // CREATE NEW
            $result = $model->create();
            $redirect_msg = 'student_created';
        }

        // 2. Redirect back to the directory with a success message
        $redirect_url = admin_url('admin.php?page=dedu-student&message=' . $redirect_msg);
        wp_redirect($redirect_url);
        exit;
    }

}