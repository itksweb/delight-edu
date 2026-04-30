<?php
namespace DelightEDU\Controllers\Admin\Admin;

use DelightEDU\Models\StaffRole;
use DelightEDU\Roles\PermissionsRegistry;


class RolesController {
    // Renders the Roles Management UI
    public function render_role_page() {

        $role_model = new StaffRole();
        $groups = PermissionsRegistry::get_all();
        $all_roles = $role_model->get_all_roles();
        $role_mapping = $role_model->get_roles_with_caps();

        include \DEDU_PATH . 'templates/admin/admin/roles-list-form.php';
        
    }    

     public function save_staff_role() {
        // 1. Security Check
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'dedu_role_nonce' ) ) {
            wp_die( 'Security check failed.' );
        }

        // 2. Permission Check
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'You do not have permission to do this.' );
        }

        $role_id   = isset( $_POST['role_id'] ) ? absint( $_POST['role_id'] ) : 0;
        $role_name = sanitize_text_field( $_POST['role_name'] );
        $caps      = isset( $_POST['capabilities'] ) ? (array) $_POST['capabilities'] : [];

        $role_model = new StaffRole();

        if ( $role_id > 0 ) {
            // UPDATE existing
            $success = $role_model->update( $role_id, $role_name, $caps );
            $message = 'role_updated';
        } else {
            // CREATE new
            $success = $role_model->create( $role_name, $caps );
            $message = 'role_created';
        }
        // This gets the URL the user was on before hitting "Save"
        $referer = wp_get_referer();

        if ( $success ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', $message, $referer ) : admin_url( 'admin.php?page=dedu-roles&message=' . $message );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg( 'error', 'save_failed', $referer ) );
        }

        
        exit;
    }

    public function delete_staff_role() {
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        // 1. Security Check (Nonce)
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'dedu_delete_role_' . $id ) ) {
            wp_die( 'Security check failed.' );
        }

        // 2. Authorization
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }
        $referer = wp_get_referer();

        $role_model = new StaffRole();
        if ( $role_model->delete( $id ) ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', 'role_deleted', $referer ) : admin_url( 'admin.php?page=dedu-roles&message=' . 'role_deleted' );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg('error','delete_failed', $referer) );
        }
        exit;
    }

    public function handle_bulk_actions() {
        check_admin_referer('dedu_bulk_roles_action', 'dedu-role-nonce');

        $action   = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
        $role_ids = isset($_POST['role_ids']) ? explode(',', $_POST['role_ids']) : [];
        $role_ids = array_map('absint', $role_ids);

        if (empty($role_ids)) {
            wp_redirect(admin_url('admin.php?page=dedu-staff-roles&status=error'));
            exit;
        }

        $role_model = new StaffRole();

        if ('delete' === $action) {
            $count = 0;
            foreach ($role_ids as $id) {
                if ($role_model->delete($id)) {
                    $count++;
                }
            }
            wp_redirect(admin_url("admin.php?page=dedu-staff-roles&message=bulk_deleted&count=$count"));
            exit;
        }

        // Redirect back if no valid action
        wp_redirect(admin_url('admin.php?page=dedu-staff-roles'));
        exit;
    }
}