<?php
if (!defined('ABSPATH')) exit;
$part = \DEDU_PATH . 'templates/admin/partials';
$data_name = "master subject";
$tspan = "5"
?>

<div class="wrap dedu-admin-wrapper" data-type="master_subject"> 
    <!-- <?php wp_nonce_field('dedu_bulk_roles_action', 'dedu-master-subject-nonce'); ?> -->
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Master Subject List</h1>
    </div>
    
    <div class="dedu-card" id="dedu-list-view" >  
        <?php include("{$part}/tab-list-header.php") ?>      
        <?php include("{$part}/table-top.php") ?>
        <div class="dedu-table-container">
            <table class="dedu-table-modern" style="min-width: 400px;">
                <thead>
                    <tr>
                        <th class="col-cb"><input type="checkbox" id="dedu-select-all"></th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>ID</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $subjects ) ) : ?>
                        <?php include("{$part}/no-data.php") ?>
                    <?php else : ?>
                        <?php foreach ( $subjects as $subject ) : ?>
                            <tr class="is-row">
                                <td class="col-cb">
                                    <input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $subject->id; ?>">
                                </td>
                                <td class="text-heading"><?php echo esc_html($subject->subject_name); ?></td>
                                <td><?php echo esc_html(ucfirst($subject->subject_type)); ?></td>
                                <td><code><?php echo esc_html($subject->id); ?></code></td>
                                <td class="dedu-row-action">
                                    <a href="javascript:void(0);" 
                                        data-id="<?php echo $subject->id; ?>"
                                        class="dedu-action-link edit dedu-edit-icon"
                                        data-name="<?php echo esc_attr($subject->subject_name); ?>"
                                        data-type="<?php echo esc_attr($subject->subject_type); ?>"
                                        title="Edit">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                    <a href="javascript:void(0);" 
                                        class="dedu-action-link delete dedu-delete-icon" 
                                        data-id="<?php echo $subject->id; ?>" 
                                        data-name="<?php echo esc_attr($subject->subject_name); ?>" 
                                        data-nonce="<?php echo wp_create_nonce('dedu_delete_master_subject_' . $subject->id); ?>"
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

    <div class="dedu-card hide-me" id="dedu-form-view" >
        <?php include("{$part}/tab-form-header.php") ?>
        <div class="form-wrap">
            <p>Enter a subject name that will be available school-wide (e.g., Mathematics, Physics).</p>
            
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="dedu_save_master_subject">
                <?php wp_nonce_field('dedu_master_subject_nonce'); ?>

                <div class="dedu-from-group">
                    <div class="form-field form-required" width="65%">
                        <label for="subject_name">Subject Name</label>
                        <input name="subject_name" id="subject_name" type="text" value="" width="100%" aria-required="true" required>
                    </div>

                    <div class="form-field" width="25%">
                        <label for="subject_type">Subject Type</label>
                        <select name="subject_type" id="subject_type">
                            <option value="core">Core (Compulsory)</option>
                            <option value="elective">Elective (Optional)</option>
                            <option value="vocational">Vocational</option>
                        </select>
                    </div>
                </div>
                <div class="dedu-form-actions">
                    <button type="submit" class="dedu-btn dedu-btn-primary">
                        Add to Master List
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>