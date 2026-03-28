<?php
$ddd = $data_name === "class subjects";
$cls = $ddd ? "hide-me":"";
?>
<div class="dedu-tab-header">
    <h3><?php echo "{$data_name} List"; ?></h3>
    <button id="show-form-btn" class='<?php echo "dedu-btn dedu-btn-primary {$cls}" ?>'  >
        <span class="dashicons dashicons-plus"></span>
        <?php echo "Add New {$data_name}"; ?>
    </button>
</div>