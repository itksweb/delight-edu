<?php
namespace DelightEDU\Controllers\Admin\Academics;

use DelightEDU\Models\Classes;
use DelightEDU\Models\Section;


class ClassSectionController {   

    public function render_class_section_page() {
        $class_model = new Classes();

        // Get data for the lists
        $all_classes = $class_model->get_all_with_sections();

        // Include the view
        include DEDU_PATH . 'templates/admin/academics/class-section-list-form.php';
    }

    public function render_academic_dashboard() {
        echo '<div class="wrap"><h1>DelightEDU Academic Dashboard</h1><p>Welcome to your bespoke School Management Academic Dashboard.</p></div>';
    }

    public function save_class_with_sections() {
        // 1. Security & Permission Check
        if ( ! isset( $_POST['dedu_nonce'] ) || ! wp_verify_nonce( $_POST['dedu_nonce'], 'dedu_class_complex_action' ) ) {
            wp_die( 'Security check failed.' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }

        $class_id   = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;
        $class_name = sanitize_text_field( $_POST['class_name'] );
        $numeric_name = absint( $_POST['numeric_name']);
        $section_names = isset($_POST['sections']) ? (array) $_POST['sections'] : [];
        $categories    = isset($_POST['section_category']) ? (array) $_POST['section_category'] : [];

        $class_model = new Classes();
        
        if ( $class_id > 0 ) {
            // UPDATE existing
            $success = $class_model->update( $class_id, $class_name, $numeric_name, $section_names, $categories );
            $message = 'class_updated';
        } else {
            // CREATE new
            $success = $class_model->create( $class_name, $numeric_name, $section_names, $categories );
            $message = 'class_created';
        }

        $referer = wp_get_referer();

        if ( $success ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', $message, $referer ) : admin_url( 'admin.php?page=dedu-classes-sections&message=' . $message );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg( 'error', 'save_failed', $referer ) );
        }    
        exit;
    }

    public function delete_class() {
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        // 1. Security Check (Nonce)
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'dedu_delete_class_' . $id ) ) {
            wp_die( 'Security check failed.' );
        }

        // 2. Authorization
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }

        global $wpdb;
        $table_assigned_subjects     = $wpdb->prefix . 'dedu_class_subjects';
        $table_class_sections = $wpdb->prefix . 'dedu_sections';


        $class_has_subjects = $wpdb->get_results($wpdb->prepare(
            "SELECT id FROM $table_assigned_subjects WHERE class_id = %d",
            $id ), OBJECT_K); 
        if ($class_has_subjects) {
            wp_die( 'You cannot delete a class that already has subjects assigned to it. Unassign the subjects and try again' ); 
        }

        $wpdb->delete($table_class_sections, ['class_id' => $id], ['%d']);

        $referer = wp_get_referer();

        $base_url = $referer ? $referer : admin_url( 'admin.php?page=dedu-classes-sections' ); // Fallback
        $class_model = new Classes();

        if ( $class_model->delete( $id ) ) {
            wp_redirect( add_query_arg( 'message', 'class_deleted', $base_url ) );
        } else {
            wp_redirect( add_query_arg( 'error', 'delete_failed', $base_url ) );
        }
        exit;
    }

    public function handle_save_class_ajax() {
        // 1. Security Check
        check_ajax_referer('dedu_class_complex_action', 'dedu_nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions.']);
        }

        $class_model = new Classes();
        $section_model = new Section();

        // 2. Create the Class
        $class_id = $class_model->create([
            'class_name'   => $_POST['class_name'],
            'numeric_name' => $_POST['numeric_name']
        ]);

        if ($class_id) {
            // 3. Create the Sections
            $section_names = isset($_POST['sections']) ? (array) $_POST['sections'] : [];
            $categories    = isset($_POST['cate']) ? (array) $_POST['cate'] : [];

            foreach ($section_names as $index => $name) {
                if (!empty($name)) {
                    $section_model->create([
                        'section_name' => $name,
                        'cate'         => isset($categories[$index]) ? $categories[$index] : '',
                        'class_id'     => $class_id
                    ]);
                }
            }

            // 4. GENERATE THE UPDATED TABLE HTML
            // This avoids manual string building in JavaScript
            $all_classes = $class_model->get_all_with_sections();
            
            ob_start();
            // We include a partial file that only contains the <tr> loops
            include DEDU_PATH . 'templates/admin/partials/class-table-rows.php';
            $table_html = ob_get_clean();

            wp_send_json_success(['html' => $table_html]);
        }

        wp_send_json_error(['message' => 'Failed to save class.']);
    }
}