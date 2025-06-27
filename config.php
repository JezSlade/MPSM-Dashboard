<?php
// config.php

$available_widgets = [];
$widget_directory = __DIR__ . '/widgets/'; // Path to the widgets folder

// Check if the directory exists
if (is_dir($widget_directory)) {
    // Scan the directory for PHP files
    $widget_files = glob($widget_directory . '*.php');

    foreach ($widget_files as $file_path) {
        // Temporarily include the file to get its configuration
        // This is done in a function scope to avoid variable collisions
        $widget_config = (function($file) {
            $_widget_config = []; // Initialize to ensure it's set
            include $file; // Include the widget file to get its $_widget_config
            return $_widget_config;
        })($file_path);

        // Extract widget ID from filename (e.g., stats.php -> stats)
        $widget_id = basename($file_path, '.php');

        // Check if the widget config is valid and contains necessary info
        if (!empty($widget_config) && isset($widget_config['name'], $widget_config['icon'])) {
            $available_widgets[$widget_id] = [
                'name' => $widget_config['name'],
                'icon' => $widget_config['icon'],
                'width' => $widget_config['width'] ?? 1, // Default to 1 if not specified
                'height' => $widget_config['height'] ?? 1 // Default to 1 if not specified
            ];
        } else {
            error_log("Warning: Widget file '{$file_path}' does not contain valid \$_widget_config.");
        }
    }
} else {
    error_log("Error: Widgets directory not found at '{$widget_directory}'");
}

// Ensure available_widgets is accessible globally if needed by other files (e.g., helpers.php directly)
// Or pass it explicitly to functions that need it.
// For simplicity in this setup, it's included by index.php which makes it available.