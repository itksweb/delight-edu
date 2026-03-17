<?php
namespace DelightEDU\Controllers\Admin\Submenus;

use DelightEDU\Controllers\Admin\MainRoot\SettingsController;
class MainRootSubmenus {

    private $parent_slug = 'delight-edu';

    public function __construct() {
        add_action( 'dedu_register_submenus', [$this, 'add_parent_submenus'] );
    }

    public function add_parent_submenus() {

        $submenus = [
            [
                'p-title'    => __('Enquiries', 'delight-edu'),
                'm-title'    => __('Enquiries', 'delight-edu'),
                'slug'     => 'dedu-enquiries',
                'callback' => [$this, 'render_dashboard']
            ],
            [
                'p-title'    => __('Settings', 'delight-edu'),
                'm-title'    => __('Settings', 'delight-edu'),
                'slug'     => 'dedu-settings',
                'callback' => [new SettingsController(), 'render_settings_page']
            ],
            [
                'p-title'    => __('Setup School', 'delight-edu'),
                'm-title'    => __('Setup School', 'delight-edu'),
                'slug'     => 'dedu-setup-school',
                'callback' => [$this, 'render_dashboard']
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

    public function render_settings_page() {
        echo '<div class="wrap"><h1>DelightEDU Settings</h1><p>Welcome to your bespoke School Management System Settings page.</p></div>';
    }

    public function render_dashboard() {
        echo '<div class="wrap"><h1>DelightEDU Dashboard</h1><p>Welcome to your bespoke School Management System.</p></div>';
    }
}