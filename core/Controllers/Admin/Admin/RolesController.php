<?php
namespace DelightEDU\Controllers\Admin\Admin;

use DelightEDU\Models\StaffRole;
use DelightEDU\Roles\PermissionsRegistry;

class RolesController {
    // Renders the Roles Management UI
    public function render_role_page() {

        $role_model = new StaffRole();
        // 2. ALWAYS fetch groups because the toggle-form is now part of the list page
        $groups = PermissionsRegistry::get_all();
        
        // 3. Initialize variables to prevent "Undefined" errors in the hidden form
        $role = null;
        $all_roles = $role_model->get_all_roles();
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';

        if ( 'edit' === $action ) {
            $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
            $role = $role_model->get_role( $id );
            
            if ( ! $role ) {
                wp_die( 'Role not found.' );
            }
        }
        // 5. Load the unified template
        include \DEDU_PATH . 'templates/admin/roles-list-form.php';
        
    }

    
    


}