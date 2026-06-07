<?php

namespace DelightEDU\Models;
use DelightEDU\Assets\Admin\Helpers;

class TermModel {
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'dedu_terms';
    }

    public function create($session_id, $data) {

        $term_name = isset($data['term_name']) ? sanitize_text_field($data['term_name']) : '';
        $starts = isset($data['starts']) ? sanitize_text_field($data['starts']) : null;
        $ends = isset($data['ends']) ? sanitize_text_field($data['ends']) : null;

        global $wpdb;
        // 1. Insert the new session into your custom session table
        $wpdb->insert(
            $this->table,
            [
                'session_id' => $session_id,
                'term_name' => $term_name,
                'starts'       => !empty($starts) ? $starts : null,
                'ends'         => !empty($ends) ? $ends : null
            ],
            ['%d', '%s', '%s', '%s']
        );
        
        $session_id = $wpdb->insert_id;
        return $session_id;       
    }

    public function update($session_id, $data) {

        $term_id = isset($data['term_id']) ? absint($data['term_id']) : 0;
        if (empty($term_id)) return false; // Cannot update without a valid term ID

        $term_name = isset($data['term_name']) ? sanitize_text_field($data['term_name']) : '';
        $starts = isset($data['starts']) ? sanitize_text_field($data['starts']) : null;
        $ends = isset($data['ends']) ? sanitize_text_field($data['ends']) : null;
        global $wpdb;

         $done = $wpdb->update(
            $this->table,
            [
                'session_id' => $session_id,  
                'term_name' => $term_name,
                'starts'       => !empty($starts) ? $starts : null,
                'ends'         => !empty($ends) ? $ends : null
            ],
            [ 'id' => $term_id ], [ '%d', '%s', '%s', '%s' ], [ '%d' ]
        );

        return false !== $done ;
    }

    public function createSessionTerms($session_id) {
        global $wpdb;
        $session_id = (int) $session_id;
        $term_label = get_option('dedu_term_naming_label', 'Term'); 
        $divisions  = absint(get_option('dedu_session_divisions', 3));

        for ($i = 1; $i <= $divisions; $i++) {
            // Constructs: "Cohort 1", "Cohort 2", etc.
            $generated_term_name = $term_label . ' ' . $i; 
            $wpdb->insert(
                "{$wpdb->prefix}dedu_terms",
                [
                    'session_id' => $session_id,
                    'term_name'  => $generated_term_name,
                    'is_current' => 0
                ],
                ['%d', '%s', '%d']
            );
        }
    }

    public function get_session_terms($session_id) {
        global $wpdb;
        $table_terms = $wpdb->prefix . 'dedu_terms';
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT *,
                CASE 
                    WHEN starts IS NOT NULL AND ends IS NOT NULL AND CURDATE() BETWEEN starts AND ends THEN 1
                    ELSE 0
                END as is_current
            FROM $table_terms WHERE session_id = %d", $session_id)
        );

        return $results ? $results : [];
    }

    public function delete ( $id ) {
        global $wpdb;

        // 1. Fetch the WP User ID BEFORE deleting the parent record
        $parent_record = $wpdb->get_row( $wpdb->prepare(
            "SELECT wp_user_id FROM {$this->table} WHERE id = %d", 
            $id 
        ));

        // 2. Delete associated link to parent (Cleanup Orphans)
        $link_table = $wpdb->prefix . 'dedu_parents_student_mapping';
        $wpdb->delete($link_table, [ 'parent_id' => $id ], [ '%d' ] );

        // 3. Delete the parent record from your custom table
        $result = $wpdb->delete($this->table, [ 'id' => $id ], [ '%d' ]  );

        // 4. Now delete the actual WordPress User if it exists
        if ( ! empty( $parent_record->wp_user_id ) ) {
            // Note: wp_delete_user needs the ID, and optionally a 'reassign' ID
            require_once( ABSPATH . 'wp-admin/includes/user.php' ); // Ensure function is loaded
            wp_delete_user( $parent_record->wp_user_id );
        }

        return false !== $result;
    }

}