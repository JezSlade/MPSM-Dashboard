<?php
// widgets/calendar.php

// Widget Name: Calendar
// Widget Icon: fas fa-calendar
// Widget Width: 1.0
// Widget Height: 1.0

// The $_widget_config array is no longer directly used for metadata extraction
// by discover_widgets(). It's kept here for backward compatibility or other
// internal widget logic if needed. The metadata is now parsed from comments.
$_widget_config = [
    'name' => 'Calendar',
    'icon' => 'calendar', // This 'calendar' will be overridden by the comment parsing
    'width' => 1,
    'height' => 1
];

/**
 * Helper function to generate calendar grid HTML.
 * This function is now local to the calendar widget.
 * It's wrapped in function_exists() to prevent redeclaration errors
 * if this file is included multiple times (e.g., by config.php and helpers.php).
 */
if (!function_exists('generate_calendar')) {
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
}
?>
<div class="compact-content">
    <div class="calendar-header">
        <h3><?= date('F Y') ?></h3>
    </div>
    <div class="calendar-grid">
        <div class="day-name">Sun</div>
        <div class="day-name">Mon</div>
        <div class="day-name">Tue</div>
        <div class="day-name">Wed</div>
        <div class="day-name">Thu</div>
        <div class="day-name">Fri</div>
        <div class="day-name">Sat</div>
        <?= generate_calendar() ?>
    </div>
</div>
<div class="expanded-content">
    <div class="calendar-header">
        <button><i class="fas fa-chevron-left"></i></button>
        <h3><?= date('F Y') ?></h3>
        <button><i class="fas fa-chevron-right"></i></button>
    </div>
    <div class="calendar-grid">
        <div class="day-name">Sun</div>
        <div class="day-name">Mon</div>
        <div class="day-name">Tue</div>
        <div class="day-name">Wed</div>
        <div class="day-name">Thu</div>
        <div class="day-name">Fri</div>
        <div class="day-name">Sat</div>
        <?= generate_calendar() ?>
    </div>
</div>
