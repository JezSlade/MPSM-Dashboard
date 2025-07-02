<?php
// widgets/printers.php

// Widget Name: Printers Status
// Widget Icon: fas fa-print
// Widget Width: 1.0
// Widget Height: 1.0

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
        echo '<div class="neomorphic-card p-4 text-center h-full flex flex-col justify-center">';
        echo '<p class="text-5xl font-bold text-[var(--accent)] mb-2">' . htmlspecialchars($printers_online) . '</p>';
        echo '<p class="text-lg text-[var(--text-secondary)]">Printers Online / ' . htmlspecialchars($printers_total) . '</p>';
        echo '</div>';
        echo '</div>'; // End compact-content

        // Expanded View Content (Visible only when widget is maximized)
        echo '<div class="expanded-content">';
        echo '<div class="neomorphic-card p-4 h-full flex flex-col">';
        echo '<h4 class="text-xl font-bold text-[var(--accent)] mb-4">Detailed Printer List</h4>';
        echo '<div class="printers-table-container flex-grow">'; // Scrollable area for table
        echo '<table class="printers-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Location</th>';
        echo '<th>Status</th>';
        echo '<th>Toner</th>';
        echo '<th>Pages</th>';
        echo '<th>Actions</th>'; // For drilldowns
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($printer_list as $printer) {
            $status_class = '';
            if ($printer['status'] === 'Online') $status_class = 'neomorphic-badge green';
            if ($printer['status'] === 'Offline') $status_class = 'neomorphic-badge red';
            if ($printer['status'] === 'Maintenance') $status_class = 'neomorphic-badge yellow';

            echo '<tr>';
            echo '<td>' . htmlspecialchars($printer['id']) . '</td>';
            echo '<td>' . htmlspecialchars($printer['location']) . '</td>';
            echo '<td><span class="' . $status_class . '">' . htmlspecialchars($printer['status']) . '</span></td>';
            echo '<td>' . htmlspecialchars($printer['toner']) . '</td>';
            echo '<td>' . htmlspecialchars($printer['pages']) . '</td>';
            echo '<td>';
            echo '<a href="#" class="btn-link">Details</a>'; // Placeholder for drilldown
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; // End scrollable area
        echo '</div>'; // End neomorphic-card
        echo '</div>'; // End expanded-content
    }
}

return 'render_printers_widget';
