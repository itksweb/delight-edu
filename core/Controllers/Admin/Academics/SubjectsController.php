<?php
namespace DelightEDU\Controllers\Admin\Academics;

use DelightEDU\Models\SubjectModel;

class SubjectsController {
    private $model;

    public function __construct() {
        $this->model = new SubjectModel();
    }

    /**
     * View 1: The Master Subject List
     */
    public function render_master_list_view() {
        $subjects = $this->model->get_all_master();
        include DEDU_PATH . 'templates/admin/subjects/manage-master-subjects.php';
    }

    /**
     * View 2: Assigning Subjects to Classes
     */
    public function render_assign_subjects_view() {
        global $wpdb;
        $current_year = \dedu_get_current_year();
        
        // Table names for readability and maintenance
        $table_curriculum   = $wpdb->prefix . 'dedu_class_subjects';
        $table_sec_pivot    = $wpdb->prefix . 'dedu_subject_sections';
        $table_teacher_pivot = $wpdb->prefix . 'dedu_subject_teachers';
        $table_staff        = $wpdb->prefix . 'dedu_staff';

        $classes_with_counts = $wpdb->get_results($wpdb->prepare("
            SELECT 
                c.id, 
                c.class_name, 
                COUNT(cs.id) as subject_count,
                (
                    SELECT JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'subject_id', cs.subject_id,
                            'subject_code', cs.subject_code,
                            'sections', IFNULL((SELECT JSON_ARRAYAGG(section_id) FROM $table_sec_pivot WHERE curriculum_id = cs.id), JSON_ARRAY()),
                            'teachers', IFNULL((SELECT JSON_ARRAYAGG(staff_id) FROM $table_teacher_pivot WHERE curriculum_id = cs.id), JSON_ARRAY())
                        )
                    )
                    FROM $table_curriculum cs 
                    WHERE cs.class_id = c.id AND cs.academic_year = %s
                ) as curriculum_json
            FROM {$wpdb->prefix}dedu_classes c
            LEFT JOIN $table_curriculum cs ON c.id = cs.class_id AND cs.academic_year = %s
            GROUP BY c.id
            ORDER BY c.numeric_name ASC
        ", $current_year, $current_year));

        $all_sections = $wpdb->get_results("SELECT id, section_name, class_id FROM {$wpdb->prefix}dedu_sections");
        $master_subjects = $wpdb->get_results("SELECT id, subject_name FROM {$wpdb->prefix}dedu_subjects_master ORDER BY subject_name ASC");
        $teachers_list = $wpdb->get_results("SELECT id, first_name, last_name FROM {$wpdb->prefix}dedu_staff WHERE is_teacher = 1 ORDER BY first_name ASC");

        // Group sections by class_id for fast JS lookup
        $sections_by_class = [];
        foreach ($all_sections as $sec) {
            $sections_by_class[$sec->class_id][] = $sec;
        }
            include DEDU_PATH . 'templates/admin/subjects/class-subjects-list.php';
    }
    
}