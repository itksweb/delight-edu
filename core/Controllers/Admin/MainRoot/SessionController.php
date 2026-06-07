<?php
namespace DelightEDU\Controllers\Admin\MainRoot;
use DelightEDU\Models\SessionModel;
use DelightEDU\Models\TermModel;

class SessionController {

    protected $table;
    private $model;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'dedu_sessions';
        $this->model = new SessionModel();
    }
    public function render_sessions_page() {
        global $wpdb;
        $table_terms = $wpdb->prefix . 'dedu_terms';
        
        
        $sessions = $wpdb->get_results("SELECT *, 
            CASE 
                WHEN starts IS NOT NULL AND ends IS NOT NULL AND CURDATE() BETWEEN starts AND ends THEN 1
                ELSE 0
            END as is_current
            FROM {$this->table} ORDER BY session_name ASC
        ");
        include \DEDU_PATH . 'templates/admin/settings/session-list-form.php';
    }

    public function ajax_get_session_terms () {
        // Security check
        check_ajax_referer('dedu_session_nonce', 'nonce');

        if (!current_user_can('manage_options')) { 
            wp_send_json_error('Unauthorized');
        }

        $session_id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        if (!$session_id) {
            wp_send_json_error('Session does not exist here');
        }
        $termModel = new TermModel();
        $terms = $termModel->get_session_terms($session_id);
        
        wp_send_json_success(['terms' => $terms]);
    }
    public function ajax_handle_create_session_record() {
        // Check security nonce matching the drawer layout fields
        // check_ajax_referer( 'dedu_create_session_action', 'create_session_nonce' );
         check_ajax_referer( 'dedu_session_drawer_action', 'drawer_nonce' );

        $session_name = isset( $_POST['session_name'] ) ? sanitize_text_field( $_POST['session_name'] ) : '';

        if ( empty( $session_name ) ) {
            wp_send_json_error( 'The session name must be specified.' );
        }

        global $wpdb;
        $session_model = new SessionModel();
        $session_id = $session_model->create( $session_name );


        if ( !$session_id ) {
            wp_send_json_error( 'Failed to add session to database' );
        }

        // Drop clean parameters back to JavaScript insertion block
        wp_send_json_success([
            'id'   => $session_id,
            'name' => $session_name
        ]);
    }

    public function ajax_handle_update_session_record() {
        check_ajax_referer( 'dedu_session_drawer_action', 'drawer_nonce' );

        $session_id = isset($_POST['session_id']) ? absint($_POST['session_id']) : 0;
        $session_name = isset($_POST['session_name']) ? sanitize_text_field($_POST['session_name']) : '';
        $starts = isset($_POST['starts']) ? sanitize_text_field($_POST['starts']) : null;
        $ends = isset($_POST['ends']) ? sanitize_text_field($_POST['ends']) : null;

        if (!$session_id || empty($session_name)) {
            wp_send_json_error('Required tracking data parameters are missing.');
        }
        
        global $wpdb;
        // 1. Update master session baseline
        $model = $this->model;
        $success = $model->update( $session_id, $session_name, $starts, $ends );

        // 2. Process Child Terms Loops if present
        if ( !$success ) {
            wp_send_json_error('Something went wrong! Your session details could not be saved');
        }
        if ( isset($_POST['terms']) && is_array($_POST['terms']) ) {
            foreach ( $_POST['terms'] as $term ) {
                $term_id = isset($term['term_id']) ? absint($term['term_id']) : 0;
                if (empty($term['starts']) || empty($term['ends'])) continue;
                $term_model = new TermModel();
                $term_id 
                    ? $term_model->update($session_id, $term) 
                    : $term_model->create($session_id, $term);
            }
        }
        wp_send_json_success('Session updated successfully.');
    }

    public function handle_delete_session() {
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        // 1. Security Check (Nonce)
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'dedu_delete_session_' . $id ) ) {
            wp_die( 'Security check failed.' );
        }

        // 2. Authorization
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }
        $referer = wp_get_referer();

        $model = $this->model;

        if ( $model->delete( $id ) ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', 'session_deleted', $referer ) : admin_url( 'admin.php?page=dedu-sessions&message=' . 'session_deleted' );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg('error','delete_failed', $referer) );
        }
        exit;
    }
}