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

    public function create() {
        global $wpdb;
        $user = [];
        $user['email'] = sanitize_user($_POST['email']); 
        $user['password'] = !empty($_POST['password']) ? $_POST['password'] : "PA\$\$1ng";
        
        // 1. Create the WordPress User first
        $user_id = Helpers::create_wp_user($user, "dedu_student");
        
        if (is_wp_error($user_id) || !$user_id) {
            $user_id = null; 
        } 
        
        
        // Sanitize the data
        $schema = $this->get_student_schema();
        $photoKey = !isset($_POST['student_photo']) ? 'student_photo':'';
        $sanitized_data = Helpers::sanitize_data($schema, $user_id, $photoKey);

        //  Generate student ID Number
        $prefix = get_option('dedu_student_id_prefix', 'EDU');
        $prefix = rtrim($prefix, '-') . '-' . date('y') . '-';
        $student_id_number = Helpers::generate_unique_id($prefix);

        $prep = [];
        $prep["user_id"] = $user_id;
        $prep['admission_no'] = $student_id_number;

        
        $data = $sanitized_data[0];
        $formats = $sanitized_data[1];
        $data = $prep + $data;
        $formats = ['%d', '%s'] + $formats;

        // 3. Insert into custom table
        $inserted =  $wpdb->insert( $this->table, $data, $formats );

        if ($inserted){
            $parent_data = $_POST['parents'];
            $student_id = $wpdb->insert_id;
            $parent_model = new ParentModel();
            $student_parent_model = new StudentParentModel();
            foreach($parent_data as $parent){
                $relationship = isset($parent['relationship']) ? $parent['relationship']: "";
                $parent_id = $parent['mode'] === "existing" 
                    ? absint($parent['existing_id'])
                    : $parent_model->create($parent);
                if ($parent_id ) {
                    $student_parent_model->link($student_id, $parent_id, $relationship);
                }
            }
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
        $done = $wpdb->update($this->table, $data, 
            ['id' => $student_id], // The WHERE clause
            null,          // Format (auto-detected usually)
            ['%d']         // Format of the WHERE clause
        );

        $parent_data = $_POST['parents'];
        $parent_model = new ParentModel();
        $student_parent_model = new StudentParentModel();
        foreach($parent_data as $parent){
            if (isset($parent['mode'])) {
                $parent_id = $parent['mode'] === "existing" 
                    ? absint($parent['existing_id'])
                    : $parent_model->create($parent);
                if ($parent_id ) {
                    $relationship = isset($parent['relationship']) ? $parent['relationship']: "";
                    $student_parent_model->link($student_id, $parent_id, $relationship);
                }
            } else {
                $parent_id = isset($parent['parent_id']) ? $parent['parent_id'] : null;
                if ($parent_id) {
                    $parent_model->update($parent_id, $parent);
                }
            }
            
        }

        return $done;
    }

    public function delete( $id ) {
        global $wpdb;

        // 1. Fetch the WP User ID BEFORE deleting the student record
        $student_record = $wpdb->get_row( $wpdb->prepare(
            "SELECT wp_user_id FROM {$this->table} WHERE id = %d", 
            $id 
        ));

        // 2. Delete associated link to parent (Cleanup Orphans)
        $link_table = $wpdb->prefix . 'dedu_parents_student_mapping';
        $wpdb->delete($link_table, [ 'student_id' => $id ], [ '%d' ] );

        // 3. Delete the student record from your custom table
        $result = $wpdb->delete($this->table, [ 'id' => $id ], [ '%d' ]  );

        // 4. Now delete the actual WordPress User if it exists
        if ( ! empty( $student_record->wp_user_id ) ) {
            // Note: wp_delete_user needs the ID, and optionally a 'reassign' ID
            require_once( ABSPATH . 'wp-admin/includes/user.php' ); // Ensure function is loaded
            wp_delete_user( $student_record->wp_user_id );
        }

        return false !== $result;
    }

    public function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table} ORDER BY first_name ASC");
    }

    public function get_student_by_id($id) {
        global $wpdb;
        $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id), ARRAY_A);
        // $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id ));

        if ($student) {
            // Fix "Zero Dates" for the frontend
            $date_fields = ['date_of_birth', 'joining_date'];
            foreach ($date_fields as $field) {
                if (empty($student[$field]) || $student[$field] === '0000-00-00') {
                    $student[$field] = ''; // Set to empty string so the HTML input stays blank
                }
            }
        }
        return $student;
    }
}