<?php
namespace DelightEDU\Controllers\Admin\Academics;

use DelightEDU\Models\SubjectModel;
use DelightEDU\Assets\Admin\Helpers;

class SubjectsController {
    private $model;

    public function __construct() {
        $this->model = new SubjectModel();
    }

    //View 1: The Master Subject List
    public function render_master_list_view() {
        $subjects = $this->model->get_all_master();
        include DEDU_PATH . 'templates/admin/academics/master-subjects.php';
    }

     public function handle_save_master_subject() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'dedu_master_subject_nonce' ) ) {
            wp_die( 'Security check failed.' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }

        $name = sanitize_text_field($_POST['subject_name']);
        $type = sanitize_text_field($_POST['subject_type']);
        $master_subject_id   = isset( $_POST['master_subject_id'] ) ? absint( $_POST['master_subject_id'] ) : 0;
        
        $subject_model = new SubjectModel();
        if ($master_subject_id > 0) {
            $success = $subject_model->update_master_subject($master_subject_id, $name, $type);
            $message = 'subject_updated';
            // $status = $success ? 'subject_updated' : 'update_failed';
        } else {
            $success = $subject_model->create_master_subject($name, $type);
            $message = 'subject_created';
            // $status = $success ? 'subject_created' : 'save_failed';
        }
        
        $referer = wp_get_referer();        
        if ( $success ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', $message, $referer ) : admin_url( 'admin.php?page=dedu-subjects&message=' . $message );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg( 'error', 'save_failed', $referer ) );
        }
        exit;
    }

    public function delete_master_subject() {
        $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

        // 1. Security Check (Nonce)
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'dedu_delete_master_subject_' . $id ) ) {
            wp_die( 'Security check failed.' );
        }

        // 2. Authorization
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }
        $referer = wp_get_referer();

        $subject_model = new SubjectModel();
        if ( $subject_model->delete_master_subject( $id ) ) {
            // If we have a referer, add the message to it; otherwise, use a default
            $redirect_url = $referer ? add_query_arg( 'message', 'subject_deleted', $referer ) : admin_url( 'admin.php?page=dedu-subjects&message=' . 'subject_deleted' );
            wp_redirect( $redirect_url );
        } else {
            wp_redirect( add_query_arg('error','delete_failed', $referer) );
        }
        exit;
    }

    //View 2: Assigning Subjects to Classes
    public function render_assign_subjects_view() {
        global $wpdb;
        $current_year = Helpers::dedu_get_current_year();
        
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
            include DEDU_PATH . 'templates/admin/academics/class-subjects.php';
    }

    public function handle_bulk_save_class_subjects() {
        // 1. Security & Permissions
        check_admin_referer('dedu_bulk_curriculum_nonce');
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access.');
        }

        global $wpdb;

        // 2. Setup Variables
        $class_id       = absint($_POST['class_id']);
        $subjects_input = isset($_POST['subjects']) ? $_POST['subjects'] : [];
        $current_year   = Helpers::dedu_get_current_year();
        
        $table_main     = $wpdb->prefix . 'dedu_class_subjects';
        $table_sections = $wpdb->prefix . 'dedu_subject_sections';
        $table_teachers = $wpdb->prefix . 'dedu_subject_teachers';
        
        

        /**
         * 3. PREPARATION: Get currently assigned subject IDs for this class.
         * This helps us know which ones to DELETE if they were removed from the form.
         */
        $existing_assignments = $wpdb->get_results($wpdb->prepare(
            "SELECT id, subject_id FROM $table_main WHERE class_id = %d AND academic_year = %s",
            $class_id, $current_year
        ), OBJECT_K); // Keyed by the 'id' of the record

        $processed_ids = []; // We will store IDs we keep/update here

        // 4. THE LOOP: Process each subject from the form
        if (is_array($subjects_input)) {
            foreach ($subjects_input as $sub) {
                $subject_id = isset($sub['id']) ? absint($sub['id']) : 0;
                if ($subject_id === 0) continue;

                // Check if this subject is already in the database for this class/year
                $curriculum_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $table_main WHERE class_id = %d AND subject_id = %d AND academic_year = %s",
                    $class_id, $subject_id, $current_year
                ));

                if ($curriculum_id) {
                    // UPDATE existing record
                    $wpdb->update($table_main, 
                        ['subject_code' => sanitize_text_field($sub['code'] ?? '')],
                        ['id' => $curriculum_id],
                        ['%s'], ['%d']
                    );
                } else {
                    // INSERT new record
                    $wpdb->insert($table_main, [
                        'class_id'      => $class_id,
                        'subject_id'    => $subject_id,
                        'subject_code'  => sanitize_text_field($sub['code'] ?? ''),
                        'academic_year' => $current_year
                    ], ['%d', '%d', '%s', '%s']);
                    $curriculum_id = $wpdb->insert_id;
                }

                $processed_ids[] = $curriculum_id;

                /**
                 * 5. PIVOT TABLES: Sync Teachers and Sections
                 * For simplicity and to avoid duplicates, we clear pivots for THIS specific subject 
                 * and re-add them. This is safer than clearing the whole class.
                 */
                $wpdb->delete($table_sections, ['curriculum_id' => $curriculum_id], ['%d']);
                $wpdb->delete($table_teachers, ['curriculum_id' => $curriculum_id], ['%d']);

                // Insert Sections
                if (!empty($sub['sections']) && is_array($sub['sections'])) {
                    foreach ($sub['sections'] as $sec_id) {
                        $wpdb->insert($table_sections, [
                            'curriculum_id' => $curriculum_id,
                            'section_id'    => absint($sec_id)
                        ], ['%d', '%d']);
                    }
                }

                // Insert Teachers
                if (!empty($sub['teachers']) && is_array($sub['teachers'])) {
                    foreach ($sub['teachers'] as $staff_id) {
                        $wpdb->insert($table_teachers, [
                            'curriculum_id' => $curriculum_id,
                            'staff_id'      => absint($staff_id)
                        ], ['%d', '%d']);
                    }
                }
            }
        }

        /**
         * 6. CLEANUP: Delete any subjects that were NOT in the form submission
         * (Meaning the user clicked the 'X' button to remove them)
         */
        if (!empty($processed_ids)) {
            $placeholders = implode(',', array_fill(0, count($processed_ids), '%d'));
            
            // Find orphans (subjects belonging to this class/year NOT in our processed list)
            $orphans = $wpdb->get_col($wpdb->prepare(
                "SELECT id FROM $table_main WHERE class_id = %d AND academic_year = %s AND id NOT IN ($placeholders)",
                array_merge([$class_id, $current_year], $processed_ids)
            ));

            if (!empty($orphans)) {
                $orphan_placeholders = implode(',', array_fill(0, count($orphans), '%d'));
                // Clear their pivots first (Good database hygiene)
                $wpdb->query($wpdb->prepare("DELETE FROM $table_sections WHERE curriculum_id IN ($orphan_placeholders)", ...$orphans));
                $wpdb->query($wpdb->prepare("DELETE FROM $table_teachers WHERE curriculum_id IN ($orphan_placeholders)", ...$orphans));
                // Delete the main records
                $wpdb->query($wpdb->prepare("DELETE FROM $table_main WHERE id IN ($orphan_placeholders)", ...$orphans));
            }
        } 

        // 7. Success Redirect back to the list
        wp_redirect(admin_url('admin.php?page=dedu-subjects-assign&message=class_subjects_assigned'));
        exit;
    } 
    
}