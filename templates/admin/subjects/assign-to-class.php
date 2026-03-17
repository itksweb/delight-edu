<?php
if (!defined('ABSPATH')) exit;

$message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
$current_year = \dedu_get_current_year(); 
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Class Curriculum (<?php echo esc_html($current_year); ?>)</h1>
    <a href="<?php echo admin_url('admin.php?page=dedu-subjects'); ?>" class="page-title-action">Back to Master List</a>
    <hr class="wp-header-end">

    <?php if ($message === 'subject_assigned') : ?>
        <div class="notice notice-success is-dismissible"><p>Subject successfully added to curriculum.</p></div>
    <?php elseif ($message === 'assignment_failed') : ?>
        <div class="notice notice-error is-dismissible"><p>Error: Subject might already be assigned to this class for this year.</p></div>
    <?php endif; ?>

    <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
        <h2 style="margin-top:0;">Assign Subject to Class</h2>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="dedu_assign_subject_to_class">
            <?php wp_nonce_field('dedu_assign_subject_nonce', '_wpnonce'); ?>

            <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                
                <div class="form-group">
                    <label style="display:block; margin-bottom:5px;">Select Class</label>
                    <select name="class_id" required>
                        <option value="">-- Class --</option>
                        <?php foreach ($classes as $class) : ?>
                            <option value="<?php echo $class->id; ?>"><?php echo esc_html($class->class_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display:block; margin-bottom:5px;">Select Subject</label>
                    <select name="subject_id" required>
                        <option value="">-- Subject --</option>
                        <?php foreach ($master_subjects as $sub) : ?>
                            <option value="<?php echo $sub->id; ?>"><?php echo esc_html($sub->subject_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display:block; margin-bottom:5px;">Subject Code</label>
                    <input type="text" name="subject_code" placeholder="e.g. MTH101" style="width:100px;">
                </div>

                <div class="form-group">
                    <label style="display:block; margin-bottom:5px;">Pass Mark (%)</label>
                    <input type="number" name="pass_mark" value="40" style="width:70px;">
                </div>

                <input type="submit" class="button button-primary" value="Add to Curriculum">
            </div>
        </form>
    </div>

    <br>

    <h2>Current Curriculum for <?php echo esc_html($current_year); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" style="width: 25%;">Class</th>
                <th scope="col" style="width: 25%;">Subject</th>
                <th scope="col">Code</th>
                <th scope="col">Type</th>
                <th scope="col">Pass Mark</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($assigned_list)) : ?>
                <?php foreach ($assigned_list as $item) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($item->class_name); ?></strong></td>
                        <td><?php echo esc_html($item->subject_name); ?></td>
                        <td><code><?php echo esc_html($item->subject_code ?: 'N/A'); ?></code></td>
                        <td><?php echo esc_html(ucfirst($item->subject_type ?? 'core')); ?></td>
                        <td><?php echo esc_html($item->pass_mark); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">No curriculum defined for this academic year yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>