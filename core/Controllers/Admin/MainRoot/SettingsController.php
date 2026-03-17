<?php
namespace DelightEDU\Controllers\Admin\MainRoot;


class SettingsController {
    public function render_settings_page() {
        // 1. Save logic
        if ( isset($_POST['dedu_save_settings']) && check_admin_referer('dedu_settings_verify') ) {
            update_option('dedu_current_academic_year', sanitize_text_field($_POST['academic_year']));
            
            // Save the Staff Prefix
            $prefix = sanitize_text_field($_POST['staff_id_prefix']);
            update_option('dedu_staff_id_prefix', strtoupper($prefix)); // Save as uppercase for consistency
            
            echo '<div class="updated"><p>Settings saved successfully!</p></div>';
        }

        // 2. Fetch current values
        $current_year = get_option('dedu_current_academic_year', date('Y') . '/' . (date('Y') + 1));
        $staff_prefix = get_option('dedu_staff_id_prefix', 'EDU'); // Default to 'EDU'
        ?>
        
        <div class="wrap">
            <h1>Delight EDU Settings</h1>
            <form method="post">
                <?php wp_nonce_field('dedu_settings_verify'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="academic_year">Current Academic Year</label></th>
                        <td>
                            <input type="text" id="academic_year" name="academic_year" value="<?php echo esc_attr($current_year); ?>" class="regular-text" placeholder="e.g. 2025/2026">
                            <p class="description">This year will be used as the default for all new records.</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="staff_id_prefix">Staff ID Prefix</label></th>
                        <td>
                            <input type="text" id="staff_id_prefix" name="staff_id_prefix" value="<?php echo esc_attr($staff_prefix); ?>" class="regular-text" placeholder="e.g. EDU or SCH">
                            <p class="description">
                                <strong>Preview:</strong> 
                                <code id="prefix-preview"><?php echo esc_html($staff_prefix); ?>-<?php echo date('y'); ?>-001</code>
                            </p>
                            <p class="description">This prefix is used when auto-generating IDs for new staff members.</p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="dedu_save_settings" class="button button-primary" value="Save Settings">
                </p>
            </form>
        </div>

        <script>
        // Simple live preview for the admin
        document.getElementById('staff_id_prefix').addEventListener('input', function() {
            const prefix = this.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); // Clean prefix
            const year = '<?php echo date('y'); ?>';
            document.getElementById('prefix-preview').textContent = (prefix || '___') + '-' + year + '-001';
        });
        </script>
        <?php
    }
}