<?php
namespace DelightEDU\Models;

use DelightEDU\Assets\Admin\Helpers;
use DelightEDU\Models;

class StudentModel {
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'dedu_students';
    }

    public function get_student_schema() {
        return [
            'profile_picture_id'    => ['filter' => 'absint',  'format' => '%d'],
            'first_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'middle_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'last_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'email'         => ['filter' => 'sanitize_email',      'format' => '%s'],
            'phone'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'address'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'gender'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'marital_status'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'date_of_birth'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'class_id'       => ['filter' => 'absint',              'format' => '%d'],
            'section_id'       => ['filter' => 'absint',              'format' => '%d'],
            'position'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'admission_no'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'roll_no'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'blood_group'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'status' => ['filter' => 'sanitize_text_field',            'format' => '%s'],
            'joining_date'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
        ];
    }

    public function create($data) {
        global $wpdb;
        $user = [];
        $user['email'] = sanitize_user($_POST['email']); // Or generate a specific format
        $user['password'] = !empty($_POST['password']) ? $_POST['password'] : wp_generate_password();
        
        // 1. Create the WordPress User first
        $user_id = Helpers::create_wp_user($user, "dedu_student");
        if (is_wp_error($user_id)) return $user_id;
        
        // Sanitize the data
        $schema = $this->get_student_schema();
        $photoKey = !isset($_POST['student_photo']) ? 'student_photo':'';
        $sanitized_data = Helpers::sanitize_data($schema, $user_id, $photoKey);

        //  Generate student ID Number
        $prefix = get_option('dedu_student_id_prefix', 'EDU');
        $prefix = rtrim($prefix, '-') . '-' . date('y') . '-';
        $student_id_number = Helpers::generate_unique_id($prefix);

        $prep = [];
        $prep["wp_user_id"] = $user_id;
        $prep['student_id_number'] = $student_id_number;

        
        $data = $sanitized_data[0];
        $formats = $sanitized_data[1];
        $data = $prep + $data;
        $formats = ['%d', '%s'] + $formats;

        // 3. Insert into custom table
        $inserted =  $wpdb->insert( $this->table, $data, $formats );
        $parent_data = [];

        if ($inserted){
            $student_id = $wpdb->insert_id;
            $parent_model = new ParentModel();
            $success = $parent_model->create();

            //pass
        }
        return $inserted ? $student_id : false;
    }

    public function update($student_id) {
        global $wpdb;

         // Sanitize the data
        $schema = $this->get_student_schema();
        $photoKey = !isset($_POST['student_photo']) ? 'student_photo':'';
        $user_id = isset($_POST['wp_user_id']) ? absint($_POST['wp_user_id']): null;
        $sanitized_data = Helpers::sanitize_data($schema, $user_id, $photoKey);
        $data = $sanitized_data[0];

        return $wpdb->update(
            $this->table,
            $data,
            ['id' => $student_id], // The WHERE clause
            null,          // Format (auto-detected usually)
            ['%d']         // Format of the WHERE clause
        );
    }

    public function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table} ORDER BY first_name ASC");
    }

    // public function get_all() {
    //     global $wpdb;
    //     $table_users = $wpdb->users;
        
    //     return $wpdb->get_results("
    //         SELECT s.*, u.display_name as student_name, u.user_email as email
    //         FROM {$this->table} s
    //         JOIN {$table_users} u ON s.wp_user_id = u.ID
    //         ORDER BY s.id DESC
    //     ");
    // }
}