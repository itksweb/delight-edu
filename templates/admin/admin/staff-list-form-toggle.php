
<?php
if (!defined('ABSPATH')) exit;

$staff_members = $staff_members ?? [];
$roles         = $roles ?? [];
$classes       = $classes ?? [];
$form_meta     = $form_meta ?? [];
$part = \DEDU_PATH . 'templates/admin/partials';
$data_name = "staff";
$tspan = "6"
?>
<div class="wrap list-form dedu-admin-wrapper" data-type="staff"> 
    <!-- <?php wp_nonce_field('dedu_bulk_roles_action', 'dedu-staff-nonce'); ?> -->
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Staff Management</h1>
    </div>
    <div class="dedu-card" id="dedu-list-view">
        <?php include("{$part}/tab-list-header.php") ?>
        <?php include("{$part}/table-top.php") ?>
        <div class="dedu-table-container">
            <table class="dedu-table-modern" style="min-width: 400px;">
                <thead>
                    <tr>
                        <th class="col-cb"><input type="checkbox" id="dedu-select-all"></th>
                        <th style="width:120px;">ID Number</th>
                        <th>Name</th>
                        <th>Role & Position</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $staff_members ) ) : ?>
                        <?php include("{$part}/no-data.php") ?>
                    <?php else : ?>
                        <?php foreach ( $staff_members as $s ) : ?>
                            <tr class="is-row">
                                <td class="col-cb">
                                    <input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $s->id; ?>">
                                </td>
                                <td>
                                    <strong><?php echo esc_html($s->staff_id_number); ?></strong>
                                </td>
                                <td>
                                    <span class="text-heading"><?php echo esc_html("{$s->first_name} {$s->last_name}"); ?></span><br>
                                    <small><?php echo esc_html($s->email); ?></small>
                                </td>
                                <td>
                                    <?php echo esc_html($s->role_name); ?><br>
                                    <small><em><?php echo esc_html($s->position); ?></em></small>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $s->status; ?>">
                                        <?php echo ucfirst($s->status); ?>
                                    </span>
                                </td>
                                <td class="dedu-row-action">
                                    <a href="javascript:void(0);" 
                                        data-id="<?php echo $s->id; ?>"
                                        data-nonce="<?php echo wp_create_nonce("dedu_staff_nonce"); ?>"
                                        class="dedu-action-link edit dedu-edit-icon"
                                        title="Edit">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                    <a href="javascript:void(0);" 
                                        class="dedu-action-link delete dedu-delete-icon" 
                                        data-id="<?php echo $s->id; ?>" 
                                        data-name="<?php echo esc_attr($s->role_name); ?>" 
                                        data-fname="<?php echo esc_attr($s->first_name); ?>"
                                        data-lname="<?php echo esc_attr($s->last_name); ?>" 
                                        data-nonce="<?php echo wp_create_nonce('dedu_delete_staff_' . $s->id); ?>"
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
        <?php include("{$part}/table-bottom.php") ?>   
    </div>
    <div class="hide-me" id="dedu-form-view"  >
        <?php include("{$part}/tab-form-header.php") ?>
        <form id="staff-form" action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="dedu_save_staff">
            <input type="hidden" name="staff_db_id" id="staff_db_id" value="0">
            <input type="hidden" name="wp_user_id" id="wp_user_id" value="">
            <input type="hidden" name="existing_photo_url" id="existing_photo_url" value="">
            <?php wp_nonce_field('dedu_staff_nonce'); ?>
            
            <div class="dedu-card">
                <fieldset class = "fields-group">
                    <legend class = "dedu-card-title">Personal Details</legend>
                    <div class="unit dedu-upload-container" id="drop-zone">
                        <label for="staff_photo" class="dedu-upload-label">
                            <div class="upload-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            </div>
                            <div class="upload-text">
                                <strong>Click to upload</strong> or drag and drop
                                <span>PNG, JPG or GIF (max. 2MB)</span>
                            </div>
                            <input type="file" name="staff_photo" id="staff_photo" accept="image/*" hidden>
                        </label>
                        
                        <div id="image-preview" class="image-preview hidden">
                            <img src="" alt="Preview">
                            <button type="button" class="remove-img">&times;</button>
                        </div>
                    </div>
                    <div class = "unit">
                        <label>First Name*</label>
                        <input type="text" name="first_name" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Middle Name*</label>
                        <input type="text" name="middle_name" class="large-text">
                    </div>
                    <div class = "unit">
                        <label>Last Name*</label>
                        <input type="text" name="last_name" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Gender</label>
                        <select name="gender" class="large-text">
                            <?php foreach ($form_meta['genders'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Marital Status</label>
                        <select name="marital_status" class="large-text">
                            <?php foreach ($form_meta['marital_statuses'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="large-text">
                    </div>                 
                </fieldset>
            </div>

            
            <div class="dedu-card">
                <fieldset class = "fields-group">
                    <legend class = "dedu-card-title">Employment</legend>
                    <div class = "unit">
                        <label>Staff Role</label>
                        <select name="role_id" class="large-text">
                            <option value="">-- Select Role --</option>
                            <?php foreach ($roles as $r) : ?>
                                <option value="<?php echo $r->id; ?>"><?php echo esc_html($r->role_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class= "unit">
                        <label>Working Hours</label>
                        <select name="working_hours" class="large-text">
                            <?php foreach ($form_meta['working_hours'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Joining Date</label>
                        <input type="date" name="joining_date" class="large-text" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class = "unit">
                        <label>Job Title/Position </label>
                        <input type="text" name="position" class="large-text">
                    </div>
                    <div class = "unit">
                        <label>Salary Amount</label>
                        <input type="number" step="0.01" name="salary_amount" class="large-text" value="0.00">
                    </div>
                    <div class = "unit">
                        <label>
                            <input type="checkbox" name="is_teacher" id="is_teacher_toggle" value="1"> 
                            <strong>Is Teaching Staff?</strong>
                        </label>
                    </div>
                    <div class = "unit academic_field class-field" style="display: none;" >
                        <label>Assigned Class</label>
                        <select name="class_id" class="large-text" id="class-field">
                            <option value="">-- No Class Assigned --</option>
                            <?php foreach ($classes as $c) : ?>
                                <option value="<?php echo $c->id; ?>"><?php echo esc_html($c->class_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit academic_field" style="display: none;">
                        <label>Assigned Section</label>
                        <select name="section_id" class="large-text" id="sections-field">
                            <option value="" disabled selected>-- select a class first --</option>
                        </select>
                    </div>
                </fieldset>
            </div>
            <div class="dedu-card">
                <fieldset class = "fields-group">
                    <legend class = "dedu-card-title">Account</legend>
                    <div class = "unit">
                        <label>Email (Login Username)*</label>
                        <input type="email" name="email" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Password*</label>
                        <input type="password" name="password" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="large-text">
                    </div>
                </fieldset>
            </div>

            <div class="dedu-card">
                <fieldset class = "fields-group">
                    <legend class="dedu-card-title"><span class="dashicons dashicons-shield"></span>Staff Permissions</legend>
                    <p class="description">Selecting a role above will auto-fill these, but you can add/remove specific permissions for this individual.</p>
                    <?php foreach ($permission_groups as $group_name => $caps) : ?>
                        <div class="dedu-permission-card">
                            <div class="cap-list-head">
                                <h4 class="dedu-group-label">
                                    <?php echo esc_html($group_name); ?>
                                    <small><span class="dashicons dashicons-plus"></span></small>
                                </h4>
                                <label class="dedu-checkbox-label">
                                    <span class="dedu-checkbox-text">Select All</span>
                                    <input type="checkbox" name="<?php echo esc_html($group_name); ?>" class="check-all-caps" >
                                </label>
                            </div>
                            
                            <div class="dedu-cap-list">
                                <?php foreach ($caps as $cap_slug => $cap_label) : ?>
                                    <label class="dedu-checkbox-label">
                                        <input type="checkbox" name="staff_permissions[]"
                                            class="cap-checkbox" 
                                            value="<?php echo esc_attr($cap_slug); ?>" >
                                            <span class="dedu-checkbox-text"><?php echo esc_html($cap_label); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            </div>
            
            <div class="dedu-form-actions">
                <button type="submit" class="dedu-btn dedu-btn-primary">  
                </button>
            </div>
        </form>                  
    </div>
</div>

<style>
    .dedu-upload-container {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    transition: all 0.3s ease;
    background: #f9fafb;
    position: relative;
    cursor: pointer;
    grid-row: span 3;
    }

    .dedu-upload-container:hover, .dedu-upload-container.drag-over {
        border-color: #4f46e5; /* Modern Indigo */
        background: #f5f3ff;
    }

    .upload-icon { color: #9ca3af; margin-bottom: 12px; }
    .upload-text { color: #4b5563; font-size: 14px; }
    .upload-text strong { color: #4f46e5; }
    .upload-text span { display: block; color: #9ca3af; margin-top: 4px; }

    /* Preview Styling */
    .image-preview {
        position: absolute;
        inset: 0;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .image-preview img {
        max-height: 100%;
        width: auto;
        object-fit: contain;
    }

    .hidden { display: none !important; }

    .remove-img {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
    }
</style>

<script>
    const sections = <?php echo json_encode($sections_by_class); ?>;
    const classes = <?php echo json_encode($classes); ?>;
    jQuery(document).ready(function($) {
        $('#is_teacher_toggle').on('change', function() {
            if ($(this).is(':checked')) { $('.academic_field').slideDown(); }
            else { 
                $('.academic_field').slideUp();
             }
        });
    });
    
</script>