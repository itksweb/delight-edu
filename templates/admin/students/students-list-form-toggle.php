
<?php
if (!defined('ABSPATH')) exit;

$student_members = $student_members ?? [];
$roles         = $roles ?? [];
$classes       = $classes ?? [];
$form_meta     = $form_meta ?? [];
$part = \DEDU_PATH . 'templates/admin/partials';
$data_name = "student";
$tspan = "6"
?>
<div class="wrap list-form dedu-admin-wrapper" data-type="student"> 
    <!-- <?php wp_nonce_field('dedu_bulk_roles_action', 'dedu-student-nonce'); ?> -->
    <div class="dedu-page-header">
        <h1 class="dedu-page-title">students Management</h1>
    </div>
    <div class="dedu-card" id="dedu-list-view">
        <?php include("{$part}/tab-list-header.php") ?>
        <?php include("{$part}/table-top.php") ?>
        <div class="dedu-table-container">
            <table class="dedu-table-modern" style="min-width: 400px;">
                <thead>
                    <tr>
                        <th class="col-cb"><input type="checkbox" id="dedu-select-all"></th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $student_members ) ) : ?>
                        <?php include("{$part}/no-data.php") ?>
                    <?php else : ?>
                        <?php foreach ( $student_members as $s ) :
                            // 1. Get the real photo URL if an ID exists
                            $photo_url = '';
                            if ( ! empty( $s->profile_picture_id ) ) {
                                $photo_url = wp_get_attachment_image_url( $s->profile_picture_id, 'thumbnail' );
                            }

                            // 2. Fallback to your plugin's default image if no photo is found
                            if ( ! $photo_url ) {
                                // Option A: Use a local file in your plugin
                                $photo_url = \DEDU_URL . "assets/images/profile.jpg"; 
                                
                                // Option B: SaaS-style dynamic avatar (No image file needed!)
                                // $photo_url = "https://ui-avatars.com/api/?name=" . urlencode($s->first_name . ' ' . $s->last_name) . "&background=random";
                            }    
                        ?>
                            
                            <tr class="is-row">
                                <td class="col-cb">
                                    <input type="checkbox" class="dedu-selection-checkbox" value="<?php echo $s->id; ?>">
                                </td>
                                <td>
                                    <div class="prof">
                                        <img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( "{$s->first_name}_{$s->last_name}" ); ?>"  >
                                        <p>
                                            <span class="text-heading">
                                                <?php echo esc_html("{$s->first_name} {$s->last_name}"); ?>
                                            </span><br>
                                            <small><?php echo esc_html($s->email); ?></small>
                                        </p>
                                    </div>
                                    
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $s->status; ?>">
                                        <?php echo ucfirst($s->status); ?>
                                    </span>
                                </td>
                                <td class="dedu-row-action">
                                    <a href="javascript:void(0);" 
                                        data-id="<?php echo $s->id; ?>"
                                        data-nonce="<?php echo wp_create_nonce("dedu_student_nonce"); ?>"
                                        class="dedu-action-link edit dedu-edit-icon"
                                        title="Edit">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                    <a href="javascript:void(0);" 
                                        class="dedu-action-link delete dedu-delete-icon" 
                                        data-id="<?php echo $s->id; ?>" 
                                        data-name="<?php echo esc_attr($s->role_name); ?>" 
                                        data-fname="<?php echo esc_attr($s->first_name); ?>"
                                        data-lname="<?php echo esc_attr($s->last_name); ?>" 
                                        data-nonce="<?php echo wp_create_nonce('dedu_delete_student_' . $s->id); ?>"
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
    <div class="hide-me" id="dedu-form-view"  >
        <?php include("{$part}/tab-form-header.php") ?>
        <form id="student-form" action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="dedu_save_student">
            <input type="hidden" name="student_db_id" id="student_db_id" value="0">
            <input type="hidden" name="wp_user_id" id="wp_user_id" value="">
            <input type="hidden" name="existing_photo_url" id="existing_photo_url" value="">
            <?php wp_nonce_field('dedu_student_nonce'); ?>

            
            <?php 
                $field_name = "student_photo";
                $sub_pix = "";
                include("{$part}/profile-picture.php");
            ?>
            <div class="dedu-card">
                <fieldset class = "fields-group perseonal-details">
                    <legend class = "dedu-card-title">Personal Details</legend>
                    <div class = "unit">
                        <label>First Name*</label>
                        <input type="text" name="first_name" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Middle Name*</label>
                        <input type="text" name="middle_name" class="large-text">
                    </div>
                    <div class = "unit">
                        <label>Last Name*</label>
                        <input type="text" name="last_name" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Gender</label>
                        <select name="gender" class="large-text">
                            <?php foreach ($form_meta['genders'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Marital Status</label>
                        <select name="marital_status" class="large-text">
                            <?php foreach ($form_meta['marital_statuses'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Blood Group</label>
                        <select name="blood_group" class="large-text">
                            <?php foreach ($form_meta['blood_group'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="large-text">
                    </div>   
                    <div class = "unit">
                        <label>Address</label>
                        <input type="text" name="address" class="large-text" required>
                    </div>              
                </fieldset>
            </div>

            <div class="dedu-card parent-details">
                <?php 
                    $field_name = "parent[profile_photo]";
                    $sub_pix = "sub-pix";
                    include("{$part}/profile-picture.php");
                ?>
                <fieldset class = "fields-group">
                    <legend class = "dedu-card-title">Parent Detail</legend>
                    <div class = "unit">
                        <label>First Name*</label>
                        <input type="text" name="parent[first_name]" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Middle Name*</label>
                        <input type="text" name="parent[middle_name]" class="large-text">
                    </div>
                    <div class = "unit">
                        <label>Last Name*</label>
                        <input type="text" name="parent[last_name]" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Gender</label>
                        <select name="parent[gender]" class="large-text">
                            <?php foreach ($form_meta['genders'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Marital Status</label>
                        <select name="parent[marital_status]" class="large-text">
                            <?php foreach ($form_meta['marital_statuses'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Blood Group</label>
                        <select name="parent[blood_group]" class="large-text">
                            <?php foreach ($form_meta['blood_group'] as $k => $v) : ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="large-text">
                    </div>   
                    <div class = "unit">
                        <label>Address</label>
                        <input type="text" name="parent[address]" class="large-text" required>
                    </div>  
                    <div class = "unit">
                        <label>Email*</label>
                        <input type="email" name="parent[email]" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Password*</label>
                        <input type="password" name="parent[password]" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Phone Number</label>
                        <input type="text" name="parent[phone]" class="large-text">
                    </div>            
                </fieldset>
            </div>

            <div class="dedu-card">
                <fieldset class = "fields-group scholl-details">
                    <legend class = "dedu-card-title">School Detail</legend>
                    <div class = "unit">
                        <label>Joining Date</label>
                        <input type="date" name="joining_date" class="large-text" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class = "unit">
                        <label>Admission Number</label>
                        <input type="text" name="admission_no" >
                    </div>
                    <div class = "unit" >
                        <label>Assigned Class</label>
                        <select name="class_id" id="class-field">
                            <option value="">-- No Class Assigned --</option>
                            <?php foreach ($classes as $c) : ?>
                                <option value="<?php echo $c->id; ?>"><?php echo esc_html($c->class_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Assigned Section</label>
                        <select name="section_id" id="sections-field">
                            <option value="" disabled selected>-- select a class first --</option>
                        </select>
                    </div>
                </fieldset>
            </div>
            <div class="dedu-card">
                <fieldset class = "fields-group user-account">
                    <legend class = "dedu-card-title">Account</legend>
                    <div class = "unit">
                        <label>Email (Login Username)*</label>
                        <input type="email" name="email" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Password*</label>
                        <input type="password" name="password" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="large-text">
                    </div>
                </fieldset>
            </div>

            <div class="dedu-form-actions">
                <button type="submit" class="dedu-btn dedu-btn-primary">  
                </button>
            </div>
        </form>                  
    </div>
</div>

<style>

    .perseonal-details{
        position: relative;
        margin-top: 50px;
    }
    .parent-details {
        margin-top: 60px;
    }
    
    .prof {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
    }
    .prof img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #ef4444;
        object-fit: cover;
    }
    .sub-pix {
        width: 100px;
        height: 100px;
        transform: translateY(20px);
        /* grid-row: span 2; */
    }
    .sub-pix .upload-text {
        display: none;
    }
</style>

<script>
    const sections = <?php echo json_encode($sections_by_class); ?>;
    const classes = <?php echo json_encode($classes); ?>;
</script>