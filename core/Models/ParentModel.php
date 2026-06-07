<?php

namespace DelightEDU\Models;
use DelightEDU\Assets\Admin\Helpers;

class ParentModel {
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'dedu_parents';
    }

    public function get_parent_schema() {
        return [
            'profile_picture_id'    => ['filter' => 'absint',  'format' => '%d'],
            'relationship'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'first_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'middle_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'last_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'email'         => ['filter' => 'sanitize_email',      'format' => '%s'],
            'phone'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'address'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'gender'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'marital_status'    => ['filter' => 'sanitize_text_field', 'format' => '%s']            
        ];
    }

    public function sideloadParentImage($i = null) {
        $profile_picture_id = 0;
        if ($i !== null && isset($_FILES['parents']['name'][$i]['profile_photo'])) {
            // Construct a safe isolated array mimicking a standalone file upload element
            $file_array = [
                'name'     => $_FILES['parents']['name'][$i]['profile_photo'],
                'type'     => $_FILES['parents']['type'][$i]['profile_photo'],
                'tmp_name' => $_FILES['parents']['tmp_name'][$i]['profile_photo'],
                'error'    => $_FILES['parents']['error'][$i]['profile_photo'],
                'size'     => $_FILES['parents']['size'][$i]['profile_photo'],
            ];
            if ($file_array['error'] === UPLOAD_ERR_OK) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');

                // media_handle_sideload lets us process custom rearranged files array sets
                $attachment_id = media_handle_sideload($file_array, 0); 
                
                if (!is_wp_error($attachment_id)) {
                    $profile_picture_id = $attachment_id;
                }
            }
        }
        return $profile_picture_id;
    }

    public function create($data, $i = null) {
        global $wpdb;

        // 3. Create the WordPress User
        $user_id = Helpers::create_wp_user($data, "dedu_parent");
        
        // Sanitize the data
        $schema = $this->get_parent_schema();
        $sanitized_data = Helpers::sanitize_data($schema, $user_id, '', $data);
        $data = $sanitized_data[0];
        $formats = $sanitized_data[1];
        $profile_picture_id = $this->sideloadParentImage($i);
        Helpers::add_to_schema($data, $formats, $profile_picture_id, 'profile_picture_id' );
        Helpers::add_to_schema($data, $formats, $user_id, 'wp_user_id');
        error_log("Sanitized Parent Data: " . print_r($data, true));

        // 3. Insert into custom table
        $inserted =  $wpdb->insert( $this->table, $data, $formats );

        return $inserted ? $wpdb->insert_id : false;
    }

    public function update($data, $i = null) {
        global $wpdb;

        // Show database errors if any occur
        $wpdb->show_errors();   

        $parent_id = isset($data['parent_id']) ? absint($data['parent_id']) : null;
        if (!$parent_id) return false;

        $schema = $this->get_parent_schema();
        $user_id = isset($data['wp_user_id']) ? absint($data['wp_user_id']): null;
        $sanitized_data = Helpers::sanitize_data($schema, $user_id, '', $data);
        $data    = $sanitized_data[0]; // Extracted array data
        $formats = $sanitized_data[1]; // Extracted exact column formats (%s, %d)
        $profile_picture_id = $this->sideloadParentImage($i);
        Helpers::add_to_schema($data, $formats, $profile_picture_id, 'profile_picture_id' );

        // 🔴 LOG THE DATA TO SEE WHAT WE ARE SENDING
        error_log("--- DATABASE UPDATE DEBUG ---");
        error_log("Parent ID: " . $parent_id);
        error_log("Data Payload: " . print_r($data, true));
        error_log("Formats: " . print_r($formats, true));

        $done = $wpdb->update($this->table, $data, ['id' => $parent_id], $formats, ['%d']);

        // 🔴 LOG THE RESULT
        error_log("DB Return Value: " . var_export($done, true));
        if ($done === false) {
            error_log("DB Error Message: " . $wpdb->last_error);
        }

        return false !== $done;
    }

    public function get_all() {
        global $wpdb;
        $table_users = $wpdb->users;
        
        // Joining with wp_users to get the Email and Name
        return $wpdb->get_results("
            SELECT p.*, u.user_email as email, u.display_name
            FROM {$this->table} p
            JOIN {$table_users} u ON p.wp_user_id = u.ID
            ORDER BY p.id DESC
        ");
    }

    public function get_parent_by_id($id) {}

    public function delete ( $id ) {
        global $wpdb;

        // 1. Fetch the WP User ID BEFORE deleting the parent record
        $parent_record = $wpdb->get_row( $wpdb->prepare(
            "SELECT wp_user_id FROM {$this->table} WHERE id = %d", 
            $id 
        ));

        // 2. Delete associated link to parent (Cleanup Orphans)
        $link_table = $wpdb->prefix . 'dedu_parents_student_mapping';
        $wpdb->delete($link_table, [ 'parent_id' => $id ], [ '%d' ] );

        // 3. Delete the parent record from your custom table
        $result = $wpdb->delete($this->table, [ 'id' => $id ], [ '%d' ]  );

        // 4. Now delete the actual WordPress User if it exists
        if ( ! empty( $parent_record->wp_user_id ) ) {
            // Note: wp_delete_user needs the ID, and optionally a 'reassign' ID
            require_once( ABSPATH . 'wp-admin/includes/user.php' ); // Ensure function is loaded
            wp_delete_user( $parent_record->wp_user_id );
        }

        return false !== $result;
    }

}