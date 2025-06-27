<?php
$_widget_config = [
    'name' => 'Calendar',
    'icon' => 'calendar',
    'width' => 1,
    'height' => 1
];

/**
 * Helper function to generate calendar grid HTML.
 * This function is now local to the calendar widget.
 */
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
?>
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
