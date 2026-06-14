<?php
namespace DelightEDU\Assets\Admin;
use DelightEDU\Models\StaffRole;
use DelightEDU\Assets\Admin\Helpers;

class AssetProvider {

    public static function get_classes_and_sections () {
        // Get Classes for the "Form Master" dropdown
        global $wpdb;
        $table_classes = $wpdb->prefix . 'dedu_classes';
        $data = [];
        
        $classes = $wpdb->get_results("SELECT id, class_name FROM $table_classes ORDER BY numeric_name ASC");
        $all_sections = $wpdb->get_results("SELECT id, section_name, class_id FROM {$wpdb->prefix}dedu_sections");
        // Group sections by class_id for fast JS lookup
        $sections_by_class = [];
        foreach ($all_sections as $sec) {
            $sections_by_class[$sec->class_id][] = $sec;
        }
        $data['classes'] = $classes;
        $data['sections_by_class'] = $sections_by_class;
        return $data;
       
    }

    public static function get_all_parents () {
        global $wpdb;
        $table_parents = $wpdb->prefix . 'dedu_parents';
        $all_parents = $wpdb->get_results("SELECT * FROM $table_parents ORDER BY first_name ASC");
        foreach ($all_parents as $par) {
            $photo_url = isset($par->profile_picture_id) ? wp_get_attachment_url($par->profile_picture_id) : '';
            $par->photo_url = $photo_url;
        }
        return $all_parents;
    }

    public static function get_staff_data() {
        global $wpdb;
        $role_model = new StaffRole();
        // Fetch role-to-capability mapping
        $role_mapping = $role_model->get_roles_with_caps();

        return [
            'ajaxurl'         => admin_url('admin-ajax.php'),
            'rolePermissions' => $role_mapping,
            'nonce'           => wp_create_nonce('dedu_staff_nonce'),
        ];
    }

    public static function get_student_data() {

        $data = self::get_classes_and_sections();
        $parents = self::get_all_parents();

        return [
            'ajaxurl'         => admin_url('admin-ajax.php'),
            'nonce'           => wp_create_nonce('dedu_student_nonce'),
            'classes'         => $data['classes'],
            'sections' => $data['sections_by_class'],
            'all_parents' => $parents
        ];
    }

    public static function get_session_data() {
        $session_div = get_option('dedu_session_division', 3); // e.g., 3 terms per session
        $default_terms_blueprint = [];
        $numeric_names = [];
        $ordinal_names = [];

        // Build the localized template array cleanly on the server side
        for ($i = 1; $i <= $session_div; $i++) {
            $default_terms_blueprint[] = [
                'term_name' => Helpers::dedu_get_formatted_term_name($i),
                'starts'    => '',
                'ends'      => ''
            ];
            $numeric[] = Helpers::dedu_get_formatted_term_name($i, "numeric");
            $ordinal[] = Helpers::dedu_get_formatted_term_name($i);
        }
        return [
            'nonce'           => wp_create_nonce('dedu_session_nonce'),
            'ajaxurl'         => admin_url('admin-ajax.php'),
            'term_naming_style' => get_option('dedu_term_naming_style', 'ordinal'), 
            'term_label' => get_option('dedu_term_label', 'Term'),
            'sess_div'  => absint(get_option('dedu_session_division', 3)),
            'default_terms' => $default_terms_blueprint,
            'numeric'=> $numeric,
            'ordinal'=> $ordinal,
        ];
    }

    public static function get_attendance_data() {
        $data = self::get_classes_and_sections();
        $active_term = self::get_active_term();
        $holidays = get_option('dedu_school_holidays', array());
        return [
            'ajaxurl'         => admin_url('admin-ajax.php'),
            'nonce'           => wp_create_nonce('dedu_attendance_nonce'),
            'classes'         => $data['classes'],
            'sections' => $data['sections_by_class'],
            'active_term'=> $active_term ? $active_term : null,
            // 'holidays'    => $holidays
        ];
    }

    public static function get_active_term() {
        global $wpdb;
        $table_terms = $wpdb->prefix . 'dedu_terms';
        $active_term = $wpdb->get_row(
            "SELECT session_id, starts, ends 
            FROM $table_terms 
            WHERE starts IS NOT NULL 
            AND ends IS NOT NULL 
            AND CURDATE() BETWEEN starts AND ends 
            LIMIT 1", 
            ARRAY_A
        );
        return $active_term;
    }

    public static function get_active_session() {
        global $wpdb;
        $table_sessions = $wpdb->prefix . 'dedu_sessions';
        $active_session = $wpdb->get_row(
            "SELECT id, starts, ends 
            FROM $table_sessions 
            WHERE starts IS NOT NULL 
            AND ends IS NOT NULL 
            AND CURDATE() BETWEEN starts AND ends 
            LIMIT 1", 
            ARRAY_A
        );
        return $active_session;
    }
}
