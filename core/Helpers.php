<?php
namespace DelightEDU;

function dedu_get_current_year() {
    return get_option('dedu_current_academic_year', '2025/2026');
}