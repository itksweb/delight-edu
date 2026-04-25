<?php
namespace DelightEDU\Assets\Admin;
use DelightEDU\Models\StaffRole;

class AssetProvider {
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
}
