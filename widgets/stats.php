<?php
// widgets/stats.php

// Widget Name: Sales & Revenue
// Widget Icon: fas fa-chart-line
// Widget Width: 2.0
// Widget Height: 1.0
?>
<div class="compact-content">
    <div class="neomorphic-card p-4 text-center h-full flex flex-col justify-center">
        <h4 class="text-xl font-bold text-[var(--accent)] mb-2">Total Sales</h4>
        <p class="text-4xl font-bold text-[var(--text-primary)]">$12,345</p>
        <p class="text-sm text-[var(--text-secondary)]">+5% from last month</p>
    </div>
</div>
<div class="expanded-content">
    <div class="neomorphic-card p-4 h-full flex flex-col">
        <h4 class="text-xl font-bold text-[var(--accent)] mb-4">Detailed Sales & Revenue</h4>
        <p class="text-sm text-[var(--text-secondary)] mb-2">This section would display a more detailed chart or table of sales data.</p>
        <p class="text-sm text-[var(--text-secondary)] mb-4">Example: Monthly sales trends, top-selling products, revenue breakdown.</p>
        <canvas id="salesChart" class="flex-grow"></canvas>
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
</div>
