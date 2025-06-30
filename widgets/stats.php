<?php
// widgets/stats.php

// Widget configuration
$_widget_config = [
    'name' => 'Sales & Revenue',
    'icon' => 'chart-line',
    'width' => 2,
    'height' => 1
];

// Sample data for demonstration
$total_sales = '$12,345';
$new_users = '2,567';
$orders_processed = '8,901';
$page_views = '150,234';
$conversion_rate = '3.5%';
$average_order_value = '$87.50';
?>
<div class="compact-content">
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-money-bill-wave"></i>
            <div class="stat-value"><?= htmlspecialchars($total_sales) ?></div>
            <div class="stat-label">Total Sales</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <div class="stat-value"><?= htmlspecialchars($new_users) ?></div>
            <div class="stat-label">New Users</div>
        </div>
    </div>
</div>
<div class="expanded-content" style="padding-top: 10px;">
    <h4 style="color: var(--accent); margin-bottom: 15px;">Detailed Sales Overview</h4>
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-shopping-cart"></i>
            <div class="stat-value"><?= htmlspecialchars($orders_processed) ?></div>
            <div class="stat-label">Orders Processed</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-eye"></i>
            <div class="stat-value"><?= htmlspecialchars($page_views) ?></div>
            <div class="stat-label">Page Views</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-percent"></i>
            <div class="stat-value"><?= htmlspecialchars($conversion_rate) ?></div>
            <div class="stat-label">Conversion Rate</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-dollar-sign"></i>
            <div class="stat-value"><?= htmlspecialchars($average_order_value) ?></div>
            <div class="stat-label">Avg. Order Value</div>
        </div>
    </div>
</div>
