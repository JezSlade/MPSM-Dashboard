<?php
// config.php

// Define the path to the dashboard settings file
// This file will store the active widgets and dashboard preferences
define('DASHBOARD_SETTINGS_FILE', __DIR__ . '/dashboard_settings.json');
define('DYNAMIC_WIDGETS_FILE', __DIR__ . '/dynamic_widgets.json'); // New: Path for dynamically created widgets

/**
 * All available widgets in the system.
 * Key: unique widget ID (matches filename in widgets/ directory, without .php)
 * Value: array containing 'name', 'icon', 'width', 'height'
 *
 * 'name': Display name for the widget
 * 'icon': Font Awesome 6 icon class (e.g., 'chart-line', 'bell', 'print')
 * 'width': Default grid columns the widget spans (1-3)
 * 'height': Default grid rows the widget spans (1-4)
 */
$available_widgets = [
    'stats' => [
        'name' => 'Sales & Revenue',
        'icon' => 'chart-line',
        'width' => 2,
        'height' => 1
    ],
    'tasks' => [
        'name' => 'Task Manager',
        'icon' => 'tasks',
        'width' => 1,
        'height' => 2
    ],
    'calendar' => [
        'name' => 'Calendar',
        'icon' => 'calendar',
        'width' => 1,
        'height' => 1
    ],
    'notes' => [
        'name' => 'Quick Notes',
        'icon' => 'sticky-note',
        'width' => 1,
        'height' => 1
    ],
    'activity' => [
        'name' => 'Recent Activity',
        'icon' => 'history',
        'width' => 2,
        'height' => 1
    ],
    'printers' => [
        'name' => 'Printers Status',
        'icon' => 'print',
        'width' => 1,
        'height' => 1
    ],
    'select_customer' => [
        'name' => 'Select Customer',
        'icon' => 'users',
        'width' => 1,
        'height' => 1
    ],
    'debug_info' => [
        'name' => 'Debug Info Stream',
        'icon' => 'bug',
        'width' => 2,
        'height' => 2
    ],
    'ide' => [
        'name' => 'File Editor (IDE)',
        'icon' => 'code',
        'width' => 3,
        'height' => 3
    ]
    // Add more widgets here as needed
];

// Load dynamically created widgets and merge them
if (file_exists(DYNAMIC_WIDGETS_FILE)) {
    $dynamic_widgets_json = file_get_contents(DYNAMIC_WIDGETS_FILE);
    $dynamic_widgets = json_decode($dynamic_widgets_json, true);
    if (is_array($dynamic_widgets)) {
        $available_widgets = array_merge($available_widgets, $dynamic_widgets);
    }
}

// Sort available widgets alphabetically by name for consistent display in sidebar
uasort($available_widgets, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

?>
