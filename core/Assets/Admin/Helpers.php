<?php
namespace DelightEDU\Assets\Admin;


class Helpers {
    public static function sanitize_data($schema, $wp_user_id = null, $photo = "", $dataa = []) {
        $data = count($dataa) ? $dataa : $_POST;
        $data_to_save = [];
        $format_array = [];

        // Handle File Upload
        $profile_picture_id = '';
        if ($photo) {
           if (!empty($_FILES[$photo]['name'])) {
                $profile_picture_id = self::upload_user_photo($photo, $wp_user_id ); 
            }
        }

        foreach ($schema as $column => $rules) {
            if ($column ==='profile_picture_id' && $profile_picture_id) {
                $data_to_save[$column] = $profile_picture_id;
                $format_array[] = $rules['format']; 
                continue;
            }
            if ($column ==='role_id' && !isset($data[$column]) ) {
                $data_to_save[$column] = call_user_func($rules['filter'], $data[$column]);
                $format_array[] = $rules['format']; 
                continue;
            }
            if (isset($data[$column])) {
                $data_to_save[$column] = call_user_func($rules['filter'], $data[$column]);
                $format_array[] = $rules['format']; // Automatically adds the right %s, %d, or %f
            }
        }
        return [$data_to_save, $format_array];
    }

    public static function add_to_schema(&$data, &$formats, $item, $key){ 
        if($item && $key) {
            if(is_numeric($item)){
                $data[$key] = absint($item);
                $formats[] = '%d';
            } else if(is_string($item)){
                $data[$key] = sanitize_text_field($item);
                $formats[] = '%s';
            } 
        }
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
    public static function sideloadParentImage($i = null) {
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

    public static function upload_user_photo($file_key, $wp_user_id = null) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Make sure $wp_user_id is an integer. If null/empty string, default to 0 (unattached).
        $post_id = !empty($wp_user_id) ? absint($wp_user_id) : 0;

        // 1. Upload the photo. Passing 0 means it just lives in the Media Library unattached.
        $attachment_id = media_handle_upload($file_key, $post_id); 

        // 2. If upload succeeded, return the ID to save in your staff table
        if (!is_wp_error($attachment_id)) return $attachment_id; 

        // Log the error if you need to debug why an upload failed
        
        error_log($attachment_id->get_error_message());
        return false;
    }

    public static function prepare_user_data($data) {
        $email = !empty($data['email']) ? sanitize_email($data['email']) : '';
        $phone = !empty($data['phone']) ? sanitize_text_field($data['phone']) : '';

        // 1. Strict Server-Side Fallback Check
        if (empty($email) && empty($phone)) return false;

        // 2. Handle WordPress Login Details Creation
        $user = [];
        if (!empty($email)) {
            $user['email'] = $email;
            $user['user_login'] = sanitize_user($email); 
        } else {
            // If email is empty, generate a safe dummy placeholder using the phone number
            $clean_phone = preg_replace('/[^0-9]/', '', $phone);
            $dummy_username = 'parent_' . $clean_phone;
        
            $user['email'] = $dummy_username . '@yourdomain.local'; // Keeps WP user engine happy
            $user['user_login'] = $dummy_username;
        }
        $user['password'] = !empty($data['password']) ? $data['password'] : wp_generate_password();
        return $user;
    }

    public static function add_update_user($data, $role = "dedu_staff") {
        $email = !empty($data['email']) ? sanitize_user($data['email']):"";
        // 1. Check if email already exists in WordPress to prevent fatal errors
        if ($email && !email_exists($email)) {
           $password = !empty($data['password']) ? $data['password'] : wp_generate_password();
          $username = isset($data['username']) ? $data['username'] : $email;
          $wp_user_id = wp_create_user($username, $password, $email);
            if (!is_wp_error($wp_user_id)) {
                $user = new \WP_User($wp_user_id);
                $user->set_role($role);
                return $wp_user_id;
            }
        } //else if ($email && email_exists($email)) {}        
    }

    public static function create_wp_user($data, $role = "dedu_staff") {
        $user = self::prepare_user_data($data);
        // 1. Check if email already exists in WordPress to prevent fatal errors
        if (email_exists($user['email'])) return null;

        $user_id = wp_create_user($user['user_login'], $user['password'], $user['email']);
        if (is_wp_error($user_id) || !$user_id) {
            $user_id = null;
        } else {
            $new_user = new \WP_User($user_id);
            $new_user->set_role($role);
            $user_id = (int) $user_id;
        }    
        return $user_id;
    }
    public static function dedu_get_current_year() {
        return get_option('dedu_current_academic_year', '2025/2026');
    }

    /**
    * Formats a term name based on the system configuration.
    * * @param int $index The term sequence number (1, 2, 3...)
    * @return string The formatted term string layout
    */
    public static function dedu_get_formatted_term_name($index, $style = 'ordinal') {
        $term_label = get_option('dedu_term_naming_label', 'Term');

        if ($style === 'numeric') return $term_label . ' ' . $index;

        // Output format: 1st Term, 2nd Term, 3rd Term
        $suffixes = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if ((($index % 100) >= 11) && (($index % 100) <= 13))  $suffix = 'th';
        else $suffix = $suffixes[$index % 10];

        return $index . $suffix . ' ' . $term_label;
    }
}