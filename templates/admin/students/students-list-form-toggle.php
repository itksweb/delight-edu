
<?php
if (!defined('ABSPATH')) exit;

$students = $students ?? [];
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
                    <?php if ( empty( $students ) ) : ?>
                        <?php include("{$part}/no-data.php") ?>
                    <?php else : ?>
                        <?php foreach ( $students as $s ) :
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
            <input type="hidden" name="wp_user_id" id="wp_user_id" value="">
            <?php wp_nonce_field('dedu_student_nonce'); ?>

            
            <div class="personal-details">
                <?php 
                    $field_name = "student_photo";
                    $sub_pix = "";
                    include("{$part}/profile-picture.php");
                ?>
                <div class="dedu-card">        
                    <fieldset class = "fields-group">
                        <legend class = "dedu-card-title"><h2>Personal Details</h2></legend>
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
            </div>

            <div class="dedu-card">
                <fieldset class = "fields-group school-details">
                    <legend class = "dedu-card-title"><h2>School Detail</h2></legend>
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
                        <select name="class_id" id="class-field" required>
                            <option value="">-- No Class Assigned --</option>
                            <?php foreach ($classes as $c) : ?>
                                <option value="<?php echo $c->id; ?>"><?php echo esc_html($c->class_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class = "unit">
                        <label>Assigned Section</label>
                        <select name="section_id" id="sections-field" required>
                            <option value="" disabled selected>-- select a class first --</option>
                        </select>
                    </div>
                </fieldset>
            </div>

            <div class="dedu-card">
                <fieldset class = "fields-group user-account">
                    <legend class = "dedu-card-title"><h2>Account</h2></legend>
                    <div class = "unit">
                        <label>Email (Login Username)*</label>
                        <input type="email" name="email" class="large-text" required>
                    </div>
                    <div class = "unit">
                        <label>Password*</label>
                        <input type="password" name="password" >
                    </div>
                    <div class = "unit">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="large-text">
                    </div>
                </fieldset>
            </div>

            <div class="dedu-card" id="parents-container">
                <div class="dedu-parent-card-header" >
                    <h2 class="dedu-card-title" >Parent/Guardian Details</h2>
                    <button type="button" id="add-parent-btn" class="button button-secondary">
                        <span class="dashicons dashicons-plus"></span>
                        Add Another
                    </button>
                </div>

                <!-- The Wrapper for the list of parents -->
                <div id="parents-list">
                     
                </div>
            </div>
            <template id="entry">
                <div class="parent-entry">
                    <div class="dedu-parent-guardian-top fields-group">
                        <div class="unit parent-mode-toggle" >
                            <label>
                                <input type="radio" name="parents[0][mode]" value="new" class="parent-mode-switch" required> Create New Parent
                            </label>
                            <label >
                                <input type="radio" name="parents[0][mode]" value="existing" class="parent-mode-switch" required> Select Existing Parent
                            </label>
                        </div>
                        <div class="rel-switch" >
                            <span class="labels">
                                <?php foreach ($form_meta['relationship'] as $k => $v) : ?>
                                    <label data-id="<?php echo $k; ?>" class="<?php echo $k; ?>" >
                                        <input type="radio" name="parents[0][relationship]" value="<?php echo $k; ?>" class = "<?php echo "{$k}-btn input-rel"; ?>" required> 
                                        <span class="lab" ><?php echo $v; ?></span> 
                                    </label>
                                <?php endforeach;  ?>
                            </span>
                            <input type="text" class="radio-input hide-me">
                        </div>
                        <!-- Existing Parent Search (Hidden by default) -->
                        <div class="unit existing-parent-selector hide-me">
                            <label>Search Existing Parent (Phone or Email)</label>
                            <select name="parents[0][existing_id]" required>
                                <option value="">-- Select Parent --</option>
                            </select>
                        </div>
                    </div>
                    <!-- New Parent Fields -->
                    <div class="parent-fields hide-me">
                        <?php 
                            $field_name = "parents[0][profile_photo]";
                            $sub_pix = "sub-pix";
                            include("{$part}/profile-picture.php");
                        ?>
                        <fieldset class = "fields-group ">
                            <legend class = "dedu-card-title">Parent Detail</legend>
                            <div class = "unit">
                                <label>First Name*</label>
                                <input type="text" name="parents[0][first_name]" class="parent-required" required>
                            </div>
                            <div class = "unit">
                                <label>Middle Name</label>
                                <input type="text" name="parents[0][middle_name]" class="large-text">
                            </div>
                            <div class = "unit">
                                <label>Last Name*</label>
                                <input type="text" name="parents[0][last_name]" class="parent-required" required>
                            </div>
                            <div class = "unit">
                                <label>Gender</label>
                                <select name="parents[0][gender]" class="large-text">
                                    <?php foreach ($form_meta['genders'] as $k => $v) : ?>
                                        <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class = "unit">
                                <label>Marital Status</label>
                                <select name="parents[0][marital_status]" class="large-text">
                                    <?php foreach ($form_meta['marital_statuses'] as $k => $v) : ?>
                                        <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class = "unit">
                                <label>Blood Group</label>
                                <select name="parents[0][blood_group]" class="large-text">
                                    <?php foreach ($form_meta['blood_group'] as $k => $v) : ?>
                                        <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class = "unit">
                                <label>Address</label>
                                <input type="text" name="parents[0][address]" class="parent-required">
                            </div>  
                            <div class = "unit">
                                <label>Email*</label>
                                <input type="email" name="parents[0][email]" class="large-text">
                            </div>
                            <div class = "unit">
                                <label>Password*</label>
                                <input type="password" name="parents[0][password]" class="large-text">
                            </div>
                            <div class = "unit">
                                <label>Phone Number</label>
                                <input type="text" name="parents[0][phone]" class="">
                            </div>            
                        </fieldset>
                    </div>
                    <button type="button" class="remove-parent-btn hide-me">
                        - Remove this parent
                    </button>
                </div>
            </template>

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
        
        .fields-group legend {
            margin-bottom: 50px;
        }
    }

    #parents-container {
        .dedu-parent-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;

            h2 { margin:0; }
            #add-parent-btn {
                display: flex;
                align-items: center;
                gap: 5px;
            }
        }

        #parents-list .parent-entry {
            margin-top: 30px;
            background-color:#f6f6fa;
            padding: 20px;
            border-radius: 8px;
            

            .dedu-parent-guardian-top {
                align-items: flex-start;
                border-bottom: 1px solid #eee;
                margin-bottom: 40px;
                padding-bottom: 20px;

                .parent-mode-toggle{
                    align-items: flex-start;
                    flex-wrap: wrap;
                }
                .rel-switch {
                    display: grid;
                    gap: 20px;
                    align-items: flex-end;
                    justify-content: flex-end;

                    .labels {
                        display: flex;
                        align-items: flex-start;
                        justify-content: flex-end;
                        column-gap: 10px;
                    }       
                }
                .rel-switch.hide-me {
                    display: none !important;
                }

            }
            .dedu-parent-guardian-top.hide-me {
                display: none;
            }
            .parent-fields .sub-pix {
                width: 100px;
                height: 100px;
                transform: translateY(-80px);

                .upload-text {
                    display: none;
                }
            }
            .remove-parent-btn {
                color:red;
                border:none;
                background:none;
                cursor:pointer;
                margin-top:10px;
            }

        }
        
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
    
    .space-up{
        margin-top: 60px;
    }
</style>

