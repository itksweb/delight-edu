<?php
namespace DelightEDU\Controllers\Admin\Academics;

use DelightEDU\Models\Classes;


class ClassSectionController {   

    public function render_class_section_page() {
        $class_model = new Classes();

        // Get data for the lists
        $all_classes = $class_model->get_all_with_sections();

        // Include the view
        include DEDU_PATH . 'templates/admin/class-section-list-form.php';
    }

    public function render_academic_dashboard() {
        echo '<div class="wrap"><h1>DelightEDU Academic Dashboard</h1><p>Welcome to your bespoke School Management Academic Dashboard.</p></div>';
    }
}