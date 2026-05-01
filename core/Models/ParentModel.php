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
            'first_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'middle_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'last_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'email'         => ['filter' => 'sanitize_email',      'format' => '%s'],
            'phone'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'address'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'gender'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'marital_status'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'date_of_birth'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'username'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'blood_group'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
        ];
    }

    public function create($data) {
        global $wpdb;

        $user = [];
        $user['email'] = sanitize_user($data['email']);
        $user['password'] = !empty($data['password']) ? $data['password'] : wp_generate_password();
       
        // 1. Create the WordPress User first
        $user_id = Helpers::create_wp_user($user, "dedu_parent");
        if (is_wp_error($user_id)) return $user_id;
        
        // Sanitize the data
        $schema = $this->get_parent_schema();
        $photoKey = !isset($_POST['parent_photo']) ? 'parent_photo':'';
        $sanitized_data = Helpers::sanitize_data($schema, $user_id, $photoKey);

        $prep = [];
        $prep["wp_user_id"] = $user_id;
        
        $data = $sanitized_data[0];
        $formats = $sanitized_data[1];
        $data = $prep + $data;
        $formats = ['%d'] + $formats;

        // 3. Insert into custom table
        $inserted =  $wpdb->insert( $this->table, $data, $formats );
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
}