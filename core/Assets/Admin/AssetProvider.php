<?php
namespace DelightEDU\Assets\Admin;

class AssetProvider {
    public static function get_staff_data() {
        global $wpdb;

        // Fetch role-to-capability mapping
        $role_caps_raw = $wpdb->get_results("SELECT role_id, capability FROM {$wpdb->prefix}dedu_role_capabilities");
        $role_mapping = [];
        foreach ($role_caps_raw as $row) {
            $role_mapping[$row->role_id][] = $row->capability;
        }

        return [
            'ajaxurl'         => admin_url('admin-ajax.php'),
            'rolePermissions' => $role_mapping,
            'nonce'           => wp_create_nonce('dedu_staff_nonce'),
        ];
    }
}
