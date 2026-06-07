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

    public function add_parent_to_student($parent_data, $index, $student_id = null) {
        $parent_model = new ParentModel();
        $student_parent_model = new StudentParentModel();
        $mode = $parent_data['mode'];
        $relationship = sanitize_text_field($parent_data['relationship']);
        $parent_id = $mode === "existing" 
            ? absint($parent_data['existing_id']) // Get the existing parent ID from the form
            : $parent_model->create($parent_data, $index); // or Create a new parent and get its ID
        if ($parent_id && $student_id) {
            // Link the parent to the student with the specified relationship
            $student_parent_model->link($student_id, $parent_id, $relationship);
        }
    }

    public function create() {
        global $wpdb;
        $user = [];
        $user['email'] = $_POST['email']; 
        $user['password'] = $_POST['password'];
        $user['phone'] = $_POST['phone'];
        
        // 1. Create the WordPress User first
        $user_id = Helpers::create_wp_user($user, "dedu_student");
        
        if (is_wp_error($user_id) || !$user_id) {
            $user_id = null; 
        } 
        
        // Sanitize the data
        $schema = $this->get_student_schema();
        $photoKey = !isset($_POST['student_photo']) ? 'student_photo':'';

        // Handle File Upload
        $profile_picture_id = '';
        if ($photoKey && !empty($_FILES[$photoKey]['name'])) {
           $profile_picture_id = Helpers::upload_user_photo($photoKey, $user_id );
        }
        $sanitized_data = Helpers::sanitize_data($schema, $user_id);

        //  Generate student ID Number
        $prefix = get_option('dedu_student_id_prefix', 'EDU');
        $prefix = rtrim($prefix, '-') . '-' . date('y') . '-';
        $admission_no = Helpers::generate_unique_id($prefix);

        //  Add the generated admission number and WP user ID to the schema data
        $data = $sanitized_data[0];
        $formats = $sanitized_data[1];
        Helpers::add_to_schema($data, $formats, $profile_picture_id, 'profile_picture_id' );
        Helpers::add_to_schema($data, $formats, $admission_no, 'admission_no' );
        Helpers::add_to_schema($data, $formats, $user_id, 'user_id');
        
        // 3. Insert into custom table
        $inserted =  $wpdb->insert( $this->table, $data, $formats );

        if ($inserted){
            $parent_data = $_POST['parents'];
            $student_id = $wpdb->insert_id;
            foreach($parent_data as $index => $parent){
                if (empty($parent['mode']) || empty($parent['relationship'])) continue;
                // If mode is set, it means we are linking an either existing parent or a new one
                $this->add_parent_to_student($parent, $index, $student_id);
            }
        }
        return $inserted ? $student_id : false;
    }
    
    public function update($student_id) {
        global $wpdb;

        // Show database errors if any occur
        // $wpdb->show_errors();

        // Sanitize the data using your helper
        $schema = $this->get_student_schema();
        $photoKey = !isset($_POST['student_photo']) ? 'student_photo' : '';
        $user_id = isset($_POST['wp_user_id']) ? absint($_POST['wp_user_id']) : null;
        
        $sanitized_data = Helpers::sanitize_data($schema, $user_id, $photoKey);
        
        $data    = $sanitized_data[0]; // Extracted array data
        $formats = $sanitized_data[1]; // Extracted exact column formats (%s, %d)

        // Run the update query safely
        $done = $wpdb->update(
            $this->table, 
            $data, 
            ['id' => $student_id], // The WHERE clause
            $formats,              // Pass the true column formats here!
            ['%d']                 // Format of the WHERE clause
        );

        // // 🔴 LOG THE RESULT
        // error_log("DB Return Value: " . var_export($done, true));

        if (false !== $done) {
            $parent_data = $_POST['parents'];  
            foreach($parent_data as $index => $parent){
                if (!empty($parent['mode']) && !empty($parent['relationship'])){
                    // If mode is set, it means we are linking an either existing parent or a new one
                    $this->add_parent_to_student($parent, $index, $student_id);
                } else if (!empty($parent['parent_id'])) {
                    $parent_id = absint($parent['parent_id']);
                    $unlink_id = !empty($parent['unlink']) ? absint($parent['unlink']) : null;
                    // error_log("unlink id: " . $unlink_id ." parent id: ". $parent_id);
                    if ($unlink_id === 1 ) {
                        $student_parent_model = new StudentParentModel();
                        $student_parent_model->unlink($student_id, $parent_id);
                    } else {
                        $parent_model = new ParentModel();
                        $parent_model->update($parent, $index);
                    }
                    
                }
            }
        }

        if ($done === false) {
            error_log("DB Error Message: " . $wpdb->last_error);
        }

        // If $done is 0, it means it worked perfectly but no data fields actually changed values.
        // false !== $done ensures both 0 rows changed and positive values count as a true success!
        return false !== $done;
    }
    
    public function delete( $id ) {
        global $wpdb;

        // 1. Fetch the WP User ID BEFORE deleting the student record
        $student_record = $wpdb->get_row( $wpdb->prepare(
            "SELECT user_id FROM {$this->table} WHERE id = %d", 
            $id 
        ));

        // 2. Delete associated link to parent (Cleanup Orphans)
        $link_table = $wpdb->prefix . 'dedu_parents_student_mapping';
        $wpdb->delete($link_table, [ 'student_id' => $id ], [ '%d' ] );

        // 3. Delete the student record from your custom table
        $result = $wpdb->delete($this->table, [ 'id' => $id ], [ '%d' ]  );

        // 4. Now delete the actual WordPress User if it exists
        if ( ! empty( $student_record->user_id ) ) {
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