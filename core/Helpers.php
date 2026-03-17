<?php
namespace DelightEDU;

function dedu_get_current_year() {
    // Returns the saved option, or a fallback if it's not set yet
    return get_option('dedu_current_academic_year', '2025/2026');
}