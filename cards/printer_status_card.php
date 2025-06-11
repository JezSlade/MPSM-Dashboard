<?php
/**
 * MPSM Dashboard - Card: Printer Status
 *
 * This file is a self-contained component representing the "Printer Status" card.
 * It displays a summary of printer statuses (online, offline, warning, error).
 *
 * This file expects the following variables to be available (passed via render_card):
 * - $customer_id (int|null): The ID of the currently selected customer.
 * - $card_title (string): The title to display for this card.
 * - $status_summary (array): An associative array with counts for different statuses.
 * Example: ['online' => 15, 'offline' => 2, 'warning' => 3, 'error' => 1]
 *
 * Debugging Philosophy:
 * Every piece of data used by the card should be logged upon its inclusion.
 * This helps verify that the correct data is being passed and processed.
 */

// Ensure variables are defined, providing defaults for robustness.
$customer_id = $customer_id ?? 'N/A';
$card_title = $card_title ?? 'Printer Status Summary';
$status_summary = $status_summary ?? [
    'online' => 0,
    'offline' => 0,
    'warning' => 0,
    'error' => 0,
    'unknown' => 0 // Add unknown for robustness
];

debug_log("Rendering Printer Status Card. Title: '$card_title', Customer ID: $customer_id", 'DEBUG');
debug_log("Printer Status Data: " . json_encode($status_summary), 'DEBUG');

// Calculate total printers for display
$total_printers = array_sum($status_summary);

?>
<div class="card printer-status-card">
    <h3><?php echo sanitize_html($card_title); ?></h3>
    <?php if ($customer_id != 'N/A'): ?>
        <p class="card-subtitle">For Customer ID: <strong><?php echo sanitize_html($customer_id); ?></strong></p>
    <?php else: ?>
        <p class="card-subtitle">No customer selected.</p>
    <?php endif; ?>

    <div class="status-summary-grid">
        <div class="status-item online">
            <span class="count"><?php echo sanitize_html($status_summary['online']); ?></span>
            <span class="label">Online</span>
        </div>
        <div class="status-item offline">
            <span class="count"><?php echo sanitize_html($status_summary['offline']); ?></span>
            <span class="label">Offline</span>
        </div>
        <div class="status-item warning">
            <span class="count"><?php echo sanitize_html($status_summary['warning']); ?></span>
            <span class="label">Warning</span>
        </div>
        <div class="status-item error">
            <span class="count"><?php echo sanitize_html($status_summary['error']); ?></span>
            <span class="label">Error</span>
        </div>
        <?php if (isset($status_summary['unknown'])): ?>
            <div class="status-item unknown">
                <span class="count"><?php echo sanitize_html($status_summary['unknown']); ?></span>
                <span class="label">Unknown</span>
            </div>
        <?php endif; ?>
    </div>

    <p class="total-printers">Total Printers: <strong><?php echo sanitize_html($total_printers); ?></strong></p>

    <div class="card-actions">
        <a href="#" class="button small-button view-details-button" title="View detailed printer list">View Details</a>
    </div>

    <?php debug_log("Finished rendering Printer Status Card.", 'DEBUG'); ?>
</div>

<style>
/* Card-specific styles for Printer Status Card */
.printer-status-card {
    /* Specific overrides if needed, though general card styles handle most */
}

.printer-status-card .status-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 15px;
    margin-top: 15px;
    margin-bottom: 20px;
    padding: 10px;
    background-color: rgba(0,0,0,0.1); /* Slight darker background for the grid */
    border-radius: 8px;
}

.printer-status-card .status-item {
    text-align: center;
    padding: 10px 5px;
    border-radius: 8px;
    background-color: var(--bg-secondary-dark); /* Use secondary background for contrast */
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.printer-status-card .status-item .count {
    display: block;
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.printer-status-card .status-item .label {
    font-size: 0.9rem;
    color: var(--text-secondary);
}

/* Specific coloring for status items */
.printer-status-card .status-item.online .count { color: limegreen; }
.printer-status-card .status-item.offline .count { color: #808080; /* Dark grey */ }
.printer-status-card .status-item.warning .count { color: var(--highlight-yellow); }
.printer-status-card .status-item.error .count { color: var(--highlight-magenta); }
.printer-status-card .status-item.unknown .count { color: orange; }


.printer-status-card .total-printers {
    text-align: right;
    font-size: 1.1rem;
    color: var(--text-primary);
    font-weight: bold;
    margin-top: 10px;
}

.printer-status-card .card-actions {
    margin-top: auto; /* Push actions to the bottom */
    text-align: right;
}

.printer-status-card .small-button {
    padding: 8px 15px;
    font-size: 0.9rem;
    background-color: var(--highlight-key); /* Use a darker highlight for buttons */
    color: #fff;
    border: 1px solid var(--highlight-key);
    border-radius: 5px;
}

.printer-status-card .small-button:hover {
    background-color: var(--bg-primary); /* Darker on hover */
    border-color: var(--highlight-cyan);
    color: var(--highlight-cyan);
}
</style>