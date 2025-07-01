<?php
// widgets/stats.php

// Widget configuration
$_widget_config = [
    'name' => 'Sales & Revenue',
    'icon' => 'chart-line',
    'width' => 2,
    'height' => 1
];
?>
<div class="compact-content">
    <div style="text-align: center; padding: 10px;">
        <h4 style="color: var(--accent); margin-bottom: 5px;">Total Sales</h4>
        <p style="font-size: 2em; font-weight: bold; color: var(--text-primary);">$12,345</p>
        <p style="font-size: 0.9em; color: var(--text-secondary);">+5% from last month</p>
    </div>
</div>
<div class="expanded-content">
    <h4 style="color: var(--accent); margin-bottom: 15px;">Detailed Sales & Revenue</h4>
    <p>This section would display a more detailed chart or table of sales data.</p>
    <p>Example: Monthly sales trends, top-selling products, revenue breakdown.</p>
    <canvas id="salesChart" width="400" height="200"></canvas>
    <script>
        // Basic Chart.js example (requires Chart.js library to be loaded globally)
        // For production, consider loading Chart.js via a module or directly in index.php
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Sales',
                            data: [1200, 1900, 3000, 5000, 2300, 3000],
                            backgroundColor: 'rgba(0, 188, 212, 0.6)',
                            borderColor: 'rgba(0, 188, 212, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
    </script>
</div>
