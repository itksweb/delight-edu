<?php
namespace DelightEDU\Models;

class Section {
    protected $table;
    protected $table_classes;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'dedu_sections';
        $this->table_classes = $wpdb->prefix . 'dedu_classes';
    }

    /**
     * Fetch sections joined with their class name
     */
    public function get_all_with_class() {
        global $wpdb;
        return $wpdb->get_results("
            SELECT s.*, c.class_name 
            FROM {$this->table} s
            LEFT JOIN {$this->table_classes} c ON s.class_id = c.id
            ORDER BY c.numeric_name ASC, s.section_name ASC
        ");
    }

    /**
     * Crucial for the Staff module: Get only sections for a specific class
     */
    public function get_by_class($class_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE class_id = %d ORDER BY section_name ASC",
            $class_id
        ));
    }

    public function create($data) {
        global $wpdb;
        return $wpdb->insert(
            $this->table,
            [
                'section_name' => sanitize_text_field($data['section_name']),
                'section_category'     => isset($data['cate']) ? sanitize_text_field($data['cate']) : '',
                'capacity'     => absint($data['capacity']),
                'class_id'     => absint($data['class_id'])
            ],
            ['%s', '%d', '%d']
        );
    }
}