<?php
if (!defined('ABSPATH')) exit;

// Get the status message if it exists
$message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Master Subject List</h1>
    <hr class="wp-header-end">

    <?php if ($message === 'subject_created') : ?>
        <div class="notice notice-success is-dismissible"><p>Subject successfully added to master list.</p></div>
    <?php elseif ($message === 'save_failed') : ?>
        <div class="notice notice-error is-dismissible"><p>Failed to save subject. It might already exist.</p></div>
    <?php endif; ?>

    <div id="col-container" class="wp-clearfix">
        
        <div id="col-left">
            <div class="col-wrap">
                <div class="form-wrap">
                    <h2>Add New Subject</h2>
                    <p>Enter a subject name that will be available school-wide (e.g., Mathematics, Physics).</p>
                    
                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                        <input type="hidden" name="action" value="dedu_add_master_subject">
                        <?php wp_nonce_field('dedu_master_subject_nonce'); ?>

                        <div class="form-field form-required">
                            <label for="subject_name">Subject Name</label>
                            <input name="subject_name" id="subject_name" type="text" value="" size="40" aria-required="true" required>
                        </div>

                        <div class="form-field">
                            <label for="subject_type">Subject Type</label>
                            <select name="subject_type" id="subject_type">
                                <option value="core">Core (Compulsory)</option>
                                <option value="elective">Elective (Optional)</option>
                                <option value="vocational">Vocational</option>
                            </select>
                        </div>

                        <p class="submit">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Add to Master List">
                        </p>
                    </form>
                </div>
            </div>
        </div><div id="col-right">
            <div class="col-wrap">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-name">Name</th>
                            <th scope="col" class="manage-column column-type">Type</th>
                            <th scope="col" class="manage-column column-id">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($subjects)) : ?>
                            <?php foreach ($subjects as $subject) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($subject->subject_name); ?></strong></td>
                                    <td><?php echo esc_html(ucfirst($subject->subject_type)); ?></td>
                                    <td><code><?php echo esc_html($subject->id); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3">No subjects found in the master list.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div></div></div>