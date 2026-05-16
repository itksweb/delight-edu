<?php
namespace DelightEDU\Assets\Admin;
use DelightEDU\Models\StaffRole;

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
        global $wpdb;

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
}
