<?php
namespace DelightEDU\Controllers\Admin;

use DelightEDU\Controllers\Admin\Submenus\MainRootSubmenus;
use DelightEDU\Controllers\Admin\Submenus\AdministratorSubmenus;
use DelightEDU\Controllers\Admin\Submenus\StudentSubmenus;
use DelightEDU\Controllers\Admin\Submenus\AcademicSubmenus;
use DelightEDU\Controllers\Admin\Submenus\ExamSubmenus;
Use DelightEDU\Assets\Admin\AssetProvider;

class Menu {

    private $parent_slug = 'delight-edu';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menus' ] );
        add_action( 'admin_notices', [ $this, 'display_global_toast' ] ); 
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    public function register_menus() {

        $submenus = [
            [
                'p-title'    => __('DelightEDU', $this->parent_slug),
                'm-title'    => __('DelightEDU', $this->parent_slug),
                'slug'     => $this->parent_slug,
                'callback' => [ new MainRootSubmenus(), 'render_dashboard' ],
                'icon' => 'dashicons-welcome-learn-more', 
                'priority' => 25
            ],
            [
                'p-title'    => __('Administrator', $this->parent_slug),
                'm-title'    => __('Administrator', $this->parent_slug),
                'slug'     => 'dedu-admin',
                'callback' => [ new AdministratorSubmenus(), 'render_admin_dashboard' ],
                'icon' => 'dashicons-welcome-learn-more', 
                'priority' => 26
            ],
            [
                'p-title'    => __('Student Management', $this->parent_slug),
                'm-title'    => __('Student Management', $this->parent_slug),
                'slug'     => 'dedu-student-root',
                'callback' => [new StudentSubmenus(), 'render_student_management_dashboard'],
                'icon' => 'dashicons-book-alt', 
                'priority' => 29
            ],
            [
                'p-title'    => __('Academics', $this->parent_slug),
                'm-title'    => __('Academics', $this->parent_slug),
                'slug'     => 'dedu-academic-root',
                'callback' => [new AcademicSubmenus(), 'render_academic_dashboard'],
                'icon' => 'dashicons-book-alt', 
                'priority' => 27
            ],
            [
                'p-title'    => __('Examination', $this->parent_slug),
                'm-title'    => __('Examination', $this->parent_slug),
                'slug'     => 'dedu-exam-root',
                'callback' => [new ExamSubmenus(), 'render_exam_dashboard'],
                'icon' => 'dashicons-book-alt', 
                'priority' => 28
            ],
            
        ];

        foreach ( $submenus as $sub ) {
            add_menu_page(
                $sub['p-title'],           // Page Title
                $sub['m-title'],           // Menu Title
                'manage_options',        // Capability
                $sub['slug'],            // Slug
                $sub['callback'],         // Callback
                $sub['icon'],           // Icon
                $sub['priority']        // Priority
            );
        }

        // CRITICAL: Trigger a custom action so controller submenus can "plug in"
        do_action( 'dedu_register_submenus' );

    }

    public function display_global_toast() {
        // 1. Only show on our plugin pages to stay "polite" in the WP admin
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'dedu' ) === false ) {
            return;
        }

        // 2. Extract message or error keys
        $message_key = isset( $_GET['message'] ) ? sanitize_text_field( $_GET['message'] ) : '';
        $error_key   = isset( $_GET['error'] ) ? sanitize_text_field( $_GET['error'] ) : '';

        if ( empty( $message_key ) && empty( $error_key ) ) {
            return;
        }

        // 3. Centralized Map for all modules (Roles, Classes, etc.)
        $messages = [
            'role_created'    => 'Staff role created successfully.',
            'role_deleted'    => 'Staff role deleted successfully.',
            'role_updated'    => 'Staff role updated successfully.',
            'class_created'   => 'Class and sections created successfully.',
            'class_updated'   => 'Class updated successfully.',
            'class_deleted'   => 'Class removed successfully.',
            'class_subjects_assigned' => 'Class subjects assigned successfully',
            'bulk_deleted'    => isset($_GET['count']) ? absint($_GET['count']) . ' items deleted.' : 'Items deleted.',
            'save_failed'     => 'An error occurred while saving.',
            'delete_failed'   => 'Could not delete the record.',
            'staff_deleted' => 'Staff deleted successfully',
            'staff_created' => 'Staff created successfully',
            'staff_updated' => 'Staff updated successfully',
            'subject_created' => 'Subject successfully added to master list',
            'subject_updated' => 'Subject successfully updated in master list',
            'subject_deleted'   => 'Subject removed successfully.',
        ];

        $text = '';
        $is_error = ! empty( $error_key );
        $key = $is_error ? $error_key : $message_key;

        if ( isset( $messages[ $key ] ) ) {
            $text = $messages[ $key ];
        }

        if ( $text ) : ?>
            <div id="dedu-toast" class="dedu-toast <?php echo $is_error ? 'dedu-toast-error' : 'dedu-toast-success'; ?>">
                <span class="dashicons <?php echo $is_error ? 'dashicons-warning' : 'dashicons-yes-alt'; ?> dedu-toast-icon"></span>
                <div><strong><?php echo $is_error ? 'Error!' : 'Success!'; ?></strong> <?php echo esc_html( $text ); ?></div>
            </div>
        <?php endif;
    }
    
    // Enqueue CSS and JS for the Admin Dashboard
    public function enqueue_admin_assets( $hook ) {
        $is_our_plugin = strpos( $hook, 'dedu' ) !== false ;

        if ( ! $is_our_plugin ) {
            return;
        }

         //  Enqueue base JS
        wp_enqueue_script('dedu-base', \DEDU_URL . 'assets/js/base.js', [], '1.1.4', true );

        //Selectively enqueue the appropriate js script for each page
        $parts = explode('_page_', $hook);
        $slug = end($parts);
        $file_path = \DEDU_PATH . 'assets/js/pages/' . $slug . '.js';
        $file_url  = \DEDU_URL . 'assets/js/pages/' . $slug . '.js';
        
        if (file_exists($file_path)) {
           // 1. Enqueue the script as you normally do
            wp_enqueue_script( $slug, $file_url, [], '1.2.1', true );

            // 2. Add Conditional Localization
            if ( 'dedu-staff' === $slug ) {
                wp_localize_script( $slug, 'deduStaffData', AssetProvider::get_staff_data()
            );
            }
        }

        // Enqueue Your JS
        wp_enqueue_script(
            'dedu-admin-scripts', 
            \DEDU_URL . 'assets/js/admin-scripts.js', 
            ['jquery'], 
            '1.1.4', 
            true 
        );
        

        // Enqueue Your CSS
        wp_enqueue_style( 
            'dedu-admin-style', 
            \DEDU_URL . 'assets/css/admin-style.css', 
            [], 
            '1.0.0' 
        );

        
    }
}