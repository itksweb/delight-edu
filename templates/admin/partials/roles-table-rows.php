<?php foreach ($all_classes as $class) : ?>
    <tr>
        <td class="col-cb"><input type="checkbox" class="dedu-role-checkbox" value="<?php echo $class->id; ?>">
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
            <a href="#" class="dedu-action-link edit" title="Edit">
                <span class="dashicons dashicons-edit"></span>
            </a>
            <a href="javascript:void(0);" 
                class="dedu-action-link delete dedu-delete-class" 
                data-id="<?php echo $class->id; ?>" 
                data-name="<?php echo esc_attr($class->class_name); ?>" 
                data-nonce="<?php echo wp_create_nonce('dedu_delete_class_' . $class->id); ?>"
                title="Delete">
                    <span class="dashicons dashicons-trash"></span>
            </a>
        </td>
    </tr>
<?php endforeach; ?>