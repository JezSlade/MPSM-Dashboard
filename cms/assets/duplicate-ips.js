/**
 * Duplicate IPs Dashboard JavaScript
 * Handles data fetching, visualization, and filtering for duplicate IP analysis
 */

// Global state
const dupIPState = {
    duplicates: null,
    summary: null,
    loading: false,
    filters: {
        searchTerm: '',
        severityFilter: 'all'
    }
};

// Chart instances
let chartInstances = {
    customers: null,
    status: null,
    health: null
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('[DupIP] Initializing dashboard...');
    setupEventListeners();
    loadDuplicateIPData();
});

function setupEventListeners() {
    // Theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    // Refresh button
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => loadDuplicateIPData(true));
    }

    // Logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }

    // Search
    const ipSearch = document.getElementById('ip-search');
    if (ipSearch) {
        ipSearch.addEventListener('input', (e) => {
            dupIPState.filters.searchTerm = e.target.value;
            renderDuplicatesTable(dupIPState.duplicates);
        });
    }

    // Severity filter
    const severityFilter = document.getElementById('severity-filter');
    if (severityFilter) {
        severityFilter.addEventListener('change', (e) => {
            dupIPState.filters.severityFilter = e.target.value;
            renderDuplicatesTable(dupIPState.duplicates);
        });
    }
}

async function loadDuplicateIPData(forceRefresh = false) {
    if (dupIPState.loading) {
        console.log('[DupIP] Already loading, skipping...');
        return;
    }

    dupIPState.loading = true;

    try {
        console.log('[DupIP] Loading duplicate IP data...');

        // Show loading states
        showLoading('summary-cards');
        showLoading('duplicates-table-container');

        // Fetch data
        const forceParam = forceRefresh ? '?force=1' : '';
        const response = await fetchJson(`api/get-duplicate-ips.php${forceParam}`);

        dupIPState.duplicates = response.duplicates;
        dupIPState.summary = response.summary;

        console.log('[DupIP] Data loaded:', {
            totalDuplicateIPs: dupIPState.summary.totalDuplicateIPs,
            devicesAffected: dupIPState.summary.totalDevicesAffected,
            cached: response.cached
        });

        // Update alert banner
        updateAlertBanner(dupIPState.summary);

        // Render all sections
        renderSummaryCards(dupIPState.summary);
        renderCharts(dupIPState.duplicates, dupIPState.summary);
        renderDuplicatesTable(dupIPState.duplicates);

        // Show cache age toast if not fresh
        if (response.cached && response.cache_age_seconds > 180) {
            const minutes = Math.round(response.cache_age_seconds / 60);
            showToast(`Data is ${minutes} minutes old. Click refresh for latest.`, 'info');
        }

    } catch (error) {
        console.error('[DupIP] Load failed:', error);
        showToast('Failed to load duplicate IP data', 'error');
        showError('summary-cards', 'Failed to load summary');
        showError('duplicates-table-container', 'Failed to load duplicates');
    } finally {
        dupIPState.loading = false;
    }
}

function updateAlertBanner(summary) {
    const banner = document.getElementById('alert-banner');
    if (!banner) return;

    if (summary.totalDuplicateIPs > 0) {
        banner.style.display = 'flex';
        const critical = summary.severityBreakdown.critical || 0;
        const high = summary.severityBreakdown.high || 0;

        if (critical > 0) {
            banner.className = 'alert-banner alert-critical';
            banner.querySelector('strong').textContent = 'Critical Network Issue';
        } else if (high > 0) {
            banner.className = 'alert-banner alert-high';
            banner.querySelector('strong').textContent = 'High Priority Network Issue';
        } else {
            banner.className = 'alert-banner alert-medium';
            banner.querySelector('strong').textContent = 'Network Configuration Issue';
        }
    } else {
        banner.style.display = 'none';
    }
}

function renderSummaryCards(summary) {
    const container = document.getElementById('summary-cards');
    if (!container) return;

    container.innerHTML = `
        <!-- Total Duplicate IPs -->
        <div class="metric-card ${summary.totalDuplicateIPs === 0 ? 'status-success' : summary.totalDuplicateIPs > 10 ? 'status-danger' : 'status-warning'}">
            <div class="metric-icon"><i class="fas fa-network-wired"></i></div>
            <div class="metric-value">${summary.totalDuplicateIPs}</div>
            <div class="metric-label">Duplicate IPs</div>
            <div class="metric-subtitle">${summary.totalDuplicateIPs === 0 ? 'All clear' : 'Require resolution'}</div>
        </div>

        <!-- Total Devices Affected -->
        <div class="metric-card ${summary.totalDevicesAffected === 0 ? 'status-success' : 'status-warning'}">
            <div class="metric-icon"><i class="fas fa-print"></i></div>
            <div class="metric-value">${summary.totalDevicesAffected}</div>
            <div class="metric-label">Devices Affected</div>
            <div class="metric-subtitle">${summary.percentageAffected}% of total devices</div>
        </div>

        <!-- Critical Severity -->
        <div class="metric-card ${summary.severityBreakdown.critical === 0 ? 'status-success' : 'status-danger'}">
            <div class="metric-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="metric-value">${summary.severityBreakdown.critical}</div>
            <div class="metric-label">Critical Issues</div>
            <div class="metric-subtitle">10+ devices per IP</div>
        </div>

        <!-- Customers Affected -->
        <div class="metric-card status-neutral">
            <div class="metric-icon"><i class="fas fa-building"></i></div>
            <div class="metric-value">${summary.customersAffected}</div>
            <div class="metric-label">Customers Affected</div>
            <div class="metric-subtitle">Across dealership</div>
        </div>
    `;
}

function renderCharts(duplicates, summary) {
    console.log('[DupIP] Rendering charts...');

    // Destroy existing charts
    Object.keys(chartInstances).forEach(key => {
        if (chartInstances[key]) {
            chartInstances[key].destroy();
            chartInstances[key] = null;
        }
    });

    const colors = {
        success: '#27ae60',
        warning: '#f39c12',
        danger: '#e74c3c',
        info: '#3498db',
        neutral: '#95a5a6',
        critical: '#c0392b',
        primary: '#3498db'
    };

    // Chart 1: Customers with Duplicate IPs (Horizontal Bar)
    const customersCtx = document.getElementById('customers-chart');
    if (customersCtx && duplicates.length > 0) {
        // Group duplicates by customer
        const customerMap = {};
        duplicates.forEach(dup => {
            dup.affectedCustomers.forEach(customerName => {
                if (!customerMap[customerName]) {
                    customerMap[customerName] = { name: customerName, duplicateIPs: 0, devices: 0 };
                }
                customerMap[customerName].duplicateIPs++;
                customerMap[customerName].devices += dup.deviceCount;
            });
        });

        // Convert to sorted array (top 10 by device count)
        const customerList = Object.values(customerMap)
            .sort((a, b) => b.devices - a.devices)
            .slice(0, 10);

        if (customerList.length > 0) {
            chartInstances.customers = new Chart(customersCtx, {
                type: 'bar',
                data: {
                    labels: customerList.map(c => c.name.substring(0, 25)),
                    datasets: [{
                        label: 'Duplicate IPs',
                        data: customerList.map(c => c.duplicateIPs),
                        backgroundColor: colors.danger,
                        borderWidth: 0,
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const customer = customerList[context.dataIndex];
                                    return `${customer.duplicateIPs} duplicate IPs (${customer.devices} devices)`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: { display: true, text: 'Number of Duplicate IPs' },
                            ticks: { font: { size: 10 }, stepSize: 1 }
                        },
                        y: { ticks: { font: { size: 10 } } }
                    },
                    animation: { duration: 1000, easing: 'easeInOutQuart' }
                }
            });
        } else {
            customersCtx.parentElement.innerHTML = '<p class="text-muted" style="padding: 2rem; text-align: center;">No customer data available</p>';
        }
    }

    // Chart 2: Devices by Status (Pie)
    const statusCtx = document.getElementById('status-chart');
    if (statusCtx && duplicates.length > 0) {
        const statusCounts = { online: 0, recent: 0, ghost: 0, unknown: 0 };
        duplicates.forEach(dup => {
            dup.devices.forEach(dev => {
                statusCounts[dev.status] = (statusCounts[dev.status] || 0) + 1;
            });
        });

        chartInstances.status = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Online', 'Recent (1-7d)', 'Ghost (7d+)', 'Unknown'],
                datasets: [{
                    data: [
                        statusCounts.online,
                        statusCounts.recent,
                        statusCounts.ghost,
                        statusCounts.unknown
                    ],
                    backgroundColor: [colors.success, colors.info, colors.danger, colors.neutral],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 15, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: { duration: 1000, easing: 'easeInOutQuart' }
            }
        });
    }

    // Chart 3: Affected vs Healthy (Doughnut)
    const healthCtx = document.getElementById('health-chart');
    if (healthCtx) {
        chartInstances.health = new Chart(healthCtx, {
            type: 'doughnut',
            data: {
                labels: ['Affected Devices', 'Healthy Devices'],
                datasets: [{
                    data: [
                        summary.totalDevicesAffected,
                        summary.totalValidDevices - summary.totalDevicesAffected
                    ],
                    backgroundColor: [colors.danger, colors.success],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 15, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: { duration: 1000, easing: 'easeInOutQuart' }
            }
        });
    }

    console.log('[DupIP] Charts rendered successfully');
}

function renderDuplicatesTable(duplicates) {
    const container = document.getElementById('duplicates-table-container');
    if (!container || !duplicates) return;

    // Apply filters
    let filtered = duplicates.filter(dup => {
        const matchesSearch = !dupIPState.filters.searchTerm
            || dup.ipAddress.toLowerCase().includes(dupIPState.filters.searchTerm.toLowerCase())
            || dup.devices.some(dev =>
                dev.serialNumber.toLowerCase().includes(dupIPState.filters.searchTerm.toLowerCase()) ||
                dev.customerName.toLowerCase().includes(dupIPState.filters.searchTerm.toLowerCase())
            );

        const matchesSeverity = dupIPState.filters.severityFilter === 'all'
            || dup.severity === dupIPState.filters.severityFilter;

        return matchesSearch && matchesSeverity;
    });

    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>${duplicates.length === 0 ? 'No duplicate IPs found - all clear!' : 'No duplicates match the current filters'}</p>
            </div>
        `;
        return;
    }

    container.innerHTML = `
        <div class="duplicates-list">
            ${filtered.map(dup => `
                <div class="duplicate-card severity-${dup.severity}">
                    <div class="duplicate-header">
                        <div class="duplicate-ip">
                            <i class="fas fa-network-wired"></i>
                            <strong style="font-size: 1.25rem; color: var(--accent-primary);">${escapeHtml(dup.ipAddress)}</strong>
                            <span class="severity-badge severity-${dup.severity}">${dup.severity.toUpperCase()}</span>
                        </div>
                        <div class="duplicate-stats">
                            <span><i class="fas fa-print"></i> ${dup.deviceCount} devices</span>
                            <span><i class="fas fa-building"></i> ${dup.customerCount} customer${dup.customerCount > 1 ? 's' : ''}</span>
                        </div>
                    </div>
                    <div class="duplicate-devices">
                        <table class="devices-table">
                            <thead>
                                <tr>
                                    <th>IP Address</th>
                                    <th>Serial Number</th>
                                    <th>Model</th>
                                    <th>Customer</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${dup.devices.map(dev => `
                                    <tr>
                                        <td><strong style="font-family: 'Courier New', monospace; color: var(--accent-primary);">${escapeHtml(dup.ipAddress)}</strong></td>
                                        <td><strong>${escapeHtml(dev.serialNumber)}</strong></td>
                                        <td>${escapeHtml(dev.model)}</td>
                                        <td>
                                            <strong>${escapeHtml(dev.customerName)}</strong><br>
                                            <small style="color: var(--text-secondary);">${escapeHtml(dev.customerCode)}</small>
                                        </td>
                                        <td>${escapeHtml(dev.location || 'N/A')}</td>
                                        <td>
                                            <span class="status-badge status-${dev.status}">${dev.status}</span>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `).join('')}
        </div>
        <div class="table-footer">
            Showing ${filtered.length} of ${duplicates.length} duplicate IPs
            ${dupIPState.filters.searchTerm || dupIPState.filters.severityFilter !== 'all' ? ' (filtered)' : ''}
        </div>
    `;
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

function showToast(message, type = 'info') {
    console.log(`[Toast] ${type}: ${message}`);
    // Toast implementation would go here
}

function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    html.setAttribute('data-theme', newTheme);
    document.body.setAttribute('data-theme', newTheme);

    const icon = document.querySelector('#theme-toggle i');
    if (icon) {
        icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }

    fetchJson('api/save-preference.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ key: 'theme', value: newTheme })
    }).catch(err => console.error('Failed to save theme:', err));
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        fetch('api/logout.php', { method: 'POST' })
            .then(() => { window.location.href = 'login.html'; })
            .catch(err => {
                console.error('Logout failed:', err);
                window.location.href = 'login.html';
            });
    }
}

console.log('[DupIP] Script loaded');

/*
CHANGELOG
2025-12-03 Claude
- HOTFIX: Changed charts to show customers with duplicate IPs (not severity)
- HOTFIX: Removed worthless "Top 10 IPs" chart
- HOTFIX: Added IP Address as first column in devices table for clarity
- HOTFIX: Made IP addresses more prominent with monospace font and color
- Charts now: Customers with Duplicate IPs, Device Status, Affected vs Healthy
*/
