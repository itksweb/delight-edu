<?php
namespace DelightEDU\Controllers\Admin\Academics;


class AttendanceController {   

    public function render_attendance_page() {

        // Get Classes for the "Form" dropdown
        global $wpdb;
        $table_classes = $wpdb->prefix . 'dedu_classes';
        $classes = $wpdb->get_results("SELECT id, class_name FROM $table_classes ORDER BY numeric_name ASC");
        $all_sections = $wpdb->get_results("SELECT id, section_name, class_id FROM {$wpdb->prefix}dedu_sections");
        // Group sections by class_id for fast JS lookup
        $sections_by_class = [];
        foreach ($all_sections as $sec) {
            $sections_by_class[$sec->class_id][] = $sec;
        }

        // Include the view
        include DEDU_PATH . 'templates/admin/academics/attendance.php';
    }

    public function ajax_handle_load_attendance_roaster() {
        $class_id = isset($_POST['class_id']) ? absint($_POST['class_id']) : 0;
        $section_id = isset($_POST['section_id']) ? absint($_POST['section_id']) : 0;
        $date     = isset($_POST['attendance_date']) ? sanitize_text_field($_POST['attendance_date']) : '';

        if (!$class_id || empty($date)) {
            wp_send_json_error('Invalid parameters provided. Please provide the date and class');
        }

        global $wpdb;

        // 1. Figure out which term this calendar date belongs to using our clean system boundaries
        $term = $wpdb->get_row($wpdb->prepare(
            "SELECT id, term_name FROM {$wpdb->prefix}dedu_terms WHERE %s BETWEEN starts AND ends",
            $date
        ));

        if (!$term) {
            wp_send_json_error('Selected date does not fall within any configured academic term boundaries.');
        }

        if ($section_id) {
            // 2. Fetch all students registered in the selected class
            $students = $wpdb->get_results($wpdb->prepare(
                "SELECT id, first_name, last_name FROM {$wpdb->prefix}dedu_students WHERE class_id = %d AND section_id = %d ORDER BY first_name ASC",
                $class_id, $section_id
            ));

            // 3. Fetch existing attendance data for this class/date combo if it exists
            $existing_records = $wpdb->get_results($wpdb->prepare(
                "SELECT student_id, status, remarks FROM {$wpdb->prefix}dedu_attendance WHERE class_id = %d AND section_id = %d AND attendance_date = %s",
                $class_id, $section_id, $date
            ), OBJECT_K); // Indexing the output array by student_id automatically makes lookups instantaneous below
        } else {
            // 2. Fetch all students registered in the selected class
            $students = $wpdb->get_results($wpdb->prepare(
                "SELECT id, first_name, last_name, section_id FROM {$wpdb->prefix}dedu_students WHERE class_id = %d ORDER BY first_name ASC",
                $class_id
            ));

            // 3. Fetch existing attendance data for this class/date combo if it exists
            $existing_records = $wpdb->get_results($wpdb->prepare(
                "SELECT student_id, status, remarks FROM {$wpdb->prefix}dedu_attendance WHERE class_id = %d AND attendance_date = %s",
                $class_id, $date
            ), OBJECT_K); // Indexing the output array by student_id automatically makes lookups instantaneous below
        }

        

        $roster_payload = [];
        foreach ($students as $student) {
            $has_record = isset($existing_records[$student->id]);
            $roster_payload[] = [
                'student_id' => $student->id,
                'name'       => $student->first_name,
                'status'     => $has_record ? $existing_records[$student->id]->status : 'present', // Defaults to present
                'section_id'  => $section_id ? $section_id : $student->section_id,
                'remarks'    => $has_record ? $existing_records[$student->id]->remarks : ''
            ];
        }

        wp_send_json_success([
            'term' => $term,
            'roster'  => $roster_payload
        ]);
    }

    public function ajax_handle_save_attendance_sheet() {
        check_ajax_referer('dedu_save_attendance_action', 'attendance_nonce');

        $class_id   = isset($_POST['class_id']) ? absint($_POST['class_id']) : 0;
        $term_id    = isset($_POST['term_id']) ? absint($_POST['term_id']) : 0;
        $date       = isset($_POST['attendance_date']) ? sanitize_text_field($_POST['attendance_date']) : '';
        $attendance = isset($_POST['attendance']) ? $_POST['attendance'] : [];

        if (!$class_id || !$term_id || empty($date) || empty($attendance)) {
            wp_send_json_error('Required roster parameters are missing.');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'dedu_attendance';

        // Build a secure, high-performance bulk compilation query statement manually
        $values = [];
        $placeholders = [];

        foreach ($attendance as $student_id => $data) {
            $student_id = absint($student_id);
            $section_id = absint($data['section_id']);
            $status     = sanitize_text_field($data['status']);
            $remarks    = sanitize_text_field($data['remarks']);

            $placeholders[] = "(%d, %d, %d, %d, %s, %s, %s)";

            // 🔴 Merges the new row values straight into your existing master array
            $values = array_merge($values, [$student_id, $term_id, $class_id, $section_id, $date, $status, $remarks]);
        }

        if (empty($values)) {
            wp_send_json_error('No valid attendance data records processed.');
        }
        error_log("hello attendance: " . print_r($values, true));


        // Combine placeholders into one query block string structure
        $query = "INSERT INTO $table_name 
                (student_id, term_id, class_id, section_id, attendance_date, status, remarks) 
                VALUES " . implode(', ', $placeholders) . "
                ON DUPLICATE KEY UPDATE 
                status = VALUES(status), 
                remarks = VALUES(remarks)";

        $prepared_query = $wpdb->prepare($query, $values);
        $result = $wpdb->query($prepared_query);

        $result !== false 
            ? wp_send_json_success('Attendance roster saved perfectly.')
            : wp_send_json_error('Database transaction execution error encountered.');
    }
}