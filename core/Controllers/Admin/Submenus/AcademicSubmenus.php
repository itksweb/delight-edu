<?php
namespace DelightEDU\Controllers\Admin\Submenus;

use DelightEDU\Controllers\Admin\Academics\ClassSectionController;
use DelightEDU\Controllers\Admin\Academics\SubjectsController;

class AcademicSubmenus {

    private $parent_slug = 'dedu-academic-root';
    public function __construct() {
        add_action( 'dedu_register_submenus', [$this, 'add_academic_submenus'] );
    }

    public function add_academic_submenus() {

        $submenus = [
            [
                'p-title'    => __('Manage Classes & Sections', 'delight-edu'),
                'm-title'    => __('Manage Classes & Sections', 'delight-edu'),
                'slug'     => 'dedu-classes-sections',
                'callback' => [new ClassSectionController(), 'render_class_section_page']
            ],
            [
                'p-title'    => __('Master Subjects', 'delight-edu'),
                'm-title'    => __('Master Subjects', 'delight-edu'),
                'slug'     => 'dedu-subjects',
                'callback' => [new SubjectsController(), 'render_master_list_view']
            ],
            [
                'p-title'    => __('Class Subjects', 'delight-edu'),
                'm-title'    => __('Class Subjects', 'delight-edu'),
                'slug'     => 'dedu-subjects-assign',
                'callback' => [new SubjectsController(), 'render_assign_subjects_view']
            ],
            [
                'p-title'    => __('Class Timetable', 'delight-edu'),
                'm-title'    => __('Class Timetable', 'delight-edu'),
                'slug'     => 'dedu-class-timetable',
                'callback' => [$this, 'render_academic_dashboard']
            ],
            [
                'p-title'    => __('Teacher Timetable', 'delight-edu'),
                'm-title'    => __('Teacher Timetable', 'delight-edu'),
                'slug'     => 'dedu-teacher-timetable',
                'callback' => [$this, 'render_academic_dashboard']
            ],
            [
                'p-title'    => __('Class Attendance', 'delight-edu'),
                'm-title'    => __('Class Attendance', 'delight-edu'),
                'slug'     => 'dedu-class-attendance',
                'callback' => [$this, 'render_academic_dashboard']
            ],
            [
                'p-title'    => __('Student Leave', 'delight-edu'),
                'm-title'    => __('Student Leave', 'delight-edu'),
                'slug'     => 'dedu-student-leave',
                'callback' => [$this, 'render_academic_dashboard']
            ],
            [
                'p-title'    => __('Study Materials', 'delight-edu'),
                'm-title'    => __('Study Materials', 'delight-edu'),
                'slug'     => 'dedu-study-materials',
                'callback' => [$this, 'render_academic_dashboard']
            ],
            [
                'p-title'    => __('Homework', 'delight-edu'),
                'm-title'    => __('Homework', 'delight-edu'),
                'slug'     => 'dedu-homework',
                'callback' => [$this, 'render_academic_dashboard']
            ],
            [
                'p-title'    => __('Noticeboard', 'delight-edu'),
                'm-title'    => __('Noticeboard', 'delight-edu'),
                'slug'     => 'dedu-noticeboard',
                'callback' => [$this, 'render_academic_dashboard']
            ],
            [
                'p-title'    => __('Events', 'delight-edu'),
                'm-title'    => __('Events', 'delight-edu'),
                'slug'     => 'dedu-events',
                'callback' => [$this, 'render_academic_dashboard']
            ],
            [
                'p-title'    => __('Student Birthdays', 'delight-edu'),
                'm-title'    => __('Student Birthdays', 'delight-edu'),
                'slug'     => 'dedu-student-birthdays',
                'callback' => [$this, 'render_academic_dashboard']
            ],
            
        ];

        foreach ( $submenus as $sub ) {
            add_submenu_page(
                $this->parent_slug,
                $sub['p-title'],           // Page Title
                $sub['m-title'],           // Menu Title
                'manage_options',        // Capability
                $sub['slug'],            // Slug
                $sub['callback']         // Callback
            );
        }

    }

    

    public function render_academic_dashboard() {
        echo '<div class="wrap"><h1>DelightEDU Academic Dashboard</h1><p>Welcome to your bespoke School Management Academic Dashboard.</p></div>';
    }
}