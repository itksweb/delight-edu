
<?php
if (!defined('ABSPATH')) exit;

$students = $students ?? [];
$classes       = $classes ?? [];
$part = \DEDU_PATH . 'templates/admin/partials';
$data_name = "attendance";
$tspan = "6"
?>
<div class="wrap list-form dedu-admin-wrapper" data-type="<?php echo $data_name ?>"> 
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Students Attendance Roaster</h1>
    </div>
    
    
    <div class="" id="dedu-form-view"  >
        <form id="dedu-attendance-filter-form">
            <div class="dedu-card">
                <fieldset class = "attendance-fields">
                    <legend class = "dedu-card-title"><h2></h2></legend>
                    <input type="hidden" id="btn-action" value="" disabled>
                    <div class = "unit" >
                        <label>Select Class</label>
                        <select name="class_id" id="class-field" required>
                            <option value="">-- No Class Selected --</option>
                            <?php foreach ($classes as $c) : ?>
                                <option value="<?php echo $c->id; ?>"><?php echo esc_html($c->class_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Select Section</label>
                        <select name="section_id" id="sections-field" >
                            <option value="" disabled selected>-- select a class first --</option>
                        </select>
                    </div>
                    <div class="unit">
                        <label>Attendance Date</label>
                        <?php 
                            $dt_id="attendance_date_input";
                            $dt_name="attendance_date";
                            $dt_value = date('Y-m-d');
                            $req = true;
                            include("{$part}/date-picker.php");
                        ?>
                        <!-- <input type="date" id="attendance_date_input" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required> -->
                    </div>
                    
                </fieldset>
            </div>
            <div class="dedu-form-actions">
                <button type="submit" id="view" class="sub-btn dedu-btn dedu-btn-primary">  
                    View Attendance
                </button>
                <button type="submit" id="take" class="sub-btn dedu-btn dedu-btn-primary">  
                    Take Attendance
                </button>
            </div>
        </form>                  
    </div>

    <div class="dedu-card hide-me" id="dedu-attendance-roster-card">
        <form id="dedu-attendance-sheet-form">
            <?php wp_nonce_field('dedu_save_attendance_action', 'attendance_nonce'); ?>
            <input type="hidden" name="term_id" value="">
            
            <div class="dedu-table-container">
                <table class="dedu-table-modern" id="attendance-roster-table" style="min-width: 400px;">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Student Name</th>
                            <th style="width: 40%; text-align: center;">Status Toggles</th>
                            <th style="width: 20%;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-students-body">
                        <template id="student-row">
                            <tr class="attendance-row">
                                <td>
                                    <input type="hidden" data-name="section_id" name="" value = "0">
                                    <p class="text-heading"></p>
                                </td>
                                <td style="text-align: center;">
                                    <div class="dedu-status-toggle-group">
                                        <label class="toggle-lbl present-lbl">
                                            <input type="radio" name="" value="present"><span>P</span>
                                        </label>
                                        <label class="toggle-lbl absent-lbl">
                                            <input type="radio" name="" value="absent"><span>A</span>
                                        </label>
                                        <label class="toggle-lbl late-lbl">
                                            <input type="radio" name="" value="late"><span>L</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="attendance[][remarks]" value="" placeholder="Notes..." class="widefat remarks">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="dedu-form-actions" style="margin-top: 20px; text-align: right;">
                <button type="submit" id="take-att" class="dedu-btn dedu-btn-success">Take Attendance</button>
            </div>
        </form>
    </div>
</div>

<style>
    .attendance-fields{
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 20px;
    }
    /* This class freezes the input without changing its looks */
    .read-only-checkbox {
        pointer-events: none;
        cursor: default; /* Prevents the pointer hand icon */
        user-select: none;
    }
</style>