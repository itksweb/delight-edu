<?php
namespace DelightEDU\Database;

/**
 * Handles Plugin Database Migrations
 */
class Schema {
    public static function install() {
        // 1. Run your existing table creation logic
        self::create_tables();

        // 2. Run the roles creation logic
        self::create_roles();
        
        // 3. Clear rewrite rules (good practice for custom portals/slugs)
        flush_rewrite_rules();
    }

    private static function create_roles() {
        $roles = [
            'dedu_staff'   => 'Staff',
            'dedu_student' => 'Student',
            'dedu_parent'  => 'Parent'
        ];

        foreach ($roles as $role_id => $display_name) {
            // add_role returns the role object or null if it already exists
            add_role($role_id, $display_name, ['read' => true]);
        }
    }

    private static function create_tables(){
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        //-- 1. Custom Staff Roles Table
        $table_staff_roles = $wpdb->prefix . 'dedu_staff_roles';
        $sql_staff_roles = "CREATE TABLE $table_staff_roles (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            role_name varchar(100) NOT NULL,
            role_slug varchar(100) NOT NULL, -- e.g., 'junior-accountant'
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY role_slug (role_slug)
        ) $charset_collate;";

        //-- 2a. Role Capabilities Mapping Table
        $table_capabilities = $wpdb->prefix . 'dedu_role_capabilities';
        $sql_capabilities = "CREATE TABLE $table_capabilities (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            role_id bigint(20) NOT NULL,
            capability varchar(100) NOT NULL, -- e.g., 'edit_grades', 'view_reports'
            PRIMARY KEY  (id),
            KEY role_id (role_id)
        ) $charset_collate;";

        //-- 2b: Staff Specific Capabilities Table
        $table_staff_caps = $wpdb->prefix . 'dedu_staff_capabilities';
        $sql_staff_caps = "CREATE TABLE $table_staff_caps (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            staff_id bigint(20) NOT NULL,
            capability varchar(100) NOT NULL,
            PRIMARY KEY  (id),
            KEY staff_id (staff_id)
        ) $charset_collate;";

        // -- 3. Classes Table
        $table_classes = $wpdb->prefix . 'dedu_classes';
        $sql_classes = "CREATE TABLE $table_classes (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            class_name varchar(100) NOT NULL,
            numeric_name int(11) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY class_unique (class_name)
        ) $charset_collate;";

        

        //-- 4. Sections Table
        $table_sections = $wpdb->prefix . 'dedu_sections';
        $sql_sections = "CREATE TABLE $table_sections (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            section_name varchar(100) NOT NULL,
            section_category varchar(100) DEFAULT NULL,
            capacity int(11) DEFAULT 0,
            class_id bigint(20) UNSIGNED NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY class_id (class_id)
        ) $charset_collate;";


        //-- 5. Staff Table
        $table_staff = $wpdb->prefix . 'dedu_staff';
        $sql_staff = "CREATE TABLE $table_staff (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            wp_user_id bigint(20) UNSIGNED DEFAULT NULL,
            staff_id_number varchar(50) NOT NULL,
            profile_picture_id bigint(20) UNSIGNED DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            middle_name varchar(100) DEFAULT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            gender varchar(20) DEFAULT NULL,
            marital_status varchar(20) DEFAULT 'single',
            date_of_birth date DEFAULT NULL,
            role_id bigint(20) UNSIGNED DEFAULT NULL,
            position varchar(100) DEFAULT NULL,
            working_hours varchar(20) DEFAULT 'full-time',
            is_teacher tinyint(1) NOT NULL DEFAULT 0,
            salary_amount decimal(10, 2) DEFAULT 0.00,
            joining_date date DEFAULT NULL,
            class_id bigint(20) UNSIGNED DEFAULT NULL,
            section_id bigint(20) UNSIGNED DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            capabilities text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY email_unique (email),
            UNIQUE KEY staff_id_unique (staff_id_number),
            KEY role_id (role_id)
        ) $charset_collate;";

        //-- 6. Students Table (Relational)
        $table_students = $wpdb->prefix . 'dedu_students';
        $sql_students = "CREATE TABLE $table_students (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            profile_picture_id bigint(20) UNSIGNED DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            middle_name varchar(100) DEFAULT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            address varchar(100) DEFAULT NULL,
            gender enum('male', 'female') DEFAULT 'male',
            marital_status varchar(20) DEFAULT 'single',
            blood_group varchar(20) DEFAULT NULL,
            date_of_birth date DEFAULT NULL,
            class_id bigint(20) NOT NULL, -- Links to our classes table
            section_id bigint(20) DEFAULT NULL,
            admission_no varchar(50) NOT NULL,
            roll_no varchar(50),
            status enum('active', 'suspended', 'graduated') DEFAULT 'active',
            PRIMARY KEY  (id),
            UNIQUE KEY admission_no (admission_no)
        ) $charset_collate;";

        $table_parents = $wpdb->prefix . 'dedu_parents';
        $sql_parents = "CREATE TABLE $table_parents (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            wp_user_id bigint(20) UNSIGNED DEFAULT NULL,
            profile_picture_id bigint(20) UNSIGNED DEFAULT NULL,
            relationship varchar(20) NOT NULL,
            first_name varchar(100) NOT NULL,
            middle_name varchar(100) DEFAULT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            address varchar(100) DEFAULT NULL,
            marital_status varchar(20) DEFAULT 'single',
            PRIMARY KEY  (id),
        ) $charset_collate;";

        $table_student_parent = $wpdb->prefix . 'dedu_parents_student_mapping';
        $sql_student_parent = "CREATE TABLE $table_student_parent (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            student_id bigint(20) NOT NULL,
            parent_id bigint(20) NOT NULL,
            relationship_type varchar(50) DEFAULT 'father', /* Mother, Father, Guardian */
            is_emergency_contact boolean DEFAULT false,
            PRIMARY KEY  (id),
            KEY student_id (student_id),
            KEY parent_id (parent_id)
        ) $charset_collate;";

        //-- 7. Master Subjects Table (The "What")
        $table_subjects_master = $wpdb->prefix . 'dedu_subjects_master';
        $sql_subjects_master = "CREATE TABLE $table_subjects_master (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            subject_name varchar(100) NOT NULL,
            subject_type enum('core', 'elective', 'vocational') DEFAULT 'core',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY subject_name (subject_name)
        ) $charset_collate;";

        //-- 8a. Class Subjects Mapping (The "Curriculum")
        $table_class_subjects = $wpdb->prefix . 'dedu_class_subjects';
        $sql_class_subjects = "CREATE TABLE $table_class_subjects (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            class_id bigint(20) UNSIGNED NOT NULL,
            subject_id bigint(20) UNSIGNED NOT NULL,      -- Link to Master List
            section_id bigint(20) UNSIGNED NULL,
            subject_code varchar(20) DEFAULT NULL,        -- e.g., 'MTH101'
            pass_mark int(3) DEFAULT 40,                 -- Specific to this class
            is_optional tinyint(1) DEFAULT 0,                -- Is it an elective for this class?
            academic_year varchar(20) DEFAULT NULL, -- <--- MAKE SURE THIS LINE IS HERE
            PRIMARY KEY  (id),
            KEY class_id (class_id),
            KEY subject_id (subject_id)
        ) $charset_collate;";

        //-- 8b. Class Subjects to Sections Mapping 
        $table_subject_sections = $wpdb->prefix . 'dedu_subject_sections';
        $sql_subject_sections = "CREATE TABLE $table_subject_sections (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            curriculum_id bigint(20) UNSIGNED NOT NULL, -- Links to dedu_class_subjects
            section_id bigint(20) UNSIGNED NOT NULL,    -- Links to dedu_sections
            PRIMARY KEY  (id),
            KEY curriculum_id (curriculum_id),
            KEY section_id (section_id)
        ) $charset_collate;";

        //-- 8c. Class Subjects to Teachers Mapping
        $table_subject_teachers = $wpdb->prefix . 'dedu_subject_teachers';
        $sql_subject_teachers = "CREATE TABLE $table_subject_teachers (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            curriculum_id bigint(20) UNSIGNED NOT NULL, -- Links to dedu_class_subjects
            staff_id bigint(20) UNSIGNED NOT NULL,      -- Links to dedu_staff
            PRIMARY KEY  (id),
            KEY curriculum_id (curriculum_id),
            KEY staff_id (staff_id)
        ) $charset_collate;";

        //-- 9. Staff Assignments Table (The "Who, Where, and What")
        $table_staff_assignments = $wpdb->prefix . 'dedu_staff_assignments';
        $sql_staff_assignments = "CREATE TABLE $table_staff_assignments (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            staff_id bigint(20) UNSIGNED NOT NULL,
            subject_id bigint(20) UNSIGNED NOT NULL,
            class_id bigint(20) UNSIGNED NOT NULL,
            section_id bigint(20) UNSIGNED DEFAULT NULL, -- NULL means \"All Sections\" in that class
            is_primary_teacher tinyint(1) DEFAULT 0,         -- To identify the 'Form Teacher'
            academic_year varchar(20) DEFAULT NULL,       -- e.g., '2025-2026'
            PRIMARY KEY  (id),
            KEY staff_id (staff_id),
            KEY subject_id (subject_id),
            KEY class_id (class_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_staff_roles );
        dbDelta( $sql_capabilities );
        dbDelta( $sql_staff_caps );
        dbDelta( $sql_classes );
        dbDelta( $sql_sections );
        dbDelta( $sql_staff );
        dbDelta( $sql_students );
        dbDelta( $sql_parents );
        dbDelta( $sql_student_parent );
        dbDelta( $sql_subjects_master );
        dbDelta( $sql_class_subjects );
        dbDelta( $sql_staff_assignments );
        dbDelta($sql_subject_sections );
        dbDelta($sql_subject_teachers );
    }
}