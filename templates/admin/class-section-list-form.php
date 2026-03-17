<div class="wrap dedu-admin-wrapper" data-type="class">
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Classes And Sections Management</h1>
    </div>
    <div class="dedu-card" id="dedu-list-view" >
        <div class="dedu-tab-header">
            <h3>Class List</h3>
            <button id="show-form-btn" class="dedu-btn dedu-btn-primary">
                <span class="dashicons dashicons-plus"></span>
                Add New Class
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
                    <input type="text" id="dedu-search" placeholder="Filter classes..." class="dedu-search-input">
                </div>
            </div>
        </div>
        <div class="dedu-table-container">
            <table class="dedu-table-modern dedu-js-paginated">
                <thead>
                    <tr>
                        <th class="col-cb"><input type="checkbox" id="dedu-select-all"></th>
                        <th>Name</th>
                        <th>Numeric</th>
                        <th>Sections</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $all_classes ) ) : ?>
                        <tr class="dedu-no-data-static">
                            <td colspan="4">
                                <div class="dedu-empty-state">
                                    <span class="dashicons dashicons-database"></span>
                                    <p>No classes found. Start by creating your first class!</p>
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                    <?php foreach ($all_classes as $class) : ?>
                        <tr>
                            <td class="col-cb"><input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $class->id; ?>">
                                    </td>
                            <td><strong><?php echo esc_html($class->class_name); ?></strong></td>
                            <td><?php echo esc_html($class->numeric_name); ?></td>
                            <td>
                                <?php 
                                if (!empty($class->section_list)) {
                                    $sections_array = explode(', ', $class->section_list);
                                    $count = count($sections_array);
                                    
                                    // The 'data-tooltip' attribute holds the list for CSS to display
                                    echo '<div class="dedu-tooltip-container">';
                                        echo '<span class="dedu-badge-count">';
                                            printf(_n('%d Section', '%d Sections', $count, 'delight-edu'), $count);
                                        echo '</span>';
                                        echo '<span class="dedu-tooltip-text">' . esc_html($class->section_list) . '</span>';
                                    echo '</div>';
                                } else {
                                    echo '<span class="dedu-badge empty">0 Sections</span>';
                                }
                                ?>
                            </td>
                            <td class="dedu-row-action">
                                
                                <a href="javascript:void(0);" 
                                    data-id="<?php echo $class->id; ?>"
                                    class="dedu-action-link edit dedu-edit-icon"
                                    data-name="<?php echo esc_attr($class->class_name); ?>"
                                    data-num="<?php echo esc_attr($class->numeric_name); ?>"
                                    data-sections="<?php echo esc_attr( $class->section_list); ?>"
                                    title="Edit">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                                <a href="javascript:void(0);" 
                                    class="dedu-action-link delete dedu-delete-icon" 
                                    data-id="<?php echo $class->id; ?>" 
                                    data-name="<?php echo esc_attr($class->class_name); ?>" 
                                    data-nonce="<?php echo wp_create_nonce('dedu_delete_class_' . $class->id); ?>"
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
                                    <p>No classes match your search criteria.</p>
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
                    <option value="5" selected >5</option>
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
            <h3>Add A New Class</h3>
            <button id="show-list-btn" class="dedu-btn dedu-btn-primary">
                <span class="dashicons dashicons-list-view"></span>
                Back to List
            </button>
        </div>
        <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="dedu_save_class_complex">
            <?php wp_nonce_field('dedu_class_complex_action', 'dedu_nonce'); ?>

            <div class="dedu-form-group">
                <div class="dedu-input-col">
                    <label>Class Name</label>
                    <input type="text" class="inp" id="class_name" name="class_name" placeholder="e.g. Grade 10" required>
                </div>
                <div class="dedu-input-col">
                    <label>Numeric Rank</label>
                    <input type="number" class="inp" id="numeric_name" name="numeric_name" placeholder="10" required>
                </div>
            </div>

            <hr>
            <h4>Sections</h4>
            <div id="sections-container">
                <template id="section-template">
                    <div class="dedu-form-group section-row"> 
                        <input type="text" name="sections[]" class="inp section-name" width="45%" placeholder="Section Name (e.g. A)" required> 
                        <input type="text" name="section-category" class="inp section-category" id="section-category" width="45%" placeholder="e.g. Law, Sciences, etc."> 
                        <button type="button" width="10%" class="remove-section">×</button> 
                    </div>;
                </template>
            </div>
            
            <button type="button" id="add-section-btn" class="button">+ Add Another Section</button>
            
            <div style="margin-top: 30px;">
                <button type="submit" class="button button-primary button-large">Save Class and Sections</button>
            </div>
        </form>
    </div> 
</div>

