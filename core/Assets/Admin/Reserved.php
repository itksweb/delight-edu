<?php

class Reserved {
    public function __construct() {
        // Hook to inject the master toast shell into the bottom of every admin page
        add_action('admin_footer', [$this, 'dedu_render_global_toast_shell']);
    }

    public function dedu_render_global_toast_shell() {
        $screen = get_current_screen();
        
        // Safety check: Only inject the markup if we are inside your plugin pages
        if ( $screen && strpos($screen->id, 'dedu') !== false ) {
            ?>
            <div id="dedu-toast" class="dedu-toast">
                <div class="dedu-toast-content">
                    <span id="dedu-toast-icon" class="dedu-toast-icon"></span>
                    <span id="dedu-toast-text" class="dedu-toast-text"></span>
                </div>
            </div>
            <?php
        }
    }
}