<?php
// widgets/template.php

/**
 * Widget Configuration: $_widget_config
 * This array defines the metadata for your widget.
 * It is read by config.php to register the widget in the system.
 */
$_widget_config = [
    'name' => 'Template Widget', // Required: The human-readable name of your widget.
                                 // This will appear in the sidebar and widget header.
    'icon' => 'puzzle-piece',    // Required: A Font Awesome 6 icon class name (e.g., 'chart-line', 'bell', 'print').
                                 // This icon will be displayed next to the widget's name.
    'width' => 1,                // Optional (Default: 1): The number of grid columns this widget should span.
                                 // Example: 'width' => 2 would make it twice as wide as a 1x1 widget.
    'height' => 1                // Optional (Default: 1): The number of grid rows this widget should span.
                                 // Example: 'height' => 2 would make it twice as tall as a 1x1 widget.
];


/**
 * Widget Rendering Function: render_YOUR_WIDGET_ID_widget()
 * This function generates the HTML content for your widget.
 * It is called by helpers.php when the widget needs to be displayed.
 *
 * IMPORTANT:
 * 1. The function name MUST follow the format: render_YOUR_WIDGET_ID_widget
 * where 'YOUR_WIDGET_ID' is the filename of this widget WITHOUT the '.php' extension.
 * For this file (template.php), the function name is 'render_template_widget'.
 * 2. This function definition MUST be wrapped in `if (!function_exists('...'))` to prevent
 * "Cannot redeclare function" errors, as widget files are included multiple times.
 * 3. The function should ECHO or PRINT its HTML content.
 *
 * Widget States (Compact vs. Expanded Content):
 * Widgets support different content displays based on their state (normal/minimized vs. expanded/maximized).
 * Use two main div containers:
 * - `<div class="compact-content">...</div>`: Content visible when the widget is in its normal grid size.
 * This should be a "snapshot" or summary view.
 * - `<div class="expanded-content">...</div>`: Content visible only when the widget is in its maximized (modal) state.
 * This should contain detailed data, tables, charts, or more interactive elements.
 * The visibility of these divs is controlled by CSS based on the '.maximized' class on the parent '.widget' element.
 */
if (!function_exists('render_template_widget')) {
    function render_template_widget() {
        // --- START: Data Fetching / Logic for your widget ---
        // In a real widget, you would fetch data from a database, API, or perform calculations here.
        // For this template, we'll use some placeholder data.
        $status_summary = "All systems nominal (95%)";
        $detailed_items = [
            ['name' => 'Item A', 'status' => 'Active', 'uptime' => '99.9%'],
            ['name' => 'Item B', 'status' => 'Warning', 'uptime' => '95.2%'],
            ['name' => 'Item C', 'status' => 'Offline', 'uptime' => '0%'],
            ['name' => 'Item D', 'status' => 'Active', 'uptime' => '99.8%']
        ];
        // --- END: Data Fetching / Logic ---

        // --- START: Compact View Content ---
        // This content will be displayed when the widget is in its normal, smaller size.
        // Keep it concise, showing key metrics or a summary.
        echo '<div class="compact-content">';
        echo '<div style="text-align: center; padding: 20px;">';
        echo '<p style="font-size: 28px; font-weight: bold; color: var(--accent); margin-bottom: 10px;">';
        echo htmlspecialchars($status_summary);
        echo '</p>';
        echo '<p style="font-size: 14px; color: var(--text-secondary);">';
        echo 'Quick overview of system health.';
        echo '</p>';
        echo '</div>';
        echo '</div>'; // End .compact-content

        // --- START: Expanded View Content ---
        // This content will be displayed only when the widget is in its maximized (modal) state.
        // Provide more detailed information, tables, graphs, or interactive elements.
        echo '<div class="expanded-content" style="padding-top: 10px;">';
        echo '<h4 style="color: var(--accent); margin-bottom: 15px;">Detailed System Status</h4>';
        echo '<div style="max-height: 100%; overflow-y: auto;">'; // Make content scrollable if it exceeds widget height
        echo '<table style="width: 100%; border-collapse: collapse; font-size: 13px;">';
        echo '<thead>';
        echo '<tr style="background-color: var(--bg-secondary);">';
        echo '<th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--glass-border);">Name</th>';
        echo '<th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--glass-border);">Status</th>';
        echo '<th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--glass-border);">Uptime</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($detailed_items as $item) {
            $status_color = 'var(--text-primary)';
            if ($item['status'] === 'Active') $status_color = 'var(--success)';
            if ($item['status'] === 'Warning') $status_color = 'var(--warning)';
            if ($item['status'] === 'Offline') $status_color = 'var(--danger)';

            echo '<tr>';
            echo '<td style="padding: 10px; border-bottom: 1px solid var(--glass-border);">' . htmlspecialchars($item['name']) . '</td>';
            echo '<td style="padding: 10px; border-bottom: 1px solid var(--glass-border); color: ' . $status_color . ';">' . htmlspecialchars($item['status']) . '</td>';
            echo '<td style="padding: 10px; border-bottom: 1px solid var(--glass-border);">' . htmlspecialchars($item['uptime']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; // End scrollable area
        echo '</div>'; // End .expanded-content
        // --- END: Expanded View Content ---
    }
}

/**
 * Return Function Name:
 * This line is crucial! It tells helpers.php which function within this file
 * should be called to render the widget's content.
 * Always return the string name of your rendering function.
 */
return 'render_template_widget';
