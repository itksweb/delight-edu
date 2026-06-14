<?php
$name = $dt_name ? $dt_name : "";
$value = $dt_value ? $dt_value : "";
$requ = $req ? true: false;
?>

<div class="custom-date-picker">
      <input type="text" id="" name="<?php echo $name ?>" value="<?php echo $value ?>" class="date-input" placeholder="Select a date" readonly />
      <div class="calendar-dropdown">
        <div class="calendar-header">
          <button type="button" class="prev-btn" aria-label="Previous month">&lt;</button>
          <span class="month-year-label">October 2026</span>
          <button type="button" class="next-btn" aria-label="Next month">&gt;</button>
        </div>

        <div class="calendar-weekdays">
          <div>Su</div>
          <div>Mo</div>
          <div>Tu</div>
          <div>We</div>
          <div>Th</div>
          <div>Fr</div>
          <div>Sa</div>
        </div>

        <div class="calendar-days" id="calendar-days-grid"></div>
      </div>
</div>


<script>
    const requ = <?php echo json_encode($req); ?>;
    const name = <?php echo json_encode($name); ?>;
    const id = <?php echo json_encode($dt_id); ?>;
    const dateIn = document.querySelector(`input[name="${name}"]`);
    dateIn.id = id ? id:"";
    
    requ 
        ? dateIn.required = true 
        : dateIn.required = false;
</script>
