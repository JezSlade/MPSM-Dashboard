/**
 * Device Aging Dashboard JavaScript
 * Handles data fetching, Chart.js rendering, and table management
 */

const aging = {
    data: null,
    charts: {},
    filters: {
        search: '',
        ageFilter: 'all'
    }
};

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    console.log('[DeviceAging] Initializing...');
    setupEventListeners();
    loadDeviceAgeData();
});

function setupEventListeners() {
    // Search and filters
    const searchInput = document.getElementById('customer-search');
    const ageFilter = document.getElementById('age-filter');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            aging.filters.search = e.target.value.toLowerCase();
            renderCustomersTable();
        });
    }

    if (ageFilter) {
        ageFilter.addEventListener('change', (e) => {
            aging.filters.ageFilter = e.target.value;
            renderCustomersTable();
        });
    }

    // Refresh button
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => loadDeviceAgeData(true));
    }
}

async function loadDeviceAgeData(forceRefresh = false) {
    console.log(`[DeviceAging] Loading data (force=${forceRefresh})...`);

    try {
        const url = `api/device-age-report.php${forceRefresh ? '?force=1' : ''}`;
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error || 'API returned unsuccessful response');
        }

        console.log('[DeviceAging] Data loaded:', result);
        aging.data = result;

        // Render all components
        renderSummaryCards();
        renderCharts();
        renderCustomersTable();
        updateAlertBanner();

        showToast('Data loaded successfully', 'success');

    } catch (error) {
        console.error('[DeviceAging] Load error:', error);
        showToast(`Failed to load data: ${error.message}`, 'error');

        // Show error state
        document.getElementById('summary-cards').innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>Failed to load device age data</p>
                <button class="btn-refresh" onclick="loadDeviceAgeData(true)">
                    <i class="fas fa-sync-alt"></i> Retry
                </button>
            </div>
        `;
    }
}

function renderSummaryCards() {
    const summary = aging.data?.summary;
    if (!summary) return;

    const container = document.getElementById('summary-cards');

    const avgAgeColor = getAgeColor(summary.avgAgeYears);
    const coveragePercent = summary.totalDevices > 0
        ? ((summary.withAge / summary.totalDevices) * 100).toFixed(1)
        : 0;

    container.innerHTML = `
        <div class="metric-card">
            <div class="metric-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                <i class="fas fa-server"></i>
            </div>
            <div class="metric-content">
                <h3 class="metric-value">${summary.totalDevices.toLocaleString()}</h3>
                <p class="metric-label">Total Devices</p>
                <p class="metric-sublabel">${summary.withAge.toLocaleString()} with age data (${coveragePercent}%)</p>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: linear-gradient(135deg, ${avgAgeColor}, ${avgAgeColor}dd);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="metric-content">
                <h3 class="metric-value">${summary.avgAgeYears} <span style="font-size: 0.6em;">years</span></h3>
                <p class="metric-label">Average Age</p>
                <p class="metric-sublabel">Fleet average (devices with age data)</p>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="metric-content">
                <h3 class="metric-value">${summary.buckets.over5.toLocaleString()}</h3>
                <p class="metric-label">Critical Age (5+ years)</p>
                <p class="metric-sublabel">${getPercentage(summary.buckets.over5, summary.totalDevices)}% of fleet</p>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: linear-gradient(135deg, #f39c12, #d68910);">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="metric-content">
                <h3 class="metric-value">${summary.buckets.threeTo5.toLocaleString()}</h3>
                <p class="metric-label">Warning Age (3-5 years)</p>
                <p class="metric-sublabel">${getPercentage(summary.buckets.threeTo5, summary.totalDevices)}% of fleet</p>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: linear-gradient(135deg, #27ae60, #229954);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="metric-content">
                <h3 class="metric-value">${(summary.buckets.under1 + summary.buckets.oneTo3).toLocaleString()}</h3>
                <p class="metric-label">Healthy Fleet (<3 years)</p>
                <p class="metric-sublabel">${getPercentage(summary.buckets.under1 + summary.buckets.oneTo3, summary.totalDevices)}% of fleet</p>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: linear-gradient(135deg, #95a5a6, #7f8c8d);">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="metric-content">
                <h3 class="metric-value">${summary.buckets.unknown.toLocaleString()}</h3>
                <p class="metric-label">Unknown Age</p>
                <p class="metric-sublabel">${getPercentage(summary.buckets.unknown, summary.totalDevices)}% cannot decode</p>
            </div>
        </div>
    `;
}

function renderCharts() {
    const summary = aging.data?.summary;
    const customers = aging.data?.customers || [];

    if (!summary) return;

    // Destroy existing charts
    Object.keys(aging.charts).forEach(key => {
        if (aging.charts[key]) {
            aging.charts[key].destroy();
            aging.charts[key] = null;
        }
    });

    const colors = {
        excellent: '#27ae60',
        good: '#3498db',
        warning: '#f39c12',
        critical: '#e74c3c',
        unknown: '#95a5a6'
    };

    // Chart 1: Age Distribution (Doughnut)
    const ageDistCtx = document.getElementById('age-distribution-chart');
    if (ageDistCtx) {
        aging.charts.ageDistribution = new Chart(ageDistCtx, {
            type: 'doughnut',
            data: {
                labels: ['<1 Year', '1-3 Years', '3-5 Years', '5+ Years', 'Unknown'],
                datasets: [{
                    data: [
                        summary.buckets.under1 || 0,
                        summary.buckets.oneTo3 || 0,
                        summary.buckets.threeTo5 || 0,
                        summary.buckets.over5 || 0,
                        summary.buckets.unknown || 0
                    ],
                    backgroundColor: [
                        colors.excellent,
                        colors.good,
                        colors.warning,
                        colors.critical,
                        colors.unknown
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} devices (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Chart 2: Top 10 Customers by Average Age (Bar)
    const customerAgeCtx = document.getElementById('customer-age-chart');
    if (customerAgeCtx && customers.length > 0) {
        const sorted = [...customers]
            .filter(c => c.avgAgeYears > 0)
            .sort((a, b) => b.avgAgeYears - a.avgAgeYears)
            .slice(0, 10);

        aging.charts.customerAge = new Chart(customerAgeCtx, {
            type: 'bar',
            data: {
                labels: sorted.map(c => c.name.substring(0, 20)),
                datasets: [{
                    label: 'Average Age (years)',
                    data: sorted.map(c => c.avgAgeYears),
                    backgroundColor: sorted.map(c => getAgeColor(c.avgAgeYears)),
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.parsed.x} years`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Average Age (years)'
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Chart 3: Unknown Reasons (Pie)
    const unknownReasonsCtx = document.getElementById('unknown-reasons-chart');
    if (unknownReasonsCtx && summary.unknownReasons) {
        const reasons = Object.entries(summary.unknownReasons);

        if (reasons.length > 0) {
            aging.charts.unknownReasons = new Chart(unknownReasonsCtx, {
                type: 'pie',
                data: {
                    labels: reasons.map(([key]) => formatReason(key)),
                    datasets: [{
                        data: reasons.map(([, value]) => value),
                        backgroundColor: [
                            '#e74c3c',
                            '#f39c12',
                            '#3498db',
                            '#9b59b6',
                            '#95a5a6'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                font: { size: 10 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} devices (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        } else {
            unknownReasonsCtx.parentElement.innerHTML = '<p class="text-muted" style="padding: 2rem; text-align: center;">All devices have age data</p>';
        }
    }

    // Chart 4: Fleet Health Assessment (Radar)
    const healthCtx = document.getElementById('health-assessment-chart');
    if (healthCtx) {
        const total = summary.totalDevices || 1;

        aging.charts.health = new Chart(healthCtx, {
            type: 'radar',
            data: {
                labels: ['New (<1yr)', 'Young (1-3yr)', 'Mature (3-5yr)', 'Old (5+yr)', 'Known Age'],
                datasets: [{
                    label: 'Fleet Profile',
                    data: [
                        ((summary.buckets.under1 / total) * 100).toFixed(1),
                        ((summary.buckets.oneTo3 / total) * 100).toFixed(1),
                        ((summary.buckets.threeTo5 / total) * 100).toFixed(1),
                        ((summary.buckets.over5 / total) * 100).toFixed(1),
                        ((summary.withAge / total) * 100).toFixed(1)
                    ],
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderColor: '#3498db',
                    borderWidth: 2,
                    pointBackgroundColor: '#3498db',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#3498db'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    r: {
                        angleLines: { display: true },
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.parsed.r}% of fleet`;
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
}

function renderCustomersTable() {
    const customers = aging.data?.customers || [];
    const container = document.getElementById('customers-table-container');

    if (customers.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No customer data available</p>
            </div>
        `;
        return;
    }

    // Apply filters
    let filtered = customers.filter(customer => {
        // Search filter
        if (aging.filters.search) {
            const searchLower = aging.filters.search;
            const matchesSearch =
                customer.name.toLowerCase().includes(searchLower) ||
                customer.code.toLowerCase().includes(searchLower);
            if (!matchesSearch) return false;
        }

        // Age filter
        if (aging.filters.ageFilter !== 'all') {
            const avgAge = customer.avgAgeYears || 0;
            switch (aging.filters.ageFilter) {
                case 'critical':
                    if (avgAge < 5) return false;
                    break;
                case 'warning':
                    if (avgAge < 3 || avgAge >= 5) return false;
                    break;
                case 'good':
                    if (avgAge < 1 || avgAge >= 3) return false;
                    break;
                case 'excellent':
                    if (avgAge >= 1) return false;
                    break;
            }
        }

        return true;
    });

    // Sort by average age (descending)
    filtered.sort((a, b) => (b.avgAgeYears || 0) - (a.avgAgeYears || 0));

    container.innerHTML = `
        <div class="customers-table-wrapper">
            <table class="customers-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Total Devices</th>
                        <th>Avg Age</th>
                        <th><1yr</th>
                        <th>1-3yr</th>
                        <th>3-5yr</th>
                        <th>5+yr</th>
                        <th>Unknown</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${filtered.map(customer => `
                        <tr>
                            <td>
                                <strong>${escapeHtml(customer.name)}</strong><br>
                                <span class="text-muted" style="font-size: 0.875rem;">${escapeHtml(customer.code)}</span>
                            </td>
                            <td>${customer.totalDevices}</td>
                            <td>
                                <span class="age-badge" style="background-color: ${getAgeColor(customer.avgAgeYears)};">
                                    ${customer.avgAgeYears} years
                                </span>
                            </td>
                            <td>${customer.buckets.under1}</td>
                            <td>${customer.buckets.oneTo3}</td>
                            <td>${customer.buckets.threeTo5}</td>
                            <td>${customer.buckets.over5}</td>
                            <td>${customer.buckets.unknown}</td>
                            <td>${getAgeStatusBadge(customer.avgAgeYears)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            Showing ${filtered.length} of ${customers.length} customers
        </div>
    `;
}

function updateAlertBanner() {
    const summary = aging.data?.summary;
    if (!summary) return;

    const banner = document.getElementById('alert-banner');
    const total = summary.totalDevices || 1;
    const criticalPercent = (summary.buckets.over5 / total) * 100;
    const warningPercent = (summary.buckets.threeTo5 / total) * 100;

    if (criticalPercent >= 30) {
        banner.className = 'alert-banner alert-critical';
        banner.querySelector('strong').textContent = 'Critical Aging Fleet';
        banner.querySelector('p').textContent = `${criticalPercent.toFixed(1)}% of your fleet is 5+ years old and should be replaced urgently.`;
        banner.style.display = 'flex';
    } else if (criticalPercent >= 15 || warningPercent >= 40) {
        banner.className = 'alert-banner alert-high';
        banner.querySelector('strong').textContent = 'Aging Fleet Detected';
        banner.querySelector('p').textContent = 'A significant portion of your fleet is approaching or exceeding recommended replacement age.';
        banner.style.display = 'flex';
    } else if (warningPercent >= 20) {
        banner.className = 'alert-banner alert-medium';
        banner.querySelector('strong').textContent = 'Fleet Aging Notice';
        banner.querySelector('p').textContent = 'Monitor devices in the 3-5 year range for potential replacement planning.';
        banner.style.display = 'flex';
    } else {
        banner.style.display = 'none';
    }
}

// Helper Functions

function getPercentage(value, total) {
    if (!total || total === 0) return '0.0';
    return ((value / total) * 100).toFixed(1);
}

function getAgeColor(ageYears) {
    if (!ageYears || ageYears < 1) return '#27ae60';  // Green
    if (ageYears < 3) return '#3498db';               // Blue
    if (ageYears < 5) return '#f39c12';               // Orange
    return '#e74c3c';                                 // Red
}

function getAgeStatusBadge(ageYears) {
    if (!ageYears || ageYears < 1) {
        return '<span class="status-badge status-excellent">Excellent</span>';
    } else if (ageYears < 3) {
        return '<span class="status-badge status-good">Good</span>';
    } else if (ageYears < 5) {
        return '<span class="status-badge status-warning">Warning</span>';
    } else {
        return '<span class="status-badge status-critical">Critical</span>';
    }
}

function formatReason(reason) {
    const map = {
        'missing_serial': 'Missing Serial',
        'missing_manufacturer': 'Missing Manufacturer',
        'unsupported_manufacturer': 'Unsupported Brand',
        'decode_failed': 'Decode Failed',
        'undetermined': 'Undetermined'
    };
    return map[reason] || reason;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${escapeHtml(message)}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/*
CHANGELOG
2025-12-03 Claude
- Initial implementation: Device aging dashboard JavaScript
- Fetches from device-age-report.php API
- Renders 4 Chart.js visualizations (doughnut, bar, pie, radar)
- Dynamic customer table with search and age filtering
- Alert banner based on fleet health thresholds
- Toast notifications for user feedback
*/
