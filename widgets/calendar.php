<?php
$_widget_config = [
    'name' => 'Calendar',
    'icon' => 'calendar',
    'width' => 1,
    'height' => 1
];
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

    <?= generate_calendar() // Assuming generate_calendar is in helpers.php and included ?>
</div>