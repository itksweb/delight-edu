<?php
namespace DelightEDU\Controllers\Admin\Parents;

use DelightEDU\Models\ParentModel;

class ParentController {
    public function index() {
        $model = new ParentModel();
        $parents = $model->get_all();
        
        // Load the view (Assuming you have a helper to load templates)
        include DEDU_PATH . 'templates/parents/parent-list.php';
    }

    public function save_parent() {
        // Here we will handle:
        // 1. wp_create_user()
        // 2. Assigning 'dedu_parent' role
        // 3. Saving to dedu_parents table
    }
}