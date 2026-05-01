<?php

namespace DelightEDU\Models;

class StudentParentModel {
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'dedu_student_parent';
    }

    public function link($student_id, $parent_id, $relationship = 'father') {
        global $wpdb;
        return $wpdb->insert($this->table, [
            'student_id'        => $student_id,
            'parent_id'         => $parent_id,
            'relationship_type' => $relationship
        ]);
    }
}