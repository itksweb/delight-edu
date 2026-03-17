<?php
namespace DelightEDU\Models;

class SubjectModel {
    private $table_master;
    private $table_mapping;

    public function __construct() {
        global $wpdb;
        $this->table_master  = $wpdb->prefix . 'dedu_subjects_master';
        $this->table_mapping = $wpdb->prefix . 'dedu_class_subjects';
    }

    /**
     * MASTER LIST OPERATIONS
     */
    public function get_all_master() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table_master} ORDER BY subject_name ASC");
    }

    public function create_master_subject($name, $type = 'core') {
        global $wpdb;
        return $wpdb->insert(
            $this->table_master,
            [
                'subject_name' => sanitize_text_field($name),
                'subject_type' => $type
            ],
            ['%s', '%s']
        );
    }

    public function update_master_subject($master_subject_id, $name, $type = 'core') {
        global $wpdb;
        return $wpdb->update(
            $this->table_master,
            [
                'subject_name' => sanitize_text_field($name),
                'subject_type' => $type
            ],
            [ 'id' => $master_subject_id ],
            ['%s', '%s'],
            [ '%d' ]
        );
    }

    public function delete_master_subject( $id ) {
        global $wpdb;
        $result = $wpdb->delete(
            $this->table_master,
            [ 'id' => $id ],
            [ '%d' ]
        );

        return false !== $result;
    }

    /**
     * CLASS-SUBJECT MAPPING (Curriculum by Year)
     */
    public function link_to_class($class_id, $subject_id, $year, $code = '', $pass_mark = 40) {
        global $wpdb;
        
        // Prevent duplicate mapping for the same subject/class/year
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_mapping} 
             WHERE class_id = %d AND subject_id = %d AND academic_year = %s",
            $class_id, $subject_id, $year
        ));

        if ($exists > 0) return false;

        return $wpdb->insert(
            $this->table_mapping,
            [
                'class_id'      => absint($class_id),
                'subject_id'    => absint($subject_id),
                'academic_year' => sanitize_text_field($year), // e.g. "2025/2026"
                'subject_code'  => sanitize_text_field($code),
                'pass_mark'     => absint($pass_mark)
            ],
            ['%d', '%d', '%s', '%s', '%d']
        );
    }

    /**
     * Get Curriculum for a specific class and year
     */
    public function get_curriculum($class_id, $year) {
        global $wpdb;
        $query = $wpdb->prepare("
            SELECT sm.subject_name, sm.subject_type, cm.* FROM {$this->table_mapping} cm
            JOIN {$this->table_master} sm ON cm.subject_id = sm.id
            WHERE cm.class_id = %d AND cm.academic_year = %s
            ORDER BY sm.subject_name ASC
        ", $class_id, $year);
        
        return $wpdb->get_results($query);
    }
}