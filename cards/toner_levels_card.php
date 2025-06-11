<?php
/**
 * MPSM Dashboard - Card: Toner Levels
 *
 * This file is a self-contained component for displaying toner levels.
 * It visualizes toner levels for different colors and highlights low toner.
 *
 * This file expects the following variables to be available (passed via render_card):
 * - $customer_id (int|null): The ID of the currently selected customer.
 * - $card_title (string): The title to display for this card.
 * - $toner_data (array): An associative array with toner percentages for each color.
 * Example: ['black' => 75, 'cyan' => 60, 'magenta' => 40, 'yellow' => 20]
 * - $low_threshold (int): The percentage below which a toner level is considered "low".
 *
 * Debugging Philosophy:
 * Log the received toner data and threshold to ensure correct values are used for rendering.
 */

// Ensure variables are defined, providing defaults for robustness.
$customer_id = $customer_id ?? 'N/A';
$card_title = $card_title ?? 'Toner Levels Overview';
$toner_data = $toner_data ?? [
    'black' => 0,
    'cyan' => 0,
    'magenta' => 0,
    'yellow' => 0
];
$low_threshold = $low_threshold ?? 20; // Default low threshold

debug_log("Rendering Toner Levels Card. Title: '$card_title', Customer ID: $customer_id", 'DEBUG');
debug_log("Toner Data: " . json_encode($toner_data) . ", Low Threshold: $low_threshold%", 'DEBUG');

?>
<div class="card toner-levels-card">
    <h3><?php echo sanitize_html($card_title); ?></h3>
    <?php if ($customer_id != 'N/A'): ?>
        <p class="card-subtitle">For Customer ID: <strong><?php echo sanitize_html($customer_id); ?></strong></p>
    <?php else: ?>
        <p class="card-subtitle">No customer selected.</p>
    <?php endif; ?>

    <div class="toner-bars">
        <?php foreach ($toner_data as $color => $level):
            $level = sanitize_int($level); // Ensure level is an integer
            $level_class = '';
            if ($level <= $low_threshold) {
                $level_class = 'low-toner';
                debug_log("Toner for $color is low: $level%", 'WARNING');
            }
            ?>
            <div class="toner-item">
                <span class="toner-color-label <?php echo sanitize_html($color); ?>"><?php echo sanitize_html(ucfirst($color)); ?></span>
                <div class="toner-bar-container">
                    <div class="toner-bar <?php echo sanitize_html($level_class); ?>" style="width: <?php echo $level; ?>%;"></div>
                    <span class="toner-percentage"><?php echo $level; ?>%</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card-actions">
        <a href="#" class="button small-button order-toner-button" title="Order new toner cartridges">Order Toner</a>
    </div>

    <?php debug_log("Finished rendering Toner Levels Card.", 'DEBUG'); ?>
</div>

<style>
/* Card-specific styles for Toner Levels Card */
.toner-levels-card {
    /* Specific overrides if needed */
}

.toner-bars {
    margin-top: 15px;
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.toner-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.toner-color-label {
    width: 60px; /* Fixed width for labels */
    font-weight: bold;
    text-align: right;
    color: var(--text-secondary);
    text-transform: uppercase;
    font-size: 0.85rem;
}

/* Specific color labels */
.toner-color-label.black { color: #333; }
.toner-color-label.cyan { color: var(--highlight-cyan); }
.toner-color-label.magenta { color: var(--highlight-magenta); }
.toner-color-label.yellow { color: var(--highlight-yellow); }

.toner-bar-container {
    flex-grow: 1; /* Take remaining width */
    height: 20px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.toner-bar {
    height: 100%;
    background-color: limegreen; /* Default for healthy levels */
    border-radius: 10px;
    transition: width 0.5s ease-out, background-color 0.3s ease;
    position: relative;
    z-index: 1;
}

.toner-bar.low-toner {
    background-color: var(--highlight-magenta); /* Highlight low toner */
    box-shadow: 0 0 8px rgba(var(--highlight-magenta), 0.5); /* Glow for low toner */
}

.toner-bar-container .toner-percentage {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-primary);
    font-size: 0.8rem;
    font-weight: bold;
    z-index: 2; /* Ensure percentage is above the bar */
    text-shadow: 0 0 2px rgba(0,0,0,0.5); /* readability */
}

.toner-levels-card .card-actions {
    margin-top: auto; /* Push actions to the bottom */
    text-align: right;
}

.toner-levels-card .order-toner-button {
    background: linear-gradient(45deg, var(--highlight-yellow), var(--highlight-key));
    color: var(--bg-primary-dark);
}

.toner-levels-card .order-toner-button:hover {
    background: linear-gradient(45deg, var(--highlight-key), var(--highlight-yellow));
    color: var(--highlight-yellow);
}
</style>