<?php
// widgets/printers.php

// Widget configuration
$_widget_config = [
    'name' => 'Printers Status',
    'icon' => 'print', // Font Awesome icon for printers
    'width' => 1, // Default size: 1x1
    'height' => 1
];

// Widget content rendering function
if (!function_exists('render_printers_widget')) {
    function render_printers_widget() {
        // Sample data for demonstration
        $printers_online = 1234;
        $printers_total = 1500;

        $printer_list = [
            ['id' => 'PRN001', 'location' => 'Main Office', 'status' => 'Online', 'toner' => '85%', 'pages' => '12,345'],
            ['id' => 'PRN002', 'location' => 'Annex B', 'status' => 'Offline', 'toner' => '0%', 'pages' => '5,678'],
            ['id' => 'PRN003', 'location' => 'Warehouse', 'status' => 'Online', 'toner' => '60%', 'pages' => '23,456'],
            ['id' => 'PRN004', 'location' => 'Main Office', 'status' => 'Online', 'toner' => '20%', 'pages' => '8,901'],
            ['id' => 'PRN005', 'location' => 'Annex A', 'status' => 'Maintenance', 'toner' => 'NA', 'pages' => '3,123'],
            ['id' => 'PRN006', 'location' => 'Warehouse', 'status' => 'Online', 'toner' => '50%', 'pages' => '15,000'],
            ['id' => 'PRN007', 'location' => 'Main Office', 'status' => 'Online', 'toner' => '90%', 'pages' => '7,890'],
            ['id' => 'PRN008', 'location' => 'Annex B', 'status' => 'Online', 'toner' => '30%', 'pages' => '4,321'],
            ['id' => 'PRN009', 'location' => 'Warehouse', 'status' => 'Offline', 'toner' => 'NA', 'pages' => '9,876'],
            ['id' => 'PRN010', 'location' => 'Main Office', 'status' => 'Online', 'toner' => '75%', 'pages' => '11,223']
        ];


        // Compact View Content (Visible when widget is not maximized)
        echo '<div class="compact-content">';
        echo '<div style="text-align: center; padding: 20px;">';
        echo '<p style="font-size: 48px; font-weight: bold; color: var(--accent); margin-bottom: 5px;">' . htmlspecialchars($printers_online) . '</p>';
        echo '<p style="font-size: 16px; color: var(--text-secondary);">Printers Online / ' . htmlspecialchars($printers_total) . '</p>';
        echo '</div>';
        echo '</div>'; // End compact-content

        // Expanded View Content (Visible only when widget is maximized)
        echo '<div class="expanded-content" style="padding-top: 10px;">';
        echo '<h4 style="color: var(--accent); margin-bottom: 15px;">Detailed Printer List</h4>';
        echo '<div style="max-height: 100%; overflow-y: auto;">'; // Scrollable area for table
        echo '<table style="width: 100%; border-collapse: collapse; font-size: 13px;">';
        echo '<thead>';
        echo '<tr style="background-color: var(--bg-secondary);">';
        echo '<th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--glass-border);">ID</th>';
        echo '<th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--glass-border);">Location</th>';
        echo '<th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--glass-border);">Status</th>';
        echo '<th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--glass-border);">Toner</th>';
        echo '<th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--glass-border);">Pages</th>';
        echo '<th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--glass-border);">Actions</th>'; // For drilldowns
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($printer_list as $printer) {
            $status_color = 'var(--text-primary)';
            if ($printer['status'] === 'Online') $status_color = 'var(--success)';
            if ($printer['status'] === 'Offline') $status_color = 'var(--danger)';
            if ($printer['status'] === 'Maintenance') $status_color = 'var(--warning)';

            echo '<tr>';
            echo '<td style="padding: 10px; border-bottom: 1px solid var(--glass-border);">' . htmlspecialchars($printer['id']) . '</td>';
            echo '<td style="padding: 10px; border-bottom: 1px solid var(--glass-border);">' . htmlspecialchars($printer['location']) . '</td>';
            echo '<td style="padding: 10px; border-bottom: 1px solid var(--glass-border); color: ' . $status_color . ';">' . htmlspecialchars($printer['status']) . '</td>';
            echo '<td style="padding: 10px; border-bottom: 1px solid var(--glass-border);">' . htmlspecialchars($printer['toner']) . '</td>';
            echo '<td style="padding: 10px; border-bottom: 1px solid var(--glass-border);">' . htmlspecialchars($printer['pages']) . '</td>';
            echo '<td style="padding: 10px; border-bottom: 1px solid var(--glass-border);">';
            echo '<a href="#" style="color: var(--info); text-decoration: none;">Details</a>'; // Placeholder for drilldown
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; // End scrollable area
        echo '</div>'; // End expanded-content
    }
}

return 'render_printers_widget';
