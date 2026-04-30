<?php
namespace DelightEDU\Models;

class Classes {

    protected $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'dedu_classes';
    }

    public function create($class_name,  $numeric_name, $section_names =[], $categories = []) {
        global $wpdb;
        // 1. Insert the class into the main table
        $wpdb->insert(
            $this->table,
            [
                'class_name'   => sanitize_text_field($class_name),
                'numeric_name' => $numeric_name
            ],
            ['%s', '%d']
        );

        $class_id = $wpdb->insert_id;

        // 2. Insert each sections into the sections table
        $table_sections = $wpdb->prefix . 'dedu_sections';
        foreach ($section_names as $index => $name) {
            $trimmed_name = sanitize_text_field($name);
            if (!empty($trimmed_name)) {
                $wpdb->insert (
                    $table_sections, [
                    'section_name' => $trimmed_name,
                    'section_category'    => isset($categories[$index]) ? $categories[$index] : '',
                    'class_id'     => $class_id
                ]);
            }
        }
        return $class_id;
    }

    public function update($class_id, $class_name, $numeric_name, $section_names =[], $categories = []) {
        global $wpdb;

        // 1. Update the main class name
        $wpdb->update(
            $this->table,
            [ 
            'class_name'   => $class_name, 
            'numeric_name' => $numeric_name 
            ],
            [ 'id' => $class_id ], // This is the WHERE array
            [ '%s', '%d' ],        // Formats for Data
            [ '%d' ]               // Format for Where
        );

        $table_sections = $wpdb->prefix . 'dedu_sections';
        // 2. Clear old sections for this class
        $wpdb->delete( $table_sections, [ 'class_id' => $class_id ], [ '%d' ] );

        // 3. Insert the new set of classes
        foreach ($section_names as $index => $name) {
            $trimmed_name = sanitize_text_field($name);
            if (!empty($trimmed_name)) {
                $wpdb->insert (
                    $table_sections, [
                    'section_name' => $trimmed_name,
                    'section_category'    => isset($categories[$index]) ? $categories[$index] : '',
                    'class_id'     => $class_id
                ]);
            }
        }
        // Check if the last query had errors
        return ( $wpdb->last_error === '' );
    }

    public function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table} ORDER BY numeric_name ASC");
    }

    public function get_all_with_sections() {
        global $wpdb;
        $table_sections = $wpdb->prefix . 'dedu_sections';
        
        // Using LEFT JOIN to ensure classes with NO sections still show up
        $results = $wpdb->get_results("
            SELECT c.*, GROUP_CONCAT(s.section_name ORDER BY s.section_name ASC SEPARATOR ', ') as section_list
            FROM {$this->table} c
            LEFT JOIN {$table_sections} s 
            ON c.id = s.class_id
            GROUP BY c.id
            ORDER BY c.numeric_name ASC
        ");
        if ( ! empty( $results ) ) {
            foreach ( $results as $row ) {
                // If section_list is not empty, explode it; otherwise, return an empty array
                $row->section_list = ! empty( $row->section_list ) 
                    ? explode( ', ', $row->section_list ) 
                    : [];
            }
        }
        return $results;
    }
    
    public function delete($id) {
        global $wpdb;
        $table_sections = $wpdb->prefix . 'dedu_sections';
        // 1. Delete associated sections first (Cleanup Orphans)
        $wpdb->delete(
            $table_sections, 
            [ 'class_id' => $id ], 
            [ '%d' ]
        );
        return $wpdb->delete($this->table, ['id' => $id], ['%d']);
    }
    
}
