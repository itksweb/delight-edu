<?php
if (!defined('ABSPATH')) exit;

$staff_members = $staff_members ?? [];
$roles         = $roles ?? [];
$classes       = $classes ?? [];
$form_meta     = $form_meta ?? [];
?>
<div class="wrap list-form dedu-admin-wrapper" data-type=""> 
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
                                    <input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $staff->id; ?>">
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
                                        data-id="<?php echo $staff->id; ?>"
                                        id="<?php echo $staff->id; ?>" 
                                        class="dedu-action-link edit dedu-edit-icon"
                                        data-name="<?php echo esc_attr($staff->role_name); ?>"
                                        data-caps="<?php echo esc_attr( $staff->capabilities); ?>"
                                        data-type="staff"
                                        title="Edit">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                    <a href="javascript:void(0);" 
                                        class="dedu-action-link delete dedu-delete-icon" 
                                        data-type="staff"
                                        data-id="<?php echo $staff->id; ?>" 
                                        data-name="<?php echo esc_attr($staff->role_name); ?>" 
                                        data-nonce="<?php echo wp_create_nonce('dedu_delete_role_' . $staff->id); ?>"
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
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
            <input type="hidden" name="action" value="dedu_save_staff">
            <?php wp_nonce_field('dedu_staff_nonce'); ?>

            <fieldset style="margin-bottom:20px; border: 1px solid #ddd; padding: 15px;">
                <legend style="padding: 0 10px; font-weight: bold;">Account & Identity</legend>
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <div style="flex: 1;">
                        <label>First Name*</label>
                        <input type="text" name="first_name" class="large-text" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Last Name*</label>
                        <input type="text" name="last_name" class="large-text" required>
                    </div>
                </div>
                <p>
                    <label>Email (Login Username)*</label>
                    <input type="email" name="email" class="large-text" required>
                </p>
                <p>
                    <label>Password*</label>
                    <input type="password" name="password" class="large-text" required>
                </p>
            </fieldset>

            <fieldset style="margin-bottom:20px; border: 1px solid #ddd; padding: 15px;">
                <legend style="padding: 0 10px; font-weight: bold;">Personal Details</legend>
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <div style="flex: 1;">
                        <label>Gender</label>
                        <select name="gender" class="large-text">
                            <?php foreach ($form_meta['genders'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label>Marital Status</label>
                        <select name="marital_status" class="large-text">
                            <?php foreach ($form_meta['marital_statuses'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="large-text">
                    </div>
                    <div style="flex: 1;">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="large-text">
                    </div>
                </div>
            </fieldset>

            <fieldset style="margin-bottom:20px; border: 1px solid #ddd; padding: 15px;">
                <legend style="padding: 0 10px; font-weight: bold;">Employment</legend>
                <p>
                    <label>Staff Role*</label>
                    <select name="role_id" class="large-text" required>
                        <option value="">-- Select Role --</option>
                        <?php foreach ($roles as $r) : ?>
                            <option value="<?php echo $r->id; ?>"><?php echo esc_html($r->role_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <label>Job Title/Position (e.g. Senior Teacher)</label>
                    <input type="text" name="position" class="large-text">
                </p>
                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Salary Amount</label>
                        <input type="number" step="0.01" name="salary_amount" class="large-text" value="0.00">
                    </div>
                    <div style="flex: 1;">
                        <label>Joining Date</label>
                        <input type="date" name="joining_date" class="large-text" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <p>
                    <label>Working Hours</label>
                    <select name="working_hours" class="large-text">
                        <?php foreach ($form_meta['working_hours'] as $k => $v) : ?>
                            <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
            </fieldset>

            <fieldset style="margin-bottom:20px; border: 1px solid #ddd; padding: 15px; background: #f9f9f9;">
                <legend style="padding: 0 10px; font-weight: bold;">Academic Bridge</legend>
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

            <p class="submit">
                <input type="submit" class="button button-primary button-large" value="Save Staff Member" style="width:100%;">
            </p>
        </form>                  
    </div>
    
</div>