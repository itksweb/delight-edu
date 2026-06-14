<?php
if (!defined('ABSPATH'))  exit;
$data_name = "settings";
?>

<div class="wrap dedu-admin-wrapper" data-type="<?php echo $data_name ?>"> 
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">School System Configuration</h1>
    </div>

    <div class="dedu-card" id="settings-view"  >
        <form method="POST" action="options.php">
            <?php
            // Outputs security nonces, actions, and referers automatically
            settings_fields('dedu_global_settings_group');
            ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th colspan="2"><h2>Core Identity Settings</h2></th>
                    </tr>

                    <tr>
                        <th scope="row"><label for="dedu_school_name">Name of school</label></th>
                        <td>
                            <input 
                                type="text" 
                                id="dedu_school_name" 
                                name="dedu_school_name" 
                                value="<?php echo esc_attr($school_name); ?>" 
                                class="regular-text"
                                placeholder="e.g. Hannywealth School"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dedu_school_motto">School Motto</label></th>
                        <td>
                            <input 
                                type="text" 
                                id="dedu-school-motto" 
                                name="dedu_school_motto" 
                                value="<?php echo esc_attr($school_motto); ?>" 
                                class="regular-text"
                                placeholder="e.g. Knowledge is king"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dedu_current_academic_year">Current Academic Year</label></th>
                        <td>
                            <input 
                                type="text" 
                                id="dedu_current_academic_year" 
                                name="dedu_current_academic_year" 
                                value="<?php echo esc_attr($current_year); ?>" 
                                class="regular-text"
                                placeholder="e.g. 2025/2026"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dedu_staff_id_prefix">Staff ID Prefix</label></th>
                        <td>
                            <input 
                                type="text" 
                                id="dedu_staff_id_prefix" 
                                name="dedu_staff_id_prefix" 
                                value="<?php echo esc_attr($staff_prefix); ?>" 
                                class="regular-text"
                                placeholder="e.g. TCH"
                            />
                            <p class="description">Will automatically convert to uppercase on save.</p>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h2>Academic Timeline Structures</h2></th>
                    </tr>

                    <tr>
                        <th scope="row"><label for="dedu_term_naming_label">Term Structure Label</label></th>
                        <td>
                            <input 
                                type="text" 
                                id="dedu_term_naming_label" 
                                name="dedu_term_naming_label" 
                                value="<?php echo esc_attr($term_label); ?>" 
                                class="regular-text"
                                placeholder="e.g. Term, Semester, Cohort"
                            />
                            <p class="description">Used for calendar placeholder generation (e.g. "Semester" creates "Semester 1").</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="dedu_session_divisions">Divisions Per Session</label></th>
                        <td>
                            <input 
                                type="number" 
                                id="dedu_session_divisions" 
                                name="dedu_session_divisions" 
                                value="<?php echo esc_attr($divisions); ?>" 
                                class="small-text"
                                min="1"
                                max="12"
                            />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="dedu_term_naming_style">Term Naming Style</label></th>
                        <td>
                            <?php $current_style = get_option('dedu_term_naming_style', 'ordinal'); ?>
                            <select name="dedu_term_naming_style">
                                <option value="ordinal" <?php selected($current_style, 'ordinal'); ?>>Ordinal (1st Term, 2nd Term...)</option>
                                <option value="numeric" <?php selected($current_style, 'numeric'); ?>>Numeric (Term 1, Term 2...)</option>
                            </select>
                            <p class="description">Defines how terms are initialized on drawer generation configurations.</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php 
            // Generates native WordPress primary submit button
            submit_button('Save Settings Configurations'); 
            ?>
        </form>                  
    </div>
</div>

<!-- <script>
    // Simple live preview for the admin
    
</script> -->