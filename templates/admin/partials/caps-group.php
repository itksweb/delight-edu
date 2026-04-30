<div class="dedu-permission-card">
    <div class="cap-list-head">
        <h4 class="dedu-group-label">
            <?php echo esc_html($group_name); ?>
            <small><span class="dashicons dashicons-plus"></span></small>
        </h4>
        <label class="dedu-checkbox-label">
            <span class="dedu-checkbox-text">Select All</span>
            <input type="checkbox" name="<?php echo esc_html($group_name); ?>" class="check-all-caps" >
        </label>
    </div>
    <div class="dedu-cap-list">
        <?php foreach ($caps as $cap_slug => $cap_label) : ?>
            <label class="dedu-checkbox-label">
                <input type="checkbox" name="<?php echo esc_attr($caps_name) ?>"
                    class="cap-checkbox" 
                    value="<?php echo esc_attr($cap_slug); ?>" >
                    <span class="dedu-checkbox-text"><?php echo esc_html($cap_label); ?></span>
            </label>
        <?php endforeach; ?>
    </div>  
</div>

<style>
    .dedu-permission-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 15px;
    }

    .cap-list-head {
        display: flex;
        border-bottom: 1px solid #e2e8f0;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        font-size: 12px;
    }

    .dedu-group-label {
        margin: 0;
        color: #334155;
        cursor: pointer;
    }

    .dedu-group-label .dashicons {
        font-size: 12px;
    }

    .dedu-cap-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .dedu-checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: #475569;
    }

    .dedu-checkbox-label:hover .dedu-checkbox-text {
        color: #2563eb;
    }
</style>