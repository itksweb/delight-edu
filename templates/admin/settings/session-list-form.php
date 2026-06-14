<?php
if (!defined('ABSPATH')) exit;
$part = \DEDU_PATH . 'templates/admin/partials';
$data_name = "session";
$tspan = "5";
?>

<div class="wrap dedu-admin-wrapper" data-type="<?php echo $data_name ?>">
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">Manage School Session<</h1>
    </div>
    <div class="dedu-card" id="dedu-list-view">
        <?php include("{$part}/tab-list-header.php") ?>
        <?php include("{$part}/table-top.php") ?>
        <div class="dedu-table-container">
            <table class="dedu-table-modern" style="min-width: 400px;">
                <thead>
                    <tr>
                        <th class="col-cb"><input type="checkbox" id="dedu-select-all"></th>
                        <th class="manage-column column-primary" style="width: 30%;">Session Name</th>
                        <th style="width: 25%;">Start Date</th>
                        <th style="width: 25%;">End Date</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $sessions ) ) : ?>
                        <?php include("{$part}/no-data.php") ?>
                    <?php else : ?>
                        <?php foreach ( $sessions as $s ) : ?>
                            <tr class="is-row">
                                <td class="col-cb">
                                    <input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $s->id; ?>">
                                </td>
                                
                                <td class="text-heading">
                                    <?php echo esc_html($s->session_name); ?>
                                </td>
                                
                                <td><?php echo esc_html($s->starts); ?></td>
                                <td><?php echo esc_html($s->ends); ?></td>
                                <td class="dedu-row-action">
                                    <a href="javascript:void(0);" 
                                    class="dedu-action-link edit dedu-edit-icon"
                                        data-id="<?php echo $s->id; ?>"
                                        data-name="<?php echo esc_attr($s->session_name); ?>"
                                        title="Edit">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                    <a href="javascript:void(0);" 
                                        class="dedu-action-link delete dedu-delete-icon" 
                                        data-id="<?php echo $s->id; ?>" 
                                        data-name="<?php echo esc_attr($s->session_name); ?>" 
                                        data-nonce="<?php echo wp_create_nonce('dedu_delete_session_' . $s->id); ?>"
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
            <template id="session-template">
                <tr class="is-row">
                    <td class="col-cb">
                        <input type="checkbox" class="dedu-selection-checkbox" value="">
                    </td>
                    <td class="text-heading"></td>
                    <td></td>
                    <td></td>
                    <td class="dedu-row-action">
                        <a href="javascript:void(0);" 
                        class="dedu-action-link edit dedu-edit-icon"
                            data-id=""
                            data-name=""
                            title="Edit">
                            <span class="dashicons dashicons-edit"></span>
                        </a>
                    </td>
                </tr>
            </template>
        </div>
        <?php include("{$part}/table-bottom.php") ?>
    </div> 
</div>

<div id="dedu-session-drawer" class="dedu-drawer">
    <div class="dedu-drawer-header">
        <h3 id="dedu-drawer-title">Add New Academic Session</h3>
        <button type="button" id="dedu-close-drawer" class="dedu-close-btn">&times;</button>
    </div>
    
    <form id="dedu-session-form">
        <?php wp_nonce_field('dedu_session_drawer_action', 'drawer_nonce'); ?>
        <input type="hidden" id="drawer_session_id" name="session_id" value="">

        <div id="drawer-session-section" class="st-group">
            <input type="text" id="drawer_session_name" name="session_name" placeholder="e.g., 2026/2027" class="st-name" required>
            <input type="text" id="drawer_starts" class ="date-space start-date" name="starts" placeholder="Start Date">
            <input type="text" id="drawer_ends" class ="date-space end-date" name="ends" placeholder="End Date" >
        </div>
        <hr>
        <?php include("{$part}/dedu-toggle.php") ?>
        <div id="term-rows"></div>

        <div class="dedu-form-actions" style="margin-top: 15px;">
            <button type="submit" id="dedu-drawer-submit-btn" class="dedu-btn dedu-btn-primary" style="width: 100%;">
                Save Session Configurations
            </button>
        </div>
        <template id="session-date-template">
            <div class="dates">
                <input type="text" id="drawer_starts" class ="date-space" name="starts" placeholder="Start Date">
                <input type="text" id="drawer_ends" class ="date-space" name="ends" placeholder="End Date" >
            </div>
            <input type="text" class="date-space" name="" value="">
        </template>
        <template id="term-template">
            <div class="term-row st-group">
                <input type="hidden" name="terms[0][term_id]" value="">
                <input type="text" class="st-name" tabindex="-1" placeholder="1st Term" name="terms[0][term_name]" value="" readonly>
                <input type="text" class="date-space start-date" placeholder="Start Date" name="terms[0][starts]" value="">
                <input type="text" class="date-space end-date" placeholder="End Date" name="terms[0][ends]" value="">
            </div>
        </template>
    </form>
</div>

<style>
    .dedu-drawer {
        overflow-y: auto;
        input {
            width: 100%;
            padding: 0 10px;
            font-size: 12px;
            border: 1px solid #ccc;
            border-radius: 3px;
            height: auto;
            line-height: 0.8;
        }
        .st-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 5px;
            .st-name { grid-column: 1 / -1; }
        }
        #term-rows {
            display: grid;
            gap: 10px;
            padding-top: 10px;
            .term-row .st-name{
                pointer-events: none;
                border: none;
            }
        }
    }    
</style>
