<?php
// helpers.php

// No longer needs to require config.php here directly as index.php handles it.
// If helpers.php was ever called directly without index.php, you'd need to re-add it.

// Function to render widget content
function render_widget($widget_id) {
    $widget_file_path = __DIR__ . '/widgets/' . $widget_id . '.php';

    if (file_exists($widget_file_path)) {
        ob_start(); // Start output buffering to capture widget HTML
        include $widget_file_path; // Include the widget's PHP file
        return ob_get_clean(); // Return the captured HTML
    } else {
        // Fallback for widgets not found
        return '<div class="widget-content">Widget ' . htmlspecialchars($widget_id) . ' content not found.</div>';
    }
}

// Helper function to generate calendar (remains the same unless moved to its own file)
function generate_calendar() {
    $days_in_month = date('t');
    $first_day = date('w', strtotime(date('Y-m-01')));

    $html = '';

    // Empty days for the first week
    for ($i = 0; $i < $first_day; $i++) {
        $html .= '<div class="day empty"></div>';
    }

    // Days of the month
    for ($day = 1; $day <= $days_in_month; $day++) {
        $today = ($day == date('j')) ? ' today' : '';
        $event = (in_array($day, [7, 10, 17, 24])) ? ' event' : '';
        $html .= '<div class="day'.$today.$event.'">'.$day.'</div>';
    }

    // Fill remaining empty days
    $remaining = 42 - $days_in_month - $first_day;
    for ($i = 0; $i < $remaining; $i++) {
        $html .= '<div class="day empty"></div>';
    }

    return $html;
}