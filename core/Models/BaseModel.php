<?php
namespace DelightEDU\Models;

abstract class BaseModel {
    protected $table;

    public function __construct() {
        global $wpdb;
        // Automatically prefix the table name
        $this->table = $wpdb->prefix . $this->get_table_name();
    }

    // Every child model MUST define its table name (e.g., 'dedu_classes')
    abstract protected function get_table_name();

    public function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table} ORDER BY id DESC");
    }

    public function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id));
    }

    public function delete($id) {
        global $wpdb;
        return $wpdb->delete($this->table, ['id' => $id], ['%d']);
    }
}