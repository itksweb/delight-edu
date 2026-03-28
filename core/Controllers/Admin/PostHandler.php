<?php
namespace DelightEDU\Controllers\Admin;

use DelightEDU\Controllers\Admin\Academics\ClassSectionController;
use DelightEDU\Controllers\Admin\Academics\SubjectsController;
use DelightEDU\Controllers\Admin\Admin\StaffController;
use DelightEDU\Controllers\Admin\Admin\RolesController;

class PostHandler {

    public function __construct() {

        /*==========================================
          ==>  STAFF ROLES HOOKS  
        ==========================================*/
        // This hook matches the <input type="hidden" name="action" value="dedu_save_role">
        add_action( 'admin_post_dedu_save_role', [ new RolesController(), 'save_staff_role' ] );
        add_action( 'admin_post_dedu_delete_role', [ new RolesController(), 'delete_staff_role' ] );
        add_action('admin_post_dedu_bulk_action_roles', [new RolesController(), 'handle_bulk_actions']);


        /*==========================================
          ==>  CLASSES AND SECTIONS HOOKS  
        ==========================================*/
        add_action( 'admin_post_dedu_save_class_complex', [ new ClassSectionController(), 'save_class_with_sections' ] );
        add_action( 'admin_post_dedu_delete_class', [ new ClassSectionController(), 'delete_class' ] );
        add_action('wp_ajax_dedu_save_class_ajax', [new ClassSectionController(), 'handle_save_class_ajax']);
        // If you want it to work for non-admins (usually not for deletes, but for completeness)
        // add_action( 'admin_post_nopriv_dedu_delete_class', [ $this, 'delete_class' ] );

        /*==========================================
          ==>  SUBJECTS HOOKS  
        ==========================================*/
        add_action( 'admin_post_dedu_save_master_subject', [ new SubjectsController(), 'handle_save_master_subject' ] );
        add_action( 'admin_post_dedu_delete_master_subject', [ new SubjectsController(), 'delete_master_subject' ] );
        add_action( 'admin_post_dedu_assign_subject_to_class', [ new SubjectsController(), 'handle_assign_subject_to_class' ] );
        add_action('admin_post_dedu_bulk_save_curriculum', [new SubjectsController(), 'handle_bulk_save_class_subjects']);

        /*==========================================
          ==>  STAFF MODULE HOOKS  
        ==========================================*/
        add_action( 'admin_post_dedu_save_staff', [ new StaffController(), 'handle_save_staff' ] );
        add_action( 'admin_post_dedu_delete_staff', [ new StaffController(), 'handle_delete_staff' ] );
    }

    
}