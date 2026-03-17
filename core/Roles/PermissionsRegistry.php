<?php
namespace DelightEDU\Roles;

class PermissionsRegistry {

    /**
     * Returns a grouped array of all available capabilities.
     * This structure is perfect for rendering grouped checkboxes in the UI.
     */
    public static function get_all() {
        return [
            'Students' => [
                'view_students'   => 'View Student Records',
                'add_students'    => 'Admit New Students',
                'edit_students'   => 'Edit Student Info',
                'delete_students' => 'Remove Students',
                'manage_student_promotion'  => 'Manage Student Promotion',
                'manage_id_cards'  => 'Manage ID Cards'

            ],
            'Assignment Scope' => [
                'only_assigned_class'  =>  'Only Assigned Class',
                'only_assigned_subjects' => 'Only Assigned Subjects' 
            ],
            'Student Attendance'  => [
                'view_student Attendance'  =>  'View Student Attendance',
                'take _student_attendance'  =>  'Take Student Attendance'
            ],
            'Staff Attendance'  => [
                'view_staff Attendance'  =>  'View Staff Attendance',
                'take _staff_attendance'  =>  'Take Staff Attendance'
            ],
            'Classes & Sections'  => [
                'manage_classes'  => 'Manage Classes & Sections',
                'delete_classes'  => 'Delete Classes & Sections',
            ],
            'Roles'  => [
                'manage_roles'  => 'Manage Roles',
                'delete_roles'  => 'Delete Roles',
            ],
            'Settings'  => [
                'manage_settings'  => 'Manage Settings',
                'delete_settings'  => 'Delete Settings',
            ],
            'subjects' => [
                'add_subjects' => 'Add Subjects',
                'view_subjects' => 'View Subjects',
                'edit_subjects'    => 'Edit Subjects',
                'delete_subjects'     => 'Delete Subjects',
            ],
            'Examination' => [
                'add_exam' => 'Add Exam',
                'view_exam' => 'View Exam',
                'edit_exam'    => 'Edit Exam',
                'delete_exam'     => 'Delete Exam',
                'add_exam_result' => 'Add Exam Result',
                'view_exam_result' => 'View Exam Result',
                'edit_exam_result'    => 'Edit Exam Result',
                'delete_exam_result'     => 'Delete Exam Result',
            ],
            'Timetable' => [
                'add_timetable' => 'Add Timetable',
                'view_timetable' => 'View Timetable',
                'edit_timetable'    => 'Edit Timetable',
                'delete_timetable'     => 'Delete Timetable',
            ],
            'staff' => [
                'add_staff' => 'Add Staff',
                'view_staff' => 'View Staff',
                'edit_staff'    => 'Edit Staff',
                'delete_staff'     => 'Delete Staff',
            ],
            'Student Leave' => [
                'add_student_leave' => 'Add Student Leave',
                'view_student_leave' => 'View Student Leave',
                'edit_student_leave'    => 'Edit Student Leave',
                'delete_student_leave'     => 'Delete Student Leave',
            ],
            'Staff Leave' => [
                'add_staff_leave' => 'Add Staff Leave',
                'view_staff_leave' => 'View Staff Leave',
                'edit_staff_leave'    => 'Edit Staff Leave',
                'delete_staff_leave'     => 'Delete Staff Leave',
            ],
            'Certificates' => [
                'add_certificates' => 'Add Certificates',
                'view_certificates' => 'View Certificates',
                'edit_certificates'    => 'Edit Certificates',
                'delete_certificates'     => 'Delete Certificates',
                'issue_certificates'     => 'issue Certificates'
            ],
            'Study Materials' => [
                'view_study_materials' => 'View Study Materials',
                'add_edit_study_materials' => 'Add/Edit Study Materials',
                'delete_study_materials'    => 'Delete Study Materials',
                ],
            'Accounting' => [
                'add_expense' => 'Add Expense',
                'view_expense' => 'View Expense',
                'edit_expense'    => 'Edit Expense',
                'delete_expense'     => 'Delete Expense',
                'add_income' => 'Add Income',
                'view_income' => 'View Income',
                'edit_income'    => 'Edit Income',
                'delete_income'     => 'Delete Income',
                'add_fee_type' => 'Add Fee Type',
                'view_fee_type' => 'View Fee Type',
                'edit_fee_type'    => 'Edit Fee Type',
                'delete_fee_type'     => 'Delete Fee Type',
                'add_invoice' => 'Add Invoice',
                'view_invoice' => 'View Invoice',
                'edit_invoice'    => 'Edit Invoice',
                'delete_invoice'     => 'Delete Invoice',
            ],
            
        ];
    }

    /**
     * Get a flat list of just the keys (e.g., ['view_students', 'add_students', ...])
     */
    public static function get_list() {
        $flat_list = [];
        foreach ( self::get_all() as $group => $caps ) {
            foreach ( $caps as $key => $label ) {
                $flat_list[] = $key;
            }
        }
        return $flat_list;
    }
}