<?php

namespace DelightEDU\Models;


class SessionModel {
    private $table;
    protected $table_terms;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'dedu_sessions';
        $this->table_terms = $wpdb->prefix . 'dedu_terms';
    }

    public function get_session_schema() {
        return [
            'session_name'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'starts'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'ends'    => ['filter' => 'sanitize_text_field', 'format' => '%s'],
            'is_current'       => ['filter' => 'absint',              'format' => '%d'],
        ];
    }

    public function create($session_name) {
        global $wpdb;
        
        // 1. Insert the new session into your custom session table
        $wpdb->insert(
            $this->table,
            ['session_name' => $session_name], ['%s']
        );
        
        $session_id = $wpdb->insert_id;
        return $session_id;
    }

    public function create_session_with_terms($session_name, $start_date, $end_date) {
        global $wpdb;

        // 1. Insert the new session into your custom session table
        $wpdb->insert(
            "{$wpdb->prefix}dedu_sessions",
            [
                'session_name' => sanitize_text_field($session_name),
                'start_date'   => $start_date,
                'end_date'     => $end_date,
                'is_current'   => 0 // Default to not current
            ],
            ['%s', '%s', '%s', '%d']
        );

        // Get the newly generated Session ID
        $session_id = $wpdb->insert_id;

        if ($session_id) {
            // 2. Fetch your naming rules from the wp_options table
            $term_label = get_option('dedu_term_naming_label', 'Term'); // Fallback to 'Term'
            $divisions  = absint(get_option('dedu_session_divisions', 3)); // Fallback to 3
            
            // 3. Auto-loop to generate placeholder rows in your custom terms table
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

        return $session_id;
    }

    public function update($session_id, $session_name, $starts, $ends) {
        global $wpdb;
        
        $done = $wpdb->update(
            $wpdb->prefix . 'dedu_sessions',
            [
                'session_name' => $session_name,
                'starts'       => !empty($starts) ? $starts : null,
                'ends'         => !empty($ends) ? $ends : null
            ],
            [ 'id' => $session_id ], [ '%s', '%s', '%s' ], [ '%d' ]
        );

        return false !== $done ;
    }

    public function delete( $id ) {
        global $wpdb;

        // 1. Delete associated terms first (Cleanup Orphans)
        $wpdb->delete(
            $this->table_terms, [ 'session_id' => $id ], [ '%d' ]
        );

        // 2. Delete the session
        $result = $wpdb->delete(
            $this->table, [ 'id' => $id ], [ '%d' ]
        );

        return false !== $result;
    }

}