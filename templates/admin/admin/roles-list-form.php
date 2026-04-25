<?php
if (!defined('ABSPATH')) exit;
$part = \DEDU_PATH . 'templates/admin/partials';
$data_name = "role";
$tspan = "4"
?>

<div class="wrap list-form dedu-admin-wrapper" data-type="role"> 
    <!-- <?php wp_nonce_field('dedu_bulk_roles_action', 'dedu-role-nonce'); ?> -->
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Staff Roles Management</h1>
    </div>
    <div class="dedu-card" id="dedu-list-view">
        <?php include("{$part}/tab-list-header.php") ?>
        <?php include("{$part}/table-top.php") ?>
        <div class="dedu-table-container">
            <table class="dedu-table-modern" style="min-width: 400px;">
                <thead>
                    <tr>
                        <th class="col-cb"><input type="checkbox" id="dedu-select-all"></th>
                        <th>Role Name</th>
                        <th>Permissions Assigned</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $all_roles ) ) : ?>
                        <?php include("{$part}/no-data.php") ?>
                    <?php else : ?>
                        <?php foreach ( $all_roles as $role ) : 
                            $cap_count = isset($role->capabilities) ? count($role->capabilities) : 0;
                        ?>
                            <tr class="is-row">
                                <td class="col-cb">
                                    <input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $role->id; ?>">
                                </td>
                                <td class="text-heading"><?php echo esc_html( $role->role_name ); ?></td>
                                <td>
                                    <span class="dedu-badge <?php echo ($cap_count > 0) ? '' : 'empty'; ?>">
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
                        <?php include("{$part}/no-search-result.php") ?>
                    <?php endif; ?>    
                </tbody>
            </table>
        </div>
        <?php include("{$part}/table-bottom.php") ?>   
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
        <?php include("{$part}/tab-form-header.php") ?>
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
                                <?php foreach ( $capabilities as $cap_slug => $cap_label ) : ?>
                                    <label class="dedu-checkbox-label">
                                        <input type="checkbox" class="cap-checkbox" name="capabilities[]" value="<?php echo esc_attr( $cap_slug ); ?>"
                                            >
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
                    
                </button>
            </div>
        </form>                   
    </div>
    
</div>

<script>
    const ROLE_PERMISSIONS = <?php echo json_encode($role_mapping); ?>;
</script>