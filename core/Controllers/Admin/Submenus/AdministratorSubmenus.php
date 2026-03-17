<?php
namespace DelightEDU\Controllers\Admin\Submenus;

use DelightEDU\Controllers\Admin\Admin\RolesController;
use DelightEDU\Controllers\Admin\Admin\StaffController;

class AdministratorSubmenus {

    private $parent_slug = 'dedu-admin';
    public function __construct() {
        add_action( 'dedu_register_submenus', [$this, 'add_admin_submenus'] );
    }

    public function add_admin_submenus() {

        $submenus = [
            [
                'p-title'    => __('Staff Role', 'delight-edu'),
                'm-title'    => __('Staff Role', 'delight-edu'),
                'slug'     => 'dedu-roles',
                'callback' => [new RolesController(), 'render_role_page']
            ],
            [
                'p-title'    => __('Staff List', 'delight-edu'),
                'm-title'    => __('Staff List', 'delight-edu'),
                'slug'     => 'dedu-staff',
                'callback' => [new StaffController(), 'render_staff_page']
            ],
            [
                'p-title'    => __('Staff Attendance', 'delight-edu'),
                'm-title'    => __('Staff Attendance', 'delight-edu'),
                'slug'     => 'dedu-staff-attendance',
                'callback' => [$this, 'render_admin_staff_dashboard']
            ],
            [
                'p-title'    => __('Staff ID Cards', 'delight-edu'),
                'm-title'    => __('Staff ID Cards', 'delight-edu'),
                'slug'     => 'dedu-staff-id',
                'callback' => [$this, 'render_admin_staff_dashboard']
            ],
            [
                'p-title'    => __('Staff Leave', 'delight-edu'),
                'm-title'    => __('Staff Leave', 'delight-edu'),
                'slug'     => 'dedu-staff-leave',
                'callback' => [$this, 'render_admin_staff_dashboard']
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
   
    public function render_admin_dashboard() {
        echo '<div class="wrap"><h1>DelightEDU Administrator Dashboard</h1><p>Welcome to your bespoke School Management Administrator Dashboard.</p></div>';
    }
    public function render_admin_staff_dashboard() {
        echo '<div class="wrap"><h1>DelightEDU staff mangement Dashboard</h1><p>Welcome to your bespoke Staff Management Administrator Dashboard.</p></div>';
    }
}