<?php
if (!defined('ABSPATH')) exit;
// Assuming you have fetched or localized classes and the active term dates
?>

<div class="wrap dedu-admin-wrapper" data-type="attendance">
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Student Attendance Roster</h1>
    </div>

    <div class="dedu-card" style="margin-bottom: 20px;">
        <form id="dedu-attendance-filter-form" class="flex items-center gap-4">
            <div class="unit">
                <label>Select Class</label>
                <select id="attendance_class_select" name="class_id" required>
                    <option value="">-- Select Class --</option>
                    <?php foreach ($classes as $class) : ?>
                        <option value="<?php echo $class->id; ?>"><?php echo esc_html($class->class_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="unit">
                <label>Attendance Date</label>
                <input type="date" id="attendance_date_input" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="unit" style="align-self: flex-end;">
                <button type="submit" class="dedu-btn dedu-btn-primary">Load Roster</button>
            </div>
        </form>
    </div>

    <div class="dedu-card hide-me" id="dedu-attendance-roster-card">
        <form id="dedu-attendance-sheet-form">
            <?php wp_nonce_field('dedu_save_attendance_action', 'attendance_nonce'); ?>
            <input type="hidden" id="attendance_term_id" name="term_id" value="">
            
            <div class="dedu-table-container">
                <table class="dedu-table-modern" id="attendance-roster-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Student Name</th>
                            <th style="width: 40%; text-align: center;">Status Toggles</th>
                            <th style="width: 20%;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-students-body">
                        </tbody>
                </table>
            </div>

            <div class="dedu-form-actions" style="margin-top: 20px; text-align: right;">
                <button type="submit" class="dedu-btn dedu-btn-success">Save Roster Decisions</button>
            </div>
        </form>
    </div>
</div>