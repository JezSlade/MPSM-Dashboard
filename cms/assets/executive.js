/**
 * Executive Dashboard JavaScript
 * Handles data fetching, rendering, filtering, and sorting for executive view
 */

// Global state
const executiveState = {
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
    initializeExecutiveDashboard();
});

async function initializeExecutiveDashboard() {
    console.log('[Executive] Initializing dashboard...');

    // Set up event listeners
    setupEventListeners();

    // Load dashboard data
    await loadExecutiveDashboard();
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
        refreshBtn.addEventListener('click', () => loadExecutiveDashboard(true));
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
            executiveState.filters.searchTerm = e.target.value;
            renderPortfolioTable(executiveState.customers);
        });
    }

    // Portfolio filter
    const portfolioFilter = document.getElementById('portfolio-filter');
    if (portfolioFilter) {
        portfolioFilter.addEventListener('change', (e) => {
            executiveState.filters.healthFilter = e.target.value;
            renderPortfolioTable(executiveState.customers);
        });
    }
}

async function loadExecutiveDashboard(forceRefresh = false) {
    if (executiveState.loading) {
        console.log('[Executive] Already loading, skipping...');
        return;
    }

    executiveState.loading = true;

    try {
        console.log('[Executive] Loading dashboard data...');

        // Show loading states
        showLoading('executive-scorecard');
        showLoading('portfolio-table-container');
        showLoading('data-quality-container');

        // Fetch summary and portfolio in parallel
        const forceParam = forceRefresh ? '?force=1' : '';
        const [summaryResp, portfolioResp] = await Promise.all([
            fetchJson(`api/get-executive-summary.php${forceParam}`),
            fetchJson(`api/get-customer-portfolio.php${forceParam}`)
        ]);

        executiveState.summary = summaryResp.summary;
        executiveState.customers = portfolioResp.customers;

        console.log('[Executive] Data loaded:', {
            totalCustomers: executiveState.customers.length,
            totalDevices: executiveState.summary.totalDevices,
            cached: summaryResp.cached
        });

        // Render all sections
        renderScorecard(executiveState.summary);
        renderPortfolioTable(executiveState.customers);
        renderDataQualityCards(executiveState.summary);

        // Show cache age toast if not fresh
        if (summaryResp.cached && summaryResp.cache_age_seconds > 300) {
            const minutes = Math.round(summaryResp.cache_age_seconds / 60);
            showToast(`Data is ${minutes} minutes old. Click refresh for latest.`, 'info');
        }

    } catch (error) {
        console.error('[Executive] Load failed:', error);
        showToast('Failed to load executive dashboard', 'error');
        showError('executive-scorecard', 'Failed to load metrics');
        showError('portfolio-table-container', 'Failed to load customer portfolio');
    } finally {
        executiveState.loading = false;
    }
}

function renderScorecard(summary) {
    const container = document.getElementById('executive-scorecard');
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

        <!-- Asset Completeness -->
        <div class="metric-card ${summary.assetNumberCompleteness >= 90 ? 'status-success' : summary.assetNumberCompleteness >= 75 ? 'status-warning' : 'status-danger'}">
            <div class="metric-icon"><i class="fas fa-tags"></i></div>
            <div class="metric-value">${summary.assetNumberCompleteness}%</div>
            <div class="metric-label">Asset Completeness</div>
            <div class="metric-subtitle">${summary.missingAssetNumbers} missing</div>
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

        <!-- Cache Health -->
        <div class="metric-card ${summary.cacheHealthScore >= 90 ? 'status-success' : summary.cacheHealthScore >= 75 ? 'status-warning' : 'status-danger'}">
            <div class="metric-icon"><i class="fas fa-database"></i></div>
            <div class="metric-value">${summary.cacheHealthScore}%</div>
            <div class="metric-label">Cache Health</div>
            <div class="metric-subtitle">${formatSeconds(summary.cacheFreshnessAvg)} avg age</div>
        </div>

        <!-- Alert Definition Coverage -->
        <div class="metric-card ${summary.alertDefinitionCoverage >= 90 ? 'status-success' : summary.alertDefinitionCoverage >= 70 ? 'status-warning' : 'status-danger'}">
            <div class="metric-icon"><i class="fas fa-book"></i></div>
            <div class="metric-value">${summary.alertDefinitionCoverage}%</div>
            <div class="metric-label">Alert Definitions</div>
            <div class="metric-subtitle">${summary.unmappedAlertCodes} unmapped codes</div>
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

function renderPortfolioTable(customers) {
    const container = document.getElementById('portfolio-table-container');
    if (!container || !customers) return;

    // Apply filters
    let filtered = customers.filter(c => {
        const matchesSearch = !executiveState.filters.searchTerm
            || c.name.toLowerCase().includes(executiveState.filters.searchTerm.toLowerCase())
            || c.code.toLowerCase().includes(executiveState.filters.searchTerm.toLowerCase());

        const matchesHealth = executiveState.filters.healthFilter === 'all'
            || (executiveState.filters.healthFilter === 'healthy' && c.healthScore >= 90)
            || (executiveState.filters.healthFilter === 'attention' && c.healthScore >= 70 && c.healthScore < 90)
            || (executiveState.filters.healthFilter === 'critical' && c.healthScore < 70);

        return matchesSearch && matchesHealth;
    });

    // Apply sorting
    const { column, direction } = executiveState.sort;
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
            ${executiveState.filters.searchTerm || executiveState.filters.healthFilter !== 'all' ? ' (filtered)' : ''}
        </div>
    `;
}

function renderDataQualityCards(summary) {
    const container = document.getElementById('data-quality-container');
    if (!container) return;

    container.innerHTML = `
        <div class="data-quality-grid">
            <!-- Asset Completeness -->
            <div class="quality-card">
                <div class="quality-card-header">
                    <div class="quality-card-title">Asset Number Completeness</div>
                    <div class="quality-score ${getQualityClass(summary.assetNumberCompleteness)}">
                        ${summary.assetNumberCompleteness}%
                    </div>
                </div>
                <div class="quality-progress">
                    <div class="quality-progress-bar ${getQualityClass(summary.assetNumberCompleteness)}"
                         style="width: ${summary.assetNumberCompleteness}%"></div>
                </div>
                <div class="quality-details">
                    ${summary.missingAssetNumbers} devices missing asset numbers
                </div>
            </div>

            <!-- Cache Health -->
            <div class="quality-card">
                <div class="quality-card-header">
                    <div class="quality-card-title">Cache Health Score</div>
                    <div class="quality-score ${getQualityClass(summary.cacheHealthScore)}">
                        ${summary.cacheHealthScore}%
                    </div>
                </div>
                <div class="quality-progress">
                    <div class="quality-progress-bar ${getQualityClass(summary.cacheHealthScore)}"
                         style="width: ${summary.cacheHealthScore}%"></div>
                </div>
                <div class="quality-details">
                    Drill-down coverage: ${summary.drillDownCoverage}% | Avg age: ${formatSeconds(summary.cacheFreshnessAvg)}
                </div>
            </div>

            <!-- Alert Definition Coverage -->
            <div class="quality-card">
                <div class="quality-card-header">
                    <div class="quality-card-title">Alert Definition Coverage</div>
                    <div class="quality-score ${getQualityClass(summary.alertDefinitionCoverage)}">
                        ${summary.alertDefinitionCoverage}%
                    </div>
                </div>
                <div class="quality-progress">
                    <div class="quality-progress-bar ${getQualityClass(summary.alertDefinitionCoverage)}"
                         style="width: ${summary.alertDefinitionCoverage}%"></div>
                </div>
                <div class="quality-details">
                    ${summary.unmappedAlertCodes} alert codes need descriptions
                </div>
            </div>

            <!-- Connector Health -->
            <div class="quality-card">
                <div class="quality-card-header">
                    <div class="quality-card-title">Connector Health</div>
                    <div class="quality-score ${getQualityClass(summary.connectorHealthScore)}">
                        ${summary.connectorHealthScore}%
                    </div>
                </div>
                <div class="quality-progress">
                    <div class="quality-progress-bar ${getQualityClass(summary.connectorHealthScore)}"
                         style="width: ${summary.connectorHealthScore}%"></div>
                </div>
                <div class="quality-details">
                    ${summary.connectorsOffline} of ${summary.totalConnectors} connectors offline
                </div>
            </div>
        </div>
    `;
}

function sortPortfolio(column) {
    if (executiveState.sort.column === column) {
        // Toggle direction
        executiveState.sort.direction = executiveState.sort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        // New column, default direction
        executiveState.sort.column = column;
        executiveState.sort.direction = column === 'healthScore' || column === 'name' ? 'asc' : 'desc';
    }

    renderPortfolioTable(executiveState.customers);
}

function getSortIcon(column) {
    if (executiveState.sort.column !== column) {
        return '<i class="fas fa-sort"></i>';
    }
    return executiveState.sort.direction === 'asc'
        ? '<i class="fas fa-sort-up"></i>'
        : '<i class="fas fa-sort-down"></i>';
}

function getQualityClass(score) {
    if (score >= 90) return 'excellent';
    if (score >= 75) return 'good';
    if (score >= 60) return 'fair';
    return 'poor';
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
        console.error('[Executive] Failed to set customer:', error);
        // Redirect anyway with query param
        window.location.href = `index.php?customerCode=${encodeURIComponent(customerCode)}`;
    }
}

// Utility functions
function showLoading(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = `
        <div class="executive-loading">
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

function showToast(message, type = 'info') {
    // Reuse existing toast from shared.js if available, otherwise fallback
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
    } else {
        console.log(`[Toast ${type}]`, message);
    }
}

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

console.log('[Executive] Script loaded');
