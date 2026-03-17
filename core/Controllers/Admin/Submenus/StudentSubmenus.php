<?php
namespace DelightEDU\Controllers\Admin\Submenus;

use DelightEDU\Models\Classes;
use DelightEDU\Models\Section;

class StudentSubmenus {

    private $parent_slug = 'dedu-student-root';

    public function __construct() {
        add_action( 'dedu_register_submenus', [$this, 'add_student_submenus'] );
    }

    public function add_student_submenus() {

        $submenus = [
            [
                'p-title'    => __('Students', 'delight-edu'),
                'm-title'    => __('Students', 'delight-edu'),
                'slug'     => 'dedu-students',
                'callback' => [$this, 'render_students_management_page']
            ],
            [
                'p-title'    => __('Print ID Cards', 'delight-edu'),
                'm-title'    => __('Print ID Cards', 'delight-edu'),
                'slug'     => 'dedu-student-id',
                'callback' => [$this, 'render_student_id_dashboard']
            ],
            [
                'p-title'    => __('Certificates', 'delight-edu'),
                'm-title'    => __('Certificates', 'delight-edu'),
                'slug'     => 'dedu-student-certificates',
                'callback' => [$this, 'render_student_id_dashboard']
            ],
            [
                'p-title'    => __('Promote', 'delight-edu'),
                'm-title'    => __('Promote', 'delight-edu'),
                'slug'     => 'dedu-promote',
                'callback' => [$this, 'render_student_id_dashboard']
            ],
            [
                'p-title'    => __('Notifications', 'delight-edu'),
                'm-title'    => __('Notifications', 'delight-edu'),
                'slug'     => 'dedu-student-notification',
                'callback' => [$this, 'render_student_id_dashboard']
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


    public function render_student_management_dashboard() {
        echo '<div class="wrap"><h1>DelightEDU Student Management Dashboard</h1><p>Welcome to your bespoke Student Management Dashboard.</p></div>';
    }


    public function render_students_management_page() {
        echo '<div class="wrap"><h1>DelightEDU Students Management Dashboard</h1><p>Welcome to your bespoke Students Management Dashboard.</p></div>';
    }
    public function render_student_id_dashboard() {
        echo '<div class="wrap"><h1>DelightEDU Student ID Card Dashboard</h1><p>Welcome to your bespoke Student ID Card Dashboard.</p></div>';
    }
}