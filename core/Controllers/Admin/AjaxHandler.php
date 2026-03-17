<?php
namespace DelightEDU\Controllers\Admin;
use DelightEDU\Controllers\Admin\Admin\StaffController;

class AjaxHandler {
    public function __construct() {
        // Register all AJAX hooks here. 
        // This class is instantiated regardless of the Menu rendering.
        add_action('wp_ajax_get_staff_details', [$this, 'handle_get_staff_details']);
        // Add other AJAX hooks here...
    }
    public function handle_get_staff_details() {
        // Instantiate the controller inside the handler to be sure
        $controller = new StaffController();
        $controller->ajax_get_staff_details();
        wp_die(); // Always end AJAX with wp_die()
    }
}