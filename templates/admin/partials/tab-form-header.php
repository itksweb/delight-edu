<?php
$text = $data_name === "class subjects" ? "Assign subjects to class" : "Add A New {$data_name}";
?>
<div class="dedu-tab-header">
    <h3><?php echo $text; ?></h3>
    <button id="show-list-btn" class="dedu-btn dedu-btn-primary">
        <span class="dashicons dashicons-list-view"></span>
        <?php echo "Back to {$data_name} List"; ?>
    </button>
</div>