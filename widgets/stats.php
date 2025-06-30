<?php
// widgets/stats.php

// Widget configuration
$_widget_config = [
    'name' => 'Sales & Revenue',
    'icon' => 'chart-line', // Font Awesome icon for chart-line
    'width' => 2, // Default size
    'height' => 1
];

// Widget content rendering
if (!function_exists('render_stats_widget')) {
    function render_stats_widget() {
        // Sample data for demonstration
        $total_sales = '$12,345';
        $new_users = '2,567';
        $orders_processed = '8,901';
        $page_views = '150,234';
        $conversion_rate = '3.5%';
        $average_order_value = '$87.50';

        // Compact View Content
        echo '<div class="compact-content">';
        echo '<div class="stats-grid">';
        echo '<div class="stat-card">';
        echo '<i class="fas fa-money-bill-wave"></i>';
        echo '<div class="stat-value">' . htmlspecialchars($total_sales) . '</div>';
        echo '<div class="stat-label">Total Sales</div>';
        echo '</div>';
        echo '<div class="stat-card">';
        echo '<i class="fas fa-users"></i>';
        echo '<div class="stat-value">' . htmlspecialchars($new_users) . '</div>';
        echo '<div class="stat-label">New Users</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>'; // End compact-content

        // Expanded View Content
        echo '<div class="expanded-content" style="padding-top: 10px;">';
        echo '<h4 style="color: var(--accent); margin-bottom: 15px;">Detailed Sales Overview</h4>';
        echo '<div class="stats-grid">'; // Reusing stats-grid for 2-column layout

        echo '<div class="stat-card">';
        echo '<i class="fas fa-shopping-cart"></i>';
        echo '<div class="stat-value">' . htmlspecialchars($orders_processed) . '</div>';
        echo '<div class="stat-label">Orders Processed</div>';
        echo '</div>';

        echo '<div class="stat-card">';
        echo '<i class="fas fa-eye"></i>';
        echo '<div class="stat-value">' . htmlspecialchars($page_views) . '</div>';
        echo '<div class="stat-label">Page Views</div>';
        echo '</div>';

        echo '<div class="stat-card">';
        echo '<i class="fas fa-percent"></i>';
        echo '<div class="stat-value">' . htmlspecialchars($conversion_rate) . '</div>';
        echo '<div class="stat-label">Conversion Rate</div>';
        echo '</div>';

        echo '<div class="stat-card">';
        echo '<i class="fas fa-dollar-sign"></i>';
        echo '<div class="stat-value">' . htmlspecialchars($average_order_value) . '</div>';
        echo '<div class="stat-label">Avg. Order Value</div>';
        echo '</div>';

        echo '</div>'; // End stats-grid
        echo '</div>'; // End expanded-content
    }
}

return 'render_stats_widget';
