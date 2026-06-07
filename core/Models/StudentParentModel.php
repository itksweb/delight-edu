<?php

namespace DelightEDU\Models;

class StudentParentModel {
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'dedu_parents_student_mapping';
    }

    public function link($student_id, $parent_id, $relationship = 'father') {
        global $wpdb;
        return $wpdb->insert($this->table, [
            'student_id'        => $student_id,
            'parent_id'         => $parent_id,
            'relationship_type' => $relationship
        ]);
    }

    public function unlink($student_id, $parent_id) {
        global $wpdb;
        $unlinked =  $wpdb->delete(
            $this->table, 
            ['student_id' => absint($student_id), 'parent_id'=> absint($parent_id)], 
            ['%d','%d'] 
        );
        return false !== $unlinked; 
    }
}