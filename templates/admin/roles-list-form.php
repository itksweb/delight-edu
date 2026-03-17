<div class="wrap list-form dedu-admin-wrapper" data-type="role"> 
    <!-- <?php wp_nonce_field('dedu_bulk_roles_action', 'dedu-role-nonce'); ?> -->
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Staff Roles Management</h1>
    </div>
    <div class="dedu-card" id="dedu-list-view">
        <div class="dedu-tab-header">
            <h3>Role List</h3>
            <button id="show-form-btn" class="dedu-btn dedu-btn-primary">
                <span class="dashicons dashicons-plus"></span>
                Add New Staff Role
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
                        <th>Role Name</th>
                        <th>Permissions Assigned</th> <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $all_roles ) ) : ?>
                        <tr class="dedu-no-data-static">
                            <td colspan="4">
                                <div class="dedu-empty-state">
                                    <span class="dashicons dashicons-database"></span>
                                    <p>No roles found. Start by creating your first role!</p>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $all_roles as $role ) : 
                            $cap_count = isset($role->cap_count) ? $role->cap_count : 0;
                        ?>
                            <tr>
                                <td class="col-cb">
                                    <input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $role->id; ?>">
                                </td>
                                <td class="text-heading"><?php echo esc_html( $role->role_name ); ?></td>
                                <td>
                                    <span class="dedu-badge-count">
                                        <?php echo $cap_count; ?> Permissions
                                    </span>
                                </td>
                                <td class="dedu-row-action">
                                    <a href="javascript:void(0);" 
                                        data-id="<?php echo $role->id; ?>"
                                        class="dedu-action-link edit dedu-edit-icon"
                                        data-name="<?php echo esc_attr($role->role_name); ?>"
                                        data-caps="<?php echo esc_attr( $role->capabilities); ?>"
                                        title="Edit">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                    <a href="javascript:void(0);" 
                                        class="dedu-action-link delete dedu-delete-icon" 
                                        data-id="<?php echo $role->id; ?>" 
                                        data-name="<?php echo esc_attr($role->role_name); ?>" 
                                        data-nonce="<?php echo wp_create_nonce('dedu_delete_role_' . $role->id); ?>"
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
        <?php
        $is_edit = ! empty( $role );
        // Convert the string from the database into an array so in_array() works
        $current_caps = [];
        if ( ! empty( $role->capabilities ) ) {
            // If it's already an array, use it; if it's a string, explode it
            $current_caps = is_array($role->capabilities) 
                ? $role->capabilities 
                : array_map('trim', explode(',', $role->capabilities));
        }
        ?>
        <div class="dedu-tab-header">
            <h3>Add A New Staff role</h3>
            <button id="show-list-btn" class="dedu-btn dedu-btn-primary">
                <span class="dashicons dashicons-list-view"></span>
                Back to List
            </button>
        </div>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="dedu_save_role">
            <?php if ( $is_edit ) : ?>
                <input type="hidden" name="role_id" value="<?php echo $role->id; ?>">
            <?php endif; ?>
            <?php wp_nonce_field( 'dedu_role_nonce' ); ?>

            <div class="dedu-card dedu-carded">
                <div class="dedu-card-title">
                    <span class="dashicons dashicons-id"></span> Role Identity
                </div>
                
                <div class="dedu-form-group">
                    <label class="dedu-label" for="role_name">Display Name</label>
                    <input name="role_name" type="text" id="role_name" 
                        value="<?php echo $is_edit ? esc_attr($role->role_name) : ''; ?>" 
                        class="dedu-input" placeholder="e.g. Senior Accountant" required>
                    <p class="dedu-field-help">Give this role a clear name that describes the staff's responsibility.</p>
                </div>
            </div>

            <div class="dedu-card dedu-carded">
                <div class="dedu-card-title">
                    <span class="dashicons dashicons-shield"></span> Access Permissions
                </div>
                
                <div class="dedu-permissions-grid"> 
                    <?php foreach ( $groups as $group_name => $capabilities ) : ?>
                        <div class="dedu-permission-card">
                            <h4 class="dedu-group-label"><?php echo esc_html( $group_name ); ?></h4>
                            <div class="dedu-cap-list">
                                <?php foreach ( $capabilities as $cap_slug => $cap_label ) : ?>
                                    <label class="dedu-checkbox-label">
                                        <input type="checkbox" name="capabilities[]" value="<?php echo esc_attr( $cap_slug ); ?>"
                                            <?php checked( in_array( $cap_slug, $current_caps ) ); ?>>
                                        <span class="dedu-checkbox-text"><?php echo esc_html( $cap_label ); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="dedu-form-actions">
                <button type="submit" class="dedu-btn dedu-btn-primary">
                    <?php echo $is_edit ? 'Update Staff Role' : 'Create Staff Role'; ?>
                </button>
            </div>
        </form>                   
    </div>
    
</div>