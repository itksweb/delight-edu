<?php
namespace DelightEDU\Controllers\Admin;
use DelightEDU\Controllers\Admin\Admin\StaffController;
use DelightEDU\Controllers\Admin\Students\StudentsController;
use DelightEDU\Controllers\Admin\MainRoot\SessionController;
use DelightEDU\Controllers\Admin\Academics\AttendanceController;


class AjaxHandler {
    public function __construct() {
        // Register all AJAX hooks here. 
        // This class is instantiated regardless of the Menu rendering.
        add_action('wp_ajax_get_staff_details', [$this, 'handle_get_staff_details']);
        add_action('wp_ajax_get_student_details', [$this, 'handle_get_student_details']);
        add_action('wp_ajax_get_session_terms', [$this, 'handle_get_session_terms']);
        // Add other AJAX hooks here...
        add_action( 'wp_ajax_dedu_create_session_async', [$this, 'handle_create_session_record'] );
        add_action( 'wp_ajax_dedu_update_session_async', [$this,'handle_update_session_record'] );

        add_action('wp_ajax_dedu_load_attendance_roaster', [$this,'handle_load_attendance_roaster'] );
        add_action('wp_ajax_dedu_save_attendance_sheet', [$this,'handle_save_attendance_sheet'] );
    }
    public function handle_get_staff_details() {
        // Instantiate the controller inside the handler to be sure
        $controller = new StaffController();
        $controller->ajax_get_staff_details();
        wp_die(); // Always end AJAX with wp_die()
    }

    public function handle_get_student_details() {
        // Instantiate the controller inside the handler to be sure
        $controller = new StudentsController();
        $controller->ajax_get_student_details();
        wp_die(); // Always end AJAX with wp_die()
    }

    public function handle_get_session_terms(){
        $controller = new SessionController();
        $controller->ajax_get_session_terms();
        wp_die();
    }

    public function handle_create_session_record() {
        $controller = new SessionController();
        $controller->ajax_handle_create_session_record();
        wp_die();
    }

    public function handle_update_session_record() {
        $controller = new SessionController();
        $controller->ajax_handle_update_session_record();
        wp_die();
    }

    public function handle_load_attendance_roaster(){
        $controller = new AttendanceController();
        $controller->ajax_handle_load_attendance_roaster();
        wp_die();
    }

    public function handle_save_attendance_sheet(){
        $controller = new AttendanceController();
        $controller->ajax_handle_save_attendance_sheet();
        wp_die();
    }
}