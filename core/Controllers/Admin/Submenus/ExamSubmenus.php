<?php
namespace DelightEDU\Controllers\Admin\Submenus;


class ExamSubmenus {

    private $parent_slug = 'dedu-exam-root';
    public function __construct() {
        add_action( 'dedu_register_submenus', [$this, 'add_exam_submenus'] );
    }

    public function add_exam_submenus() {

        $submenus = [
            [
                'p-title'    => __('Exam setup', 'delight-edu'),
                'm-title'    => __('Exam setup', 'delight-edu'),
                'slug'     => 'dedu-exam-setup',
                'callback' => [$this, 'render_exam_page']
            ],
            
            [
                'p-title'    => __('Manage Exams', 'delight-edu'),
                'm-title'    => __('Manage Exams', 'delight-edu'),
                'slug'     => 'dedu-manage-exam',
                'callback' => [$this, 'render_exam_page']
            ],
            [
                'p-title'    => __('Exam result', 'delight-edu'),
                'm-title'    => __('Exam result', 'delight-edu'),
                'slug'     => 'dedu-exam-result',
                'callback' => [$this, 'render_exam_page']
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
   
    public function render_exam_dashboard() {
        echo '<div class="wrap"><h1>DelightEDU Administrator Dashboard</h1><p>Welcome to your bespoke School Management Administrator Dashboard.</p></div>';
    }
    public function render_exam_page() {
        echo '<div class="wrap"><h1>DelightEDU Examination mangement Page</h1><p>Welcome to your bespoke Examination Management Page.</p></div>';
    }
}