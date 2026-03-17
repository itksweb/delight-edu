
<?php
if (!defined('ABSPATH')) exit;

$staff_members = $staff_members ?? [];
$roles         = $roles ?? [];
$classes       = $classes ?? [];
$form_meta     = $form_meta ?? [];
?>
<div class="wrap list-form dedu-admin-wrapper" data-type="staff"> 
    <!-- <?php wp_nonce_field('dedu_bulk_roles_action', 'dedu-staff-nonce'); ?> -->
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Staff Management</h1>
    </div>
    <div class="dedu-card" id="dedu-list-view">
        <div class="dedu-tab-header">
            <h3>Staff List</h3>
            <button id="show-form-btn" class="dedu-btn dedu-btn-primary">
                <span class="dashicons dashicons-plus"></span>
                Add New Staff Staff
            </button>
        </div>
        <div class="dedu-table-toolbar">
            <div class="dedu-toolbar-left">
                <select id="dedu-bulk-action-selector" class="dedu-dropdown-btn">
                    <option value="">Bulk Actions</option>
                    <option value="delete">Delete</option>
                    <option value="edit">Edit</option>
                </select>
                <button type="button" id="dedu-apply-bulk-action" class="dedu-btn-apply">Apply</button>
            </div>
            <div class="dedu-toolbar-right">
                <div class="dedu-search-wrapper">
                    <span class="dashicons dashicons-search"></span>
                    <input type="text" id="dedu-search" placeholder="Filter roles..." class="dedu-search-input">
                </div>
            </div>
        </div>
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
                        <tr class="dedu-no-data-static">
                            <td colspan="4">
                                <div class="dedu-empty-state">
                                    <span class="dashicons dashicons-database"></span>
                                    <p>No roles found. Start by creating your first staff!</p>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $staff_members as $s ) : ?>
                            <tr>
                                <td class="col-cb">
                                    <input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $s->id; ?>">
                                </td>
                                <td>
                                    <strong><?php echo esc_html($s->staff_id_number); ?></strong>
                                </td>
                                <td>
                                    <?php echo esc_html($s->first_name . ' ' . $s->last_name); ?><br>
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
                        <tr id="dedu-no-search-results" style="display: none;">
                            <td colspan="4">
                                <div class="dedu-empty-state">
                                    <span class="dashicons dashicons-search"></span>
                                    <p>No roles match your search criteria.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>    
                </tbody>
            </table>
        </div>
        <div class="dedu-table-footer">
            <div class="dedu-table-footer-left">
                <label for="dedu-rows-per-page">Show</label>
                <select id="dedu-rows-per-page" class="dedu-select-sm">
                    <option value="2" selected>2</option>
                    <option value="5" >5</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>entries</span>
            </div>

            <div class="dedu-table-footer-right">
                <div class="dedu-pagination-info">
                    Showing <span id="current-visible-range">0-0</span> of <span id="total-visible-items">0</span>
                </div>
                <div class="dedu-pagination-controls">
                    <button type="button" id="prev-page" class="butt">‹</button>
                    <span id="page-numbers"></span>
                    <button type="button" id="next-page" class="butt">›</button>
                </div>
            </div>
        </div>   
    </div>
    <div class="dedu-card hide-me" id="dedu-form-view"  >
        <div class="dedu-tab-header">
            <h3>Add A New Staff</h3>
            <button id="show-list-btn" class="dedu-btn dedu-btn-primary">
                <span class="dashicons dashicons-list-view"></span>
                Back to Staff List
            </button>
        </div>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="dedu_save_staff">
            <input type="hidden" name="staff_db_id" id="staff_db_id" value="0">
            <input type="hidden" name="existing_photo_url" id="existing_photo_url" value="">
            <?php wp_nonce_field('dedu_staff_nonce'); ?>

            <fieldset class = "fieldset">
                <legend class = "legend">Personal Details</legend>
                <p>
                    <label>Profile Picture</label>
                    <input type="file" name="staff_photo" accept="image/*" class="large-text">
                    <p class="description">Recommended: Square image (JPG/PNG).</p>
                </p>
                <div class = "fields-row">
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
                </div>
                <div class = "fields-row">
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
                </div>
            </fieldset>

            <fieldset class = "fieldset">
                <legend class = "legend">Account</legend>
                <div class = "fields-row">
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
                </div>
            </fieldset>

            <fieldset class = "fieldset">
                <legend class = "legend">Employment</legend>
                <div class = "fields-row">
                    <div class = "unit">
                        <label>Staff Role</label>
                        <select name="role_id" class="large-text">
                            <option value="">-- Select Role --</option>
                            <?php foreach ($roles as $r) : ?>
                                <option value="<?php echo $r->id; ?>"><?php echo esc_html($r->role_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="unit">
                        <label>Working Hours</label>
                        <select name="working_hours" class="large-text">
                            <?php foreach ($form_meta['working_hours'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class = "fields-row">
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
                </div>
            </fieldset>

            <fieldset class = "fieldset" style="background: #f9f9f9;">
                <legend class = "legend">Academic Bridge</legend>
                <p>
                    <label>
                        <input type="checkbox" name="is_teacher" id="is_teacher_toggle" value="1"> 
                        <strong>Is Teaching Staff?</strong>
                    </label>
                </p>
                <div id="academic_fields" style="display:none;">
                    <p>
                        <label>Assigned Class (Form Master)</label>
                        <select name="class_id" class="large-text">
                            <option value="">-- Not a Class Master --</option>
                            <?php foreach ($classes as $c) : ?>
                                <option value="<?php echo $c->id; ?>"><?php echo esc_html($c->class_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    <p class="description">Note: Checking this allows this staff to be assigned to subjects in the Curriculum module.</p>
                </div>
            </fieldset>

            <fieldset class = "fieldset">
                <legend style="padding: 0 10px; font-weight: bold; color: #2271b1;">Staff Permissions & Overrides</legend>
                <p class="description">Selecting a role above will auto-fill these, but you can add/remove specific permissions for this individual.</p>
                
                <div class="dedu-permissions-grid">
                    <?php foreach ($permission_groups as $group_name => $caps) : ?>
                        <div class="dedu-permission-card">
                            <h4 class="dedu-group-label">
                                <?php echo esc_html($group_name); ?>
                            </h4>
                            <div class="dedu-cap-list">
                                <?php foreach ($caps as $cap_slug => $cap_label) : ?>
                                    <label class="dedu-checkbox-label">
                                        <input type="checkbox" name="staff_permissions[]"
                                            class="staff-cap-checkbox" 
                                            value="<?php echo esc_attr($cap_slug); ?>" >
                                            <span class="dedu-checkbox-text"><?php echo esc_html($cap_label); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <div class="dedu-form-actions">
                <button type="submit" class="dedu-btn dedu-btn-primary">
                    
                </button>
            </div>

            <!-- <p class="submit">
                <input type="submit" class="button button-primary button-large" value="Save Staff Member" style="width:100%;">
            </p> -->
        </form>                  
    </div>
</div>

<script>
    
jQuery(document).ready(function($) {
    $('#is_teacher_toggle').on('change', function() {
        if ($(this).is(':checked')) { $('#academic_fields').slideDown(); }
        else { $('#academic_fields').slideUp(); }
    });

    /**
     * IMPORTANT: Disabled checkboxes are NOT sent in $_POST.
     * We need to enable them just for a split second before the form submits
     * so the server receives the FULL list of permissions.
     */
    
    $('form').on('submit', function() {
        $('.staff-cap-checkbox').prop('disabled', false);
    });


});
</script>