<?php
if (!defined('ABSPATH')) exit;
// Get the status message if it exists
$message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
?>
<div class="wrap dedu-admin-wrapper" data-type="master_subject"> 
    <!-- <?php wp_nonce_field('dedu_bulk_roles_action', 'dedu-master-subject-nonce'); ?> -->
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Master Subject List</h1>
    </div>
    <?php if ($message === 'subject_created') : ?>
        <div class="notice notice-success is-dismissible"><p>Subject successfully added to master list.</p></div>
    <?php elseif ($message === 'subject_updated') : ?>
        <div class="notice notice-success is-dismissible"><p>Subject successfully updated in master list.</p></div>
    <?php elseif ($message === 'save_failed') : ?>
        <div class="notice notice-error is-dismissible"><p>Failed to save subject. It might already exist.</p></div>
    <?php endif; ?>
    
    <div class="dedu-card" id="dedu-list-view" >  
        <div class="dedu-tab-header">
            <h3>Master Subject List</h3>
            <button id="show-form-btn" class="dedu-btn dedu-btn-primary">
                <span class="dashicons dashicons-plus"></span>
                Add New Master Subject
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
                        <th>Name</th>
                        <th>Type</th>
                        <th>ID</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $subjects ) ) : ?>
                        <tr class="dedu-no-data-static">
                            <td colspan="4">
                                <div class="dedu-empty-state">
                                    <span class="dashicons dashicons-database"></span>
                                    <p>No master subjects found. Start by creating your first master subject!</p>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $subjects as $subject ) : ?>
                            <tr>
                                <td class="col-cb">
                                    <input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $subject->id; ?>">
                                </td>
                                <td><strong><?php echo esc_html($subject->subject_name); ?></strong></td>
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
                        <tr id="dedu-no-search-results" style="display: none;">
                            <td colspan="4">
                                <div class="dedu-empty-state">
                                    <span class="dashicons dashicons-search"></span>
                                    <p>No master subjects match your search criteria.</p>
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
                    <option value="2" >2</option>
                    <option value="5" >5</option>
                    <option value="10" selected >10</option>
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

    <div class="dedu-card hide-me" id="dedu-form-view" >
        <div class="dedu-tab-header">
            <h3>Add A New Master Subject</h3>
            <button id="show-list-btn" class="dedu-btn dedu-btn-primary">
                <span class="dashicons dashicons-list-view"></span>
                Back to List
            </button>
        </div>
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