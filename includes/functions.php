<?php
function getDashboardSettings() {
    static $settings = null;
    if ($settings === null) {
        $settings = require __DIR__.'/../config/config.php';
        if (isset($_SESSION['dashboard_settings'])) {
            $settings = array_merge($settings, $_SESSION['dashboard_settings']);
        }
    }
    return $settings;
}

function generate_calendar() {
    $days_in_month = date('t');
    $first_day = date('w', strtotime(date('Y-m-01')));
    
    $html = '';
    // Calendar generation logic...
    return $html;
}