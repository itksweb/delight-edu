<?php
$ddd = $data_name === "class subjects";
$cls = $ddd ? "hide-me":"";
// $new_id = $data_name === "session" ? "dedu-trigger-create-session": "show-form-btn";
$new_id = "show-form-btn";
?>
<div class="dedu-tab-header">
    <h3><?php echo "{$data_name} List"; ?></h3>
    <button id="<?php echo $new_id ?>" class='<?php echo "dedu-btn dedu-btn-primary {$cls}" ?>'  >
        <span class="dashicons dashicons-plus"></span>
        <?php echo "Add New {$data_name}"; ?>
    </button>
</div>