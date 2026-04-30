<?php
if (!defined('ABSPATH')) exit;
$part = \DEDU_PATH . 'templates/admin/partials';
$data_name = "class";
$tspan = "5"
?>

<div class="wrap dedu-admin-wrapper" data-type="class">
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Classes And Sections Management</h1>
    </div>
    <div class="dedu-card" id="dedu-list-view" >
        <?php include("{$part}/tab-list-header.php") ?>
        <?php include("{$part}/table-top.php") ?>
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
                        <?php include("{$part}/no-data.php") ?>
                    <?php else : ?>
                    <?php foreach ($all_classes as $class) : ?>
                        <tr class="is-row">
                            <td class="col-cb"><input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $class->id; ?>">
                                    </td>
                            <td class="text-heading"><?php echo esc_html($class->class_name); ?></td>
                            <td><?php echo esc_html($class->numeric_name); ?></td>
                            <td>
                                <?php 
                                    $the_list = $class->section_list;
                                    include("{$part}/badge-with-tooltip.php");                     
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
                    <?php include("{$part}/no-search-result.php") ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php include("{$part}/table-bottom.php") ?>
    </div>
    <div class="dedu-card hide-me" id="dedu-form-view"  >
        <?php include("{$part}/tab-form-header.php") ?>
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
                    </div>
                </template>
            </div>
            
            <button type="button" id="add-section-btn" class="button">+ Add Another Section</button>
            
            <div style="margin-top: 30px;">
                <button type="submit" class="button button-primary button-large">Save Class and Sections</button>
            </div>
        </form>
    </div> 
</div>
