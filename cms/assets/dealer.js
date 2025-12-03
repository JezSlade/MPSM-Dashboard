/**
 * Dealer Dashboard JavaScript
 * Handles data fetching, rendering, filtering, and sorting for dealer view
 */

// Global state
const dealerState = {
    summary: null,
    customers: null,
    loading: false,
    filters: {
        searchTerm: '',
        healthFilter: 'all'
    },
    sort: {
        column: 'healthScore',
        direction: 'asc' // Lowest health score first = needs attention
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initializeDealerDashboard();
});

async function initializeDealerDashboard() {
    console.log('[Dealer] Initializing dashboard...');

    // Set up event listeners
    setupEventListeners();

    // Load dashboard data
    await loadDealerDashboard();
}

function setupEventListeners() {
    // Theme toggle (reuse from existing app.js)
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    // Refresh button
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => loadDealerDashboard(true));
    }

    // Logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }

    // Portfolio search
    const portfolioSearch = document.getElementById('portfolio-search');
    if (portfolioSearch) {
        portfolioSearch.addEventListener('input', (e) => {
            dealerState.filters.searchTerm = e.target.value;
            renderPortfolioTable(dealerState.customers);
        });
    }

    // Portfolio filter
    const portfolioFilter = document.getElementById('portfolio-filter');
    if (portfolioFilter) {
        portfolioFilter.addEventListener('change', (e) => {
            dealerState.filters.healthFilter = e.target.value;
            renderPortfolioTable(dealerState.customers);
        });
    }
}

async function loadDealerDashboard(forceRefresh = false) {
    if (dealerState.loading) {
        console.log('[Dealer] Already loading, skipping...');
        return;
    }

    dealerState.loading = true;

    try {
        console.log('[Dealer] Loading dashboard data...');

        // Show loading states
        showLoading('dealer-scorecard');
        showLoading('portfolio-table-container');

        // Fetch summary and portfolio in parallel
        const forceParam = forceRefresh ? '?force=1' : '';
        const [summaryResp, portfolioResp] = await Promise.all([
            fetchJson(`api/get-dealer-summary.php${forceParam}`),
            fetchJson(`api/get-customer-portfolio.php${forceParam}`)
        ]);

        dealerState.summary = summaryResp.summary;
        dealerState.customers = portfolioResp.customers;

        console.log('[Dealer] Data loaded:', {
            totalCustomers: dealerState.customers.length,
            totalDevices: dealerState.summary.totalDevices,
            cached: summaryResp.cached
        });

        // Render all sections
        renderScorecard(dealerState.summary);
        renderCharts(dealerState.summary);
        renderPortfolioTable(dealerState.customers);

        // Show cache age toast if not fresh
        if (summaryResp.cached && summaryResp.cache_age_seconds > 300) {
            const minutes = Math.round(summaryResp.cache_age_seconds / 60);
            showToast(`Data is ${minutes} minutes old. Click refresh for latest.`, 'info');
        }

    } catch (error) {
        console.error('[Dealer] Load failed:', error);
        showToast('Failed to load dealer dashboard', 'error');
        showError('dealer-scorecard', 'Failed to load metrics');
        showError('portfolio-table-container', 'Failed to load customer portfolio');
    } finally {
        dealerState.loading = false;
    }
}

function renderScorecard(summary) {
    const container = document.getElementById('dealer-scorecard');
    if (!container) return;

    // Calculate derived metrics
    const offlineRate = summary.totalDevices > 0
        ? (summary.offlineDevices / summary.totalDevices * 100).toFixed(1)
        : 0;

    const ghostRate = summary.totalDevices > 0
        ? (summary.ghostDevices7d / summary.totalDevices * 100).toFixed(1)
        : 0;

    const fleetOver5yr = summary.fleetAgeDistribution.over5yr;
    const fleetAgeRate = summary.totalDevices > 0
        ? (fleetOver5yr / summary.totalDevices * 100).toFixed(0)
        : 0;

    container.innerHTML = `
        <!-- Dealership Overview Banner -->
        <div class="dealership-banner">
            <div class="banner-content">
                <i class="fas fa-chart-line"></i>
                <div class="banner-text">
                    <h3>Dealership Overview - All ${summary.totalCustomers} Customers</h3>
                    <p>Aggregated metrics across ${summary.totalDevices} managed devices</p>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="metric-card status-neutral">
            <div class="metric-icon"><i class="fas fa-building"></i></div>
            <div class="metric-value">${summary.totalCustomers.toLocaleString()}</div>
            <div class="metric-label">Active Customers</div>
        </div>

        <!-- Total Devices -->
        <div class="metric-card status-neutral">
            <div class="metric-icon"><i class="fas fa-print"></i></div>
            <div class="metric-value">${summary.totalDevices.toLocaleString()}</div>
            <div class="metric-label">Managed Devices</div>
            <div class="metric-subtitle">${summary.devicesByStatus.online.toLocaleString()} online</div>
        </div>

        <!-- Ghost Devices -->
        <div class="metric-card ${ghostRate > 5 ? 'status-danger' : ghostRate > 2 ? 'status-warning' : 'status-success'}">
            <div class="metric-icon"><i class="fas fa-ghost"></i></div>
            <div class="metric-value">${summary.ghostDevices7d.toLocaleString()}</div>
            <div class="metric-label">Ghost Devices</div>
            <div class="metric-subtitle">No contact in 7+ days (${ghostRate}%)</div>
        </div>

        <!-- Active Alerts -->
        <div class="metric-card ${summary.totalAlerts > 1000 ? 'status-danger' : summary.totalAlerts > 500 ? 'status-warning' : 'status-success'}">
            <div class="metric-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="metric-value">${summary.totalAlerts.toLocaleString()}</div>
            <div class="metric-label">Active Alerts</div>
            <div class="metric-subtitle">Requires attention</div>
        </div>

        <!-- Connector Health -->
        <div class="metric-card ${summary.connectorHealthScore >= 95 ? 'status-success' : summary.connectorHealthScore >= 85 ? 'status-warning' : 'status-danger'}">
            <div class="metric-icon"><i class="fas fa-server"></i></div>
            <div class="metric-value">${summary.connectorHealthScore}%</div>
            <div class="metric-label">Connector Health</div>
            <div class="metric-subtitle">${summary.connectorsOffline} of ${summary.totalConnectors} offline</div>
        </div>

        <!-- Duplicate IPs -->
        <div class="metric-card ${summary.duplicateIPs === 0 ? 'status-success' : summary.duplicateIPs > 20 ? 'status-danger' : 'status-warning'}">
            <div class="metric-icon"><i class="fas fa-network-wired"></i></div>
            <div class="metric-value">${summary.duplicateIPs}</div>
            <div class="metric-label">Duplicate IPs</div>
            <div class="metric-subtitle">${summary.duplicateIPs === 0 ? 'All clear' : 'Needs resolution'}</div>
        </div>

        <!-- Fleet Age -->
        <div class="metric-card ${fleetAgeRate < 15 ? 'status-success' : fleetAgeRate < 25 ? 'status-warning' : 'status-danger'}">
            <div class="metric-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="metric-value">${fleetAgeRate}%</div>
            <div class="metric-label">Fleet Age (5yr+)</div>
            <div class="metric-subtitle">${fleetOver5yr} devices aging out</div>
        </div>

        <!-- Panel Messages -->
        <div class="metric-card ${summary.panelMessagesLast24h > 100 ? 'status-warning' : 'status-success'}">
            <div class="metric-icon"><i class="fas fa-bell"></i></div>
            <div class="metric-value">${summary.panelMessagesLast24h.toLocaleString()}</div>
            <div class="metric-label">Panel Errors (24h)</div>
            <div class="metric-subtitle">${summary.problemDevices} problem devices</div>
        </div>

        <!-- Uninstalled Devices -->
        <div class="metric-card status-neutral">
            <div class="metric-icon"><i class="fas fa-trash-alt"></i></div>
            <div class="metric-value">${summary.uninstalledDevices.toLocaleString()}</div>
            <div class="metric-label">Uninstalled Devices</div>
            <div class="metric-subtitle">Removed from service</div>
        </div>
    `;
}

// Chart instances - store globally to destroy on re-render
let chartInstances = {
    fleetAge: null,
    deviceStatus: null,
    quality: null,
    connector: null
};

function renderCharts(summary) {
    console.log('[Charts] Rendering visualizations...');

    // Destroy existing charts to prevent memory leaks
    Object.keys(chartInstances).forEach(key => {
        if (chartInstances[key]) {
            chartInstances[key].destroy();
            chartInstances[key] = null;
        }
    });

    // Color scheme
    const colors = {
        success: '#27ae60',
        warning: '#f39c12',
        danger: '#e74c3c',
        info: '#3498db',
        neutral: '#95a5a6',
        primary: '#2c3e50'
    };

    // Chart 1: Fleet Age Distribution (Doughnut)
    const fleetAgeCtx = document.getElementById('fleet-age-chart');
    if (fleetAgeCtx) {
        const fleetData = summary.fleetAgeDistribution || {};
        chartInstances.fleetAge = new Chart(fleetAgeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Under 1 Year', '1-3 Years', '3-5 Years', 'Over 5 Years', 'Unknown'],
                datasets: [{
                    data: [
                        fleetData.under1yr || 0,
                        fleetData.age1to3yr || 0,
                        fleetData.age3to5yr || 0,
                        fleetData.over5yr || 0,
                        fleetData.unknown || 0
                    ],
                    backgroundColor: [
                        colors.success,
                        colors.info,
                        colors.warning,
                        colors.danger,
                        colors.neutral
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

    // Chart 2: Device Health Status (Doughnut)
    const deviceStatusCtx = document.getElementById('device-status-chart');
    if (deviceStatusCtx) {
        const statusData = summary.devicesByStatus || {};
        const online = statusData.online || 0;
        const offline = statusData.offline || 0;
        const ghost = summary.ghostDevices7d || 0;
        const healthy = Math.max(0, online - ghost);

        chartInstances.deviceStatus = new Chart(deviceStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Healthy', 'Ghost (7d)', 'Offline'],
                datasets: [{
                    data: [healthy, ghost, offline],
                    backgroundColor: [
                        colors.success,
                        colors.warning,
                        colors.danger
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

    // Chart 3: Data Quality Metrics (Horizontal Bar)
    const qualityCtx = document.getElementById('quality-metrics-chart');
    if (qualityCtx) {
        chartInstances.quality = new Chart(qualityCtx, {
            type: 'bar',
            data: {
                labels: ['Duplicate IPs', 'Ghost Devices', 'Panel Errors (24h)', 'Uninstalled'],
                datasets: [{
                    label: 'Count',
                    data: [
                        summary.duplicateIPs || 0,
                        summary.ghostDevices7d || 0,
                        summary.panelMessagesLast24h || 0,
                        summary.uninstalledDevices || 0
                    ],
                    backgroundColor: [
                        summary.duplicateIPs > 0 ? colors.danger : colors.success,
                        summary.ghostDevices7d > 0 ? colors.warning : colors.success,
                        summary.panelMessagesLast24h > 100 ? colors.danger : colors.warning,
                        colors.neutral
                    ],
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.parsed.x} issues`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            font: { size: 10 }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 11 }
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

    // Chart 4: Connector Health (Bar)
    const connectorCtx = document.getElementById('connector-health-chart');
    if (connectorCtx) {
        const activeConnectors = Math.max(0, (summary.totalConnectors || 0) - (summary.connectorsOffline || 0));
        const offlineConnectors = summary.connectorsOffline || 0;

        chartInstances.connector = new Chart(connectorCtx, {
            type: 'bar',
            data: {
                labels: ['Active', 'Offline', 'Health Score'],
                datasets: [{
                    label: 'Connectors',
                    data: [
                        activeConnectors,
                        offlineConnectors,
                        summary.connectorHealthScore || 0
                    ],
                    backgroundColor: [
                        colors.success,
                        colors.danger,
                        summary.connectorHealthScore >= 95 ? colors.success :
                        summary.connectorHealthScore >= 85 ? colors.warning : colors.danger
                    ],
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label;
                                const value = context.parsed.y;
                                if (label === 'Health Score') {
                                    return `Health Score: ${value}%`;
                                }
                                return `${label}: ${value} connectors`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            display: true,
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            font: { size: 10 }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 11 }
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

    console.log('[Charts] Rendered successfully');
}

function renderPortfolioTable(customers) {
    const container = document.getElementById('portfolio-table-container');
    if (!container || !customers) return;

    // Apply filters
    let filtered = customers.filter(c => {
        const matchesSearch = !dealerState.filters.searchTerm
            || c.name.toLowerCase().includes(dealerState.filters.searchTerm.toLowerCase())
            || c.code.toLowerCase().includes(dealerState.filters.searchTerm.toLowerCase());

        const matchesHealth = dealerState.filters.healthFilter === 'all'
            || (dealerState.filters.healthFilter === 'healthy' && c.healthScore >= 90)
            || (dealerState.filters.healthFilter === 'attention' && c.healthScore >= 70 && c.healthScore < 90)
            || (dealerState.filters.healthFilter === 'critical' && c.healthScore < 70);

        return matchesSearch && matchesHealth;
    });

    // Apply sorting
    const { column, direction } = dealerState.sort;
    filtered.sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];

        // Handle string columns
        if (column === 'name' || column === 'code') {
            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
            return direction === 'asc'
                ? aVal.localeCompare(bVal)
                : bVal.localeCompare(aVal);
        }

        // Handle numeric columns
        return direction === 'asc' ? aVal - bVal : bVal - aVal;
    });

    container.innerHTML = `
        <table class="portfolio-table">
            <thead>
                <tr>
                    <th onclick="sortPortfolio('name')">
                        Customer Name ${getSortIcon('name')}
                    </th>
                    <th onclick="sortPortfolio('totalDevices')">
                        Devices ${getSortIcon('totalDevices')}
                    </th>
                    <th onclick="sortPortfolio('offlineDevices')">
                        Offline ${getSortIcon('offlineDevices')}
                    </th>
                    <th onclick="sortPortfolio('ghostDevices')">
                        Ghost ${getSortIcon('ghostDevices')}
                    </th>
                    <th onclick="sortPortfolio('alertCount')">
                        Alerts ${getSortIcon('alertCount')}
                    </th>
                    <th onclick="sortPortfolio('connectorCount')">
                        Connectors ${getSortIcon('connectorCount')}
                    </th>
                    <th onclick="sortPortfolio('healthScore')">
                        Health ${getSortIcon('healthScore')}
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${filtered.length === 0 ? `
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                            No customers match the current filters
                        </td>
                    </tr>
                ` : filtered.map(customer => `
                    <tr>
                        <td class="customer-name">
                            <strong>${escapeHtml(customer.name)}</strong><br>
                            <small class="customer-code">${escapeHtml(customer.code)}</small>
                        </td>
                        <td>${customer.totalDevices.toLocaleString()}</td>
                        <td class="${customer.offlineDevices > 0 ? 'text-warning' : ''}">${customer.offlineDevices}</td>
                        <td class="${customer.ghostDevices > 0 ? 'text-danger' : ''}">${customer.ghostDevices}</td>
                        <td class="${customer.alertCount > 0 ? 'text-warning' : ''}">${customer.alertCount}</td>
                        <td>
                            ${customer.connectorsActive}/${customer.connectorCount}
                            ${customer.connectorsOffline > 0 ? `<span class="text-danger"> (${customer.connectorsOffline} offline)</span>` : ''}
                        </td>
                        <td>
                            <div class="health-score health-${customer.healthStatus}">
                                ${customer.healthScore}%
                            </div>
                        </td>
                        <td>
                            <button onclick="drillDownToCustomer('${escapeHtml(customer.code)}')" class="btn-view">
                                <i class="fas fa-arrow-right"></i> View
                            </button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        <div class="table-footer">
            Showing ${filtered.length} of ${customers.length} customers
            ${dealerState.filters.searchTerm || dealerState.filters.healthFilter !== 'all' ? ' (filtered)' : ''}
        </div>
    `;
}

function sortPortfolio(column) {
    if (dealerState.sort.column === column) {
        // Toggle direction
        dealerState.sort.direction = dealerState.sort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        // New column, default direction
        dealerState.sort.column = column;
        dealerState.sort.direction = column === 'healthScore' || column === 'name' ? 'asc' : 'desc';
    }

    renderPortfolioTable(dealerState.customers);
}

function getSortIcon(column) {
    if (dealerState.sort.column !== column) {
        return '<i class="fas fa-sort"></i>';
    }
    return dealerState.sort.direction === 'asc'
        ? '<i class="fas fa-sort-up"></i>'
        : '<i class="fas fa-sort-down"></i>';
}

async function drillDownToCustomer(customerCode) {
    try {
        // Save customer preference
        await fetchJson('api/save-preference.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ key: 'customerCode', value: customerCode })
        });

        // Redirect to customer dashboard
        window.location.href = 'index.php';
    } catch (error) {
        console.error('[Dealer] Failed to set customer:', error);
        // Redirect anyway with query param
        window.location.href = `index.php?customerCode=${encodeURIComponent(customerCode)}`;
    }
}

// Utility functions
function showLoading(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = `
        <div class="dealer-loading">
            <i class="fas fa-spinner"></i>
            <p>Loading...</p>
        </div>
    `;
}

function showError(containerId, message) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>${escapeHtml(message)}</p>
        </div>
    `;
}

function formatSeconds(seconds) {
    if (seconds < 60) return `${seconds}s`;
    if (seconds < 3600) return `${Math.round(seconds / 60)}m`;
    return `${Math.round(seconds / 3600)}h`;
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

async function fetchJson(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        credentials: 'same-origin'
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();

    if (data.success === false) {
        throw new Error(data.error || 'Request failed');
    }

    return data;
}

// showToast is provided by shared.js - no need to redefine

function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    html.setAttribute('data-theme', newTheme);
    document.body.setAttribute('data-theme', newTheme);

    // Update icon
    const icon = document.querySelector('#theme-toggle i');
    if (icon) {
        icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }

    // Save preference
    fetchJson('api/save-preference.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ key: 'theme', value: newTheme })
    }).catch(err => console.error('Failed to save theme:', err));
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        fetch('api/logout.php', { method: 'POST' })
            .then(() => {
                window.location.href = 'login.html';
            })
            .catch(err => {
                console.error('Logout failed:', err);
                window.location.href = 'login.html';
            });
    }
}

console.log('[Dealer] Script loaded');

/*
CHANGELOG
2025-12-06 Codex
- Rebranded dashboard labels and logs from Executive to Dealer across the JS controller.
- Updated IDs/endpoints to dealer-scorecard/dealer-summary and aligned loading UI classes.
*/
