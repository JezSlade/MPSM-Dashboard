<?php
/**
 * MPSM Dashboard - Service View
 *
 * This file defines the content and layout for the "Service" view of the dashboard.
 * It's responsible for orchestrating which cards are displayed for this specific view.
 *
 * This file expects the following variables to be available:
 * - $selected_customer_id (int|null): The ID of the currently selected customer.
 *
 * Debugging Philosophy:
 * Log which cards are being attempted to be rendered and the data passed to them.
 * This helps diagnose why a specific card might not appear or displays incorrect data.
 */

// Ensure selected_customer_id is defined
$selected_customer_id = $selected_customer_id ?? null;

debug_log("Loading Service View. Selected Customer ID: " . ($selected_customer_id ?? 'None'), 'INFO');

// You can customize which cards appear on this view.
// This example hardcodes a few, but you could dynamically load them based on user preferences
// or customer type in a more advanced implementation.

// Data specific to this view that might be passed to cards.
// In a real application, this would involve fetching data from your DB or MPS Monitor API.
$service_data = [
    'customer_id' => $selected_customer_id,
    'today' => date('Y-m-d'),
    'current_time' => date('H:i:s'),
    // ... more data relevant to service view
];

debug_log("Rendering cards for Service View...", 'INFO');
?>

<?php
// Example: Printer Status Card
// This card might show a summary of printer statuses for the selected customer.
debug_log("Attempting to render 'printer_status_card' for Service View.", 'DEBUG');
render_card('printer_status_card', array_merge($service_data, [
    'card_title' => 'Overall Printer Status',
    'status_summary' => [
        'online' => 15,
        'offline' => 2,
        'warning' => 3,
        'error' => 1
    ]
]));
?>

<?php
// Example: Toner Levels Card
// This card could display average toner levels or warn about low toner.
debug_log("Attempting to render 'toner_levels_card' for Service View.", 'DEBUG');
render_card('toner_levels_card', array_merge($service_data, [
    'card_title' => 'Average Toner Levels',
    'toner_data' => [
        'black' => 75,
        'cyan' => 60,
        'magenta' => 40,
        'yellow' => 20
    ],
    'low_threshold' => 25
]));
?>

<?php
// Example: Service Tickets Card
// This card could list open service tickets.
debug_log("Attempting to render 'service_tickets_card' for Service View.", 'DEBUG');
render_card('service_tickets_card', array_merge($service_data, [
    'card_title' => 'Open Service Tickets',
    'tickets' => [
        ['id' => 'ST001', 'subject' => 'Printer Jam - Office A', 'status' => 'Open', 'priority' => 'High'],
        ['id' => 'ST002', 'subject' => 'Toner Replacement - Printer 5', 'status' => 'Pending', 'priority' => 'Medium'],
        ['id' => 'ST003', 'subject' => 'Connectivity Issue - Branch X', 'status' => 'Open', 'priority' => 'High'],
    ]
]));
?>

<?php
// Example: Performance Metrics Card (Placeholder)
// This card demonstrates how new cards can be added.
debug_log("Attempting to render 'performance_metrics_card' for Service View.", 'DEBUG');
render_card('performance_metrics_card', array_merge($service_data, [
    'card_title' => 'Device Performance Metrics',
    'metrics' => [
        'uptime_avg' => '99.8%',
        'pages_printed_total' => '1.2M',
        'error_rate' => '0.5%'
    ]
]));
?>

<?php debug_log("Finished rendering cards for Service View.", 'INFO'); ?>