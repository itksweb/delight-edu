
<?php
if (!defined('ABSPATH')) exit;

$students = $students ?? [];
$classes       = $classes ?? [];
$part = \DEDU_PATH . 'templates/admin/partials';
$data_name = "class-attendance";
$tspan = "6"
?>
<div class="wrap list-form dedu-admin-wrapper" data-type="attendance"> 
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Students Attendance Roaster</h1>
    </div>
    
    <div class="dedu-card hide-me" id="dedu-list-view">
        <div class="dedu-table-container">
            <table class="dedu-table-modern" style="min-width: 400px;">
                <thead>
                    <tr>
                        <th class="col-cb"><input type="checkbox" id="dedu-select-all"></th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $students ) ) : ?>
                        <?php include("{$part}/no-data.php") ?>
                    <?php else : ?>
                        <?php foreach ( $students as $s ) :
                            // 1. Get the real photo URL if an ID exists
                            $photo_url = '';
                            if ( ! empty( $s->profile_picture_id ) ) {
                                $photo_url = wp_get_attachment_image_url( $s->profile_picture_id, 'thumbnail' );
                            }

                            // 2. Fallback to your plugin's default image if no photo is found
                            if ( ! $photo_url ) {
                                // Option A: Use a local file in your plugin
                                $photo_url = \DEDU_URL . "assets/images/profile.jpg"; 
                                
                                // Option B: SaaS-style dynamic avatar (No image file needed!)
                                // $photo_url = "https://ui-avatars.com/api/?name=" . urlencode($s->first_name . ' ' . $s->last_name) . "&background=random";
                            }    
                        ?>
                            
                            <tr class="is-row">
                                <td class="col-cb">
                                    <input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $s->id; ?>">
                                </td>
                                <td>
                                    <div class="prof">
                                        <img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( "{$s->first_name}_{$s->last_name}" ); ?>"  >
                                        <p>
                                            <span class="text-heading">
                                                <?php echo esc_html("{$s->first_name} {$s->last_name}"); ?>
                                            </span><br>
                                            <small><?php echo esc_html($s->email); ?></small>
                                        </p>
                                    </div>
                                    
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $s->status; ?>">
                                        <?php echo ucfirst($s->status); ?>
                                    </span>
                                </td>
                                <td class="dedu-row-action">
                                    <a href="javascript:void(0);" 
                                        data-id="<?php echo $s->id; ?>"
                                        data-nonce="<?php echo wp_create_nonce("dedu_student_nonce"); ?>"
                                        class="dedu-action-link edit dedu-edit-icon"
                                        title="Edit">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                    <a href="javascript:void(0);" 
                                        class="dedu-action-link delete dedu-delete-icon" 
                                        data-id="<?php echo $s->id; ?>" 
                                        data-fname="<?php echo esc_attr($s->first_name); ?>"
                                        data-lname="<?php echo esc_attr($s->last_name); ?>" 
                                        data-nonce="<?php echo wp_create_nonce('dedu_delete_student_' . $s->id); ?>"
                                        title="Delete">
                                            <span class="dashicons dashicons-trash"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php include("{$part}/no-search-result.php") ?>
                    <?php endif; ?>    
                </tbody>
            </table>
        </div>
          
    </div>
    <div class="" id="dedu-form-view"  >
        <form id="dedu-attendance-filter-form">
            <div class="dedu-card">
                <fieldset class = "fields-group school-details">
                    <legend class = "dedu-card-title"><h2></h2></legend>
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
                        <input type="date" id="attendance_date_input" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </fieldset>
            </div>
            <div class="dedu-form-actions">
                <button type="submit" class="dedu-btn dedu-btn-primary">  
                    Load Roster
                </button>
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
                        <template id="student-row">
                            <tr class="attendance-row">
                                <td class="text-heading"></td>
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
                <button type="submit" class="dedu-btn dedu-btn-success">Save Roster Decisions</button>
            </div>
        </form>
    </div>
</div>

