<?php
if (!defined('ABSPATH')) exit;

$message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
?>

<div class="wrap dedu-admin-wrapper" data-type="class_subjects">
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Manage Class Subjects<</h1>
    </div>
    <?php if ($message === 'bulk_saved') : ?>
        <div class="notice notice-success is-dismissible"><p>Curriculum updated successfully for the class.</p></div>
    <?php endif; ?>
    <div class="dedu-card" id="dedu-list-view">
        <div class="dedu-tab-header">
            <h3>Class subjects List</h3>
            <a href="<?php echo admin_url('admin.php?page=dedu-subjects'); ?>" 
                class="page-title-action dedu-btn dedu-btn-primary">Manage Master Subjects
            </a>
        </div>
        
        <p class="description">
            Below is a list of all classes. Click <strong>"Manage Curriculum"</strong> to assign or edit subjects for a specific class and its sections.
        </p>
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
                        <th class="manage-column column-primary" style="width: 30%;">Class Name</th>
                        <th style="width: 20%;">Subjects Assigned</th>
                        <th style="width: 25%;">Academic Year</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $classes_with_counts ) ) : ?>
                        <tr class="dedu-no-data-static">
                            <td colspan="4">
                                <div class="dedu-empty-state">
                                    <span class="dashicons dashicons-database"></span>
                                    <p>No classes found. Start by creating your first class!</p>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $classes_with_counts as $item ) : ?>
                            <tr>
                                <td class="col-cb">
                                    <input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $item->id; ?>">
                                </td>
                                <td class="column-primary">
                                    <strong>
                                        <a href="javascript:void(0);" style="text-decoration:none;" data-id="<?php echo $item->id; ?>"
                                            class="dedu-action-link edit manage-curriculum dedu-edit-icon"
                                            data-curriculum="<?php echo esc_attr($item->curriculum_json); ?>"
                                            data-name="<?php echo esc_attr($item->class_name); ?>"
                                            title="Edit">
                                            <?php echo esc_html($item->class_name); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td>
                                    <span class="count-pill <?php echo ($item->subject_count > 0) ? 'has-subjects' : 'no-subjects'; ?>">
                                        <?php echo esc_html($item->subject_count); ?> Subjects
                                    </span>
                                </td>
                                <td><?php echo esc_html($current_year); ?></td>
                                <td class="dedu-row-action">
                                    <a href="javascript:void(0);" 
                                        data-id="<?php echo $item->id; ?>"
                                        class="dedu-action-link edit manage-curriculum dedu-edit-icon"
                                        data-curriculum="<?php echo esc_attr($item->curriculum_json); ?>"
                                        data-name="<?php echo esc_attr($item->class_name); ?>"
                                        title="Edit">
                                        <span style="font-size: 16px; padding-top: 3px;" class="dashicons dashicons-edit"></span> Manage Curriculum
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="dedu-no-search-results" style="display: none;">
                            <td colspan="4">
                                <div class="dedu-empty-state">
                                    <span class="dashicons dashicons-search"></span>
                                    <p>No class match your search criteria.</p>
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
                    <option value="5" selected>5</option>
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
    <div class="dedu-card hide-me" id="dedu-form-view">
        <div class="dedu-tab-header">
            <h3>Add A New </h3>
            <button id="show-list-btn" class="dedu-btn dedu-btn-primary">
                <span class="dashicons dashicons-list-view"></span>
                Back to List
            </button>
        </div>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
            <input type="hidden" name="action" value="dedu_bulk_save_curriculum">
            <input type="hidden" name="class_id" value="">
            <?php wp_nonce_field('dedu_bulk_curriculum_nonce'); ?>

            <table class="wp-list-table widefat fixed striped" id="curriculum-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Subject</th>
                        <th style="width: 15%;">Code</th>
                        <th style="width: 30%;">Sections (Optional)</th>
                        <th style="width: 15%;">Teachers</th>
                        <th style="width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody id="subject-rows"> </tbody>
            </table>

            <div style="margin-top: 20px;">
                <button type="button" class="button" id="add-row" >
                    <span class="dashicons dashicons-plus" style="margin-top:4px;"></span> Add Subject Field
                </button>
                <input type="submit" class="button button-primary button-large" value="Save Curriculum">
            </div>
        </form>
    </div>
</div>

<script>
    const DEDU_MASTER_DATA = {
        sections: <?php echo json_encode($sections_by_class); ?>,
        subjects: <?php echo json_encode($master_subjects); ?>,
        teachers: <?php echo json_encode($teachers_list); ?>
    };
     jQuery(document).ready(function($) {
        const $tbody = $('#subject-rows');
        const $submitBtn = $('.button-primary');

        function validateCurriculum() {
            let selectedSubjects = [];
            let hasDuplicate = false;

            $tbody.find('select[name*="[id]"]').each(function() {
                let val = $(this).val();
                if (val) {
                    if (selectedSubjects.includes(val)) {
                        $(this).css('border', '2px solid #d63638');
                        hasDuplicate = true;
                    } else {
                        $(this).css('border', '');
                        selectedSubjects.push(val);
                    }
                }
            });

            if (hasDuplicate) {
                $submitBtn.prop('disabled', true).attr('title', 'Each subject can only be assigned once per class.');
            } else {
                $submitBtn.prop('disabled', false).removeAttr('title');
            }
        }
        $tbody.on('change', 'select[name*="[id]"]', function() {
            validateCurriculum();
        });
        
    });
</script>

<style>
    .count-pill {
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
    }
    .has-subjects { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .no-subjects { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

