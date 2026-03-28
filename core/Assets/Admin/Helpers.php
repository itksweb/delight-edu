<?php
namespace DelightEDU\Assets\Admin;


class Helpers {

    public static function sanitize_data($schema, $wp_user_id, $photo = "") {
        $data_to_save = [];
        $format_array = [];

        // Handle File Upload
        $profile_picture_id = '';
        if ($photo) {
           if (!empty($_FILES[$photo]['name'])) {
                $profile_picture_id = self::upload_staff_photo($photo, $wp_user_id ); 
            }
        }
        

        foreach ($schema as $column => $rules) {
            if ($column ==='profile_picture_id' && $profile_picture_id) {
                $data_to_save[$column] = $profile_picture_id;
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

    public static function generate_unique_id($prefix) {
        global $wpdb;

        $is_unique = false;
        $final_id  = '';
        $attempts  = 0;

        while (!$is_unique && $attempts < 10) {
            // Generate potential ID: PREFIX-YY-RAND(3)
            $potential_id = $prefix . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $table_name = $wpdb->prefix . 'dedu_staff';

            // Check database
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE staff_id_number = %s",
                $potential_id
            ));

            if ($exists == 0) {
                $final_id = $potential_id;
                $is_unique = true;
            }
            $attempts++;
        }

        // fallback if for some crazy reason 10 random attempts fail
        return $is_unique ? $final_id : $prefix . time();
    }

    public static function upload_photo_get_its_url($file_key) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload($file_key, 0); 

        if (!is_wp_error($attachment_id)) {
            return wp_get_attachment_url($attachment_id);
        }
        
        return false; // Return false so we know it failed
    }

    public static function upload_staff_photo($file_key, $wp_user_id) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // 1. Upload the new photo and attach it to the staff's WP User ID
        $attachment_id = media_handle_upload($file_key, $wp_user_id); 

        // 2. If upload succeeded, return the ID to save in your staff table
        if (!is_wp_error($attachment_id)) return $attachment_id; 
        
        // Get the old photo ID (if it exists)
        // $old_photo_id = $staff_id ? $this->get_staff_column($staff_id, 'profile_picture_id') : null ;
        // if ($old_photo_id) wp_delete_attachment($old_photo_id, true); // true = bypass trash
        return false;
    }

    public static function update_wp_user() {
        $user_data = [
            'ID'         => absint($_POST['wp_user_id']),
            'user_email' => sanitize_email($_POST['email']),
        ];

        // Only update password if a new one was actually provided
        if (!empty($_POST['password'])) {
            $user_data['user_pass'] = $_POST['password']; // WP hashes this automatically
        }

        return wp_update_user($user_data);
    }

    public static function create_wp_user() {
        $username = sanitize_user($_POST['email']); // Or generate a specific format
        $password = !empty($_POST['password']) ? $_POST['password'] : wp_generate_password();

        $wp_user_id = wp_create_user($username, $password, sanitize_email($_POST['email']));
        
        if (!is_wp_error($wp_user_id)) {
            // Set their role to something specific like 'subscriber' or 'staff'
            $user = new \WP_User($wp_user_id);
            $user->set_role('subscriber');
        }

        return $wp_user_id;
    }
    public static function dedu_get_current_year() {
        return get_option('dedu_current_academic_year', '2025/2026');
    }
}