<?php 
    $items_array = !empty($the_list) ?  $the_list : [];
    $count = count($items_array);
    $label =  sprintf(_n('%d section', '%d sections', $count, 'delight-edu'), $count);
    if ($count) {
        echo '<div class="dedu-tooltip-container">';
            printf('<span class="dedu-badge">%s</span>', $label );
            echo '<span class="dedu-tooltip-text">' . esc_html(implode(", ", $items_array)) . '</span>';
        echo '</div>';
    } else {
        echo "<span class='dedu-badge empty'>{$label}</span>";
    }        
?>

<style>
    /* Container for the tooltip */
    .dedu-tooltip-container {
        position: relative;
        display: inline-block;
        cursor: help;
    }

    /* The actual tooltip bubble (hidden by default) */
    .dedu-tooltip-text {
        visibility: hidden;
        width: 140px;
        background-color: #32373c;
        /* WordPress Dark Grey */
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 8px;
        position: absolute;
        z-index: 10;
        bottom: 125%;
        /* Position above the badge */
        left: 50%;
        margin-left: -70px;
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 11px;
        line-height: 1.4;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    /* The little triangle arrow at the bottom of the bubble */
    .dedu-tooltip-text::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #32373c transparent transparent transparent;
    }

    /* Show the tooltip on hover */
    .dedu-tooltip-container:hover .dedu-tooltip-text {
        visibility: visible;
        opacity: 1;
    }
</style>
    