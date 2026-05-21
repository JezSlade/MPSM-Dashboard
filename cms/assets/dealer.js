/**
 * Dealer Dashboard JavaScript
 * Risk-first dealer workspace with reliable metrics only.
 */

const dealerState = {
    summary: null,
    customers: null,
    duplicateSummary: null,
    alertAggregations: null,
    usage: null,
    loading: false,
    filters: {
        searchTerm: '',
        healthFilter: 'all'
    },
    sort: {
        column: 'healthScore',
        direction: 'asc'
    },
    widgets: {
        summary: { ok: false, error: null, meta: {} },
        portfolio: { ok: false, error: null, meta: {} },
        duplicates: { ok: false, error: null, meta: {} },
        alerts: { ok: false, error: null, meta: {} },
        usage: { ok: false, error: null, meta: {} }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    initializeDealerDashboard();
});

async function initializeDealerDashboard() {
    setupEventListeners();
    await loadDealerDashboard();
}

function setupEventListeners() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => loadDealerDashboard(true));
    }

    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }

    const portfolioSearch = document.getElementById('portfolio-search');
    if (portfolioSearch) {
        portfolioSearch.addEventListener('input', (e) => {
            dealerState.filters.searchTerm = e.target.value;
            renderPortfolioTable(dealerState.customers || []);
            renderScorecard();
        });
    }

    const portfolioFilter = document.getElementById('portfolio-filter');
    if (portfolioFilter) {
        portfolioFilter.addEventListener('change', (e) => {
            dealerState.filters.healthFilter = e.target.value;
            renderPortfolioTable(dealerState.customers || []);
            renderScorecard();
        });
    }
}

async function loadDealerDashboard(forceRefresh = false) {
    if (dealerState.loading) {
        return;
    }

    dealerState.loading = true;
    showLoading('dealer-scorecard');
    showLoading('portfolio-table-container');
    showLoading('incident-panels');
    showLoading('sales-insights');

    try {
        const summaryUrl = `api/get-dealer-summary.php${forceRefresh ? '?force=1' : ''}`;
        const portfolioUrl = `api/get-customer-portfolio.php${forceRefresh ? '?force=1' : ''}`;
        const duplicateUrl = `api/get-duplicate-ips.php?summaryOnly=1${forceRefresh ? '&force=1' : ''}`;
        const alertsUrl = 'api/command-center.php?action=get_aggregations&group_by=alert_only&limit=50';
        const usageUrl = `api/get-dealer-usage.php${forceRefresh ? '?force=1' : ''}`;

        const [summarySettled, portfolioSettled, duplicateSettled, alertsSettled, usageSettled] = await Promise.allSettled([
            fetchJson(summaryUrl),
            fetchJson(portfolioUrl),
            fetchJson(duplicateUrl),
            fetchJson(alertsUrl),
            fetchJson(usageUrl)
        ]);

        hydrateWidgetState('summary', summarySettled, (data) => ({
            ok: true,
            error: null,
            meta: {
                cached: !!data.cached,
                cacheAgeSeconds: Number(data.cache_age_seconds || 0),
                source: data.summary?._dataSource || 'unknown'
            }
        }));

        hydrateWidgetState('portfolio', portfolioSettled, (data) => ({
            ok: true,
            error: null,
            meta: {
                cached: !!data.cached,
                cacheAgeSeconds: Number(data.cache_age_seconds || 0),
                source: data.cache_source || 'unknown'
            }
        }));

        hydrateWidgetState('duplicates', duplicateSettled, (data) => ({
            ok: true,
            error: null,
            meta: {
                cached: !!data.cached,
                cacheAgeSeconds: Number(data.cache_age_seconds || 0),
                source: data.summary?.source || 'unknown'
            }
        }));

        hydrateWidgetState('alerts', alertsSettled, () => ({
            ok: true,
            error: null,
            meta: {
                cached: false,
                cacheAgeSeconds: null,
                source: 'live'
            }
        }));

        hydrateWidgetState('usage', usageSettled, (data) => ({
            ok: true,
            error: null,
            meta: {
                cached: !!data.cached,
                cacheAgeSeconds: Number(data.cache_age_seconds || 0),
                source: data.source || data.usage?._dataSource || 'unknown'
            }
        }));

        dealerState.summary = summarySettled.status === 'fulfilled' ? (summarySettled.value.summary || null) : null;
        dealerState.customers = portfolioSettled.status === 'fulfilled' ? (portfolioSettled.value.customers || []) : null;
        dealerState.duplicateSummary = duplicateSettled.status === 'fulfilled' ? (duplicateSettled.value.summary || null) : null;
        dealerState.alertAggregations = alertsSettled.status === 'fulfilled' ? (alertsSettled.value.aggregations || []) : null;
        dealerState.usage = usageSettled.status === 'fulfilled' ? (usageSettled.value.usage || null) : null;

        renderScorecard();

        if (dealerState.widgets.portfolio.ok) {
            renderPortfolioTable(dealerState.customers || []);
        } else {
            showError('portfolio-table-container', dealerState.widgets.portfolio.error || 'Failed to load customer portfolio');
        }

        renderIncidentPanels();
        renderSalesInsights();

        if (dealerState.widgets.summary.ok && dealerState.widgets.summary.meta.cached && dealerState.widgets.summary.meta.cacheAgeSeconds > 300) {
            const minutes = Math.round(dealerState.widgets.summary.meta.cacheAgeSeconds / 60);
            showToast(`Dealer summary is ${minutes} minutes old. Click refresh for latest.`, 'info');
        }

        const failedWidgets = Object.keys(dealerState.widgets).filter((key) => !dealerState.widgets[key].ok);
        if (failedWidgets.length > 0) {
            showToast(`Some widgets are unavailable: ${failedWidgets.join(', ')}`, 'warning');
        }
    } catch (error) {
        console.error('[Dealer] Dashboard load failed:', error);
        showToast('Failed to load dealer dashboard', 'error');
        showError('dealer-scorecard', 'Failed to load dealer metrics');
        showError('portfolio-table-container', 'Failed to load customer portfolio');
        showError('incident-panels', 'Failed to load incident signals');
        showError('sales-insights', 'Failed to load sales insights');
    } finally {
        dealerState.loading = false;
    }
}

function hydrateWidgetState(key, settled, onSuccess) {
    if (settled.status === 'fulfilled') {
        dealerState.widgets[key] = onSuccess(settled.value);
        return;
    }

    dealerState.widgets[key] = {
        ok: false,
        error: settled.reason?.message || 'Request failed',
        meta: {}
    };
}

function renderScorecard() {
    const container = document.getElementById('dealer-scorecard');
    if (!container) return;

    const derived = deriveKpis();
    const summaryMeta = dealerState.widgets.summary.meta || {};
    const portfolioMeta = dealerState.widgets.portfolio.meta || {};
    const alertsMeta = dealerState.widgets.alerts.meta || {};

    const fleetKpis = [
        makeKpi('Total Customers', derived.totalCustomers, 'building', 'neutral', dealerState.widgets.summary.ok),
        makeKpi('Managed Devices', derived.totalDevices, 'print', 'neutral', dealerState.widgets.summary.ok)
    ];

    const riskKpis = [
        makeKpi('Duplicate IPs', derived.duplicateIPs, 'network-wired', severityByCount(derived.duplicateIPs, 0, 15), dealerState.widgets.summary.ok),
        makeKpi('Customers Affected', derived.customersAffected, 'triangle-exclamation', severityByCount(derived.customersAffected, 0, 5), dealerState.widgets.duplicates.ok),
        makeKpi('Panel Errors (24h)', derived.panel24h, 'bell', severityByCount(derived.panel24h, 20, 80), dealerState.widgets.summary.ok),
        makeKpi('Panel Errors (7d)', derived.panel7d, 'clock', severityByCount(derived.panel7d, 100, 500), dealerState.widgets.summary.ok),
        makeKpi('Critical Customers', derived.criticalCustomersCount, 'skull-crossbones', severityByCount(derived.criticalCustomersCount, 0, 3), dealerState.widgets.portfolio.ok)
    ];

    const trustKpis = [
        makeKpi('Cache Health', formatPercent(derived.cacheHealthScore), 'database', severityByPercent(derived.cacheHealthScore, 85, 70), dealerState.widgets.summary.ok),
        makeKpi('Drilldown Coverage', formatPercent(derived.drillDownCoverage), 'layer-group', severityByPercent(derived.drillDownCoverage, 90, 70), dealerState.widgets.summary.ok)
    ];

    const signatureKpis = [
        makeKpi('Top Alert (24h)', derived.topAlertCode24hLabel, 'bolt', 'warning', dealerState.widgets.alerts.ok, derived.topAlertCode24hCountLabel)
    ];

    container.innerHTML = `
        <div class="kpi-strip">
            ${renderKpiGroup('Fleet Scale', freshnessBadge(summaryMeta), fleetKpis)}
            ${renderKpiGroup('Risk Pressure', freshnessBadge(summaryMeta), riskKpis)}
            ${renderKpiGroup('Data Trust', freshnessBadge(summaryMeta), trustKpis)}
            ${renderKpiGroup('Active Issue Signature', freshnessBadge(alertsMeta), signatureKpis)}
        </div>
    `;
}

function renderKpiGroup(title, badge, kpis) {
    return `
        <section class="kpi-group">
            <header class="kpi-group-header">
                <h3>${escapeHtml(title)}</h3>
                ${badge}
            </header>
            <div class="kpi-cards">
                ${kpis.map(renderKpiCard).join('')}
            </div>
        </section>
    `;
}

function renderKpiCard(kpi) {
    if (!kpi.available) {
        return `
            <article class="metric-card status-unavailable">
                <div class="metric-icon"><i class="fas fa-${escapeHtml(kpi.icon)}"></i></div>
                <div class="metric-value">--</div>
                <div class="metric-label">${escapeHtml(kpi.label)}</div>
                <div class="metric-subtitle">Unavailable</div>
            </article>
        `;
    }

    return `
        <article class="metric-card status-${escapeHtml(kpi.status || 'neutral')}">
            <div class="metric-icon"><i class="fas fa-${escapeHtml(kpi.icon)}"></i></div>
            <div class="metric-value">${escapeHtml(String(kpi.value))}</div>
            <div class="metric-label">${escapeHtml(kpi.label)}</div>
            ${kpi.subtitle ? `<div class="metric-subtitle">${escapeHtml(kpi.subtitle)}</div>` : ''}
        </article>
    `;
}

function makeKpi(label, value, icon, status, available, subtitle = '') {
    return {
        label,
        value,
        icon,
        status,
        available,
        subtitle
    };
}

function freshnessBadge(meta) {
    const source = meta?.source || 'unknown';
    const ageSeconds = meta?.cacheAgeSeconds;

    if (ageSeconds === null || ageSeconds === undefined) {
        return `<span class="freshness-badge">source: ${escapeHtml(source)}</span>`;
    }

    return `<span class="freshness-badge">source: ${escapeHtml(source)} | age: ${escapeHtml(formatSeconds(ageSeconds))}</span>`;
}

function deriveKpis() {
    const summary = dealerState.summary || {};
    const customers = Array.isArray(dealerState.customers) ? dealerState.customers : [];
    const duplicateSummary = dealerState.duplicateSummary || {};
    const aggregations = Array.isArray(dealerState.alertAggregations) ? dealerState.alertAggregations : [];
    const usage = dealerState.usage || {};

    const criticalCustomersCount = customers.filter((c) => Number(c.healthScore || 0) < 60).length;

    let topAlert = null;
    for (const row of aggregations) {
        const count = Number(row.count_24h || 0);
        if (!topAlert || count > Number(topAlert.count_24h || 0)) {
            topAlert = row;
        }
    }

    return {
        totalCustomers: formatNumber(summary.totalCustomers),
        totalDevices: formatNumber(summary.totalDevices),
        duplicateIPs: formatNumber(summary.duplicateIPs),
        panel24h: formatNumber(summary.panelMessagesLast24h),
        panel7d: formatNumber(summary.panelMessagesLast7d),
        cacheHealthScore: Number(summary.cacheHealthScore || 0),
        drillDownCoverage: Number(summary.drillDownCoverage || 0),
        customersAffected: formatNumber(duplicateSummary.customersAffected),
        criticalCustomersCount: formatNumber(criticalCustomersCount),
        topAlertCode24hLabel: topAlert
            ? (topAlert.alert_display_name || topAlert.alert_code || 'Unknown')
            : 'No active alerts',
        topAlertCode24hCountLabel: topAlert
            ? `${formatNumber(topAlert.count_24h)} in 24h`
            : '',
        topOffenders: Array.isArray(duplicateSummary.topOffenders) ? duplicateSummary.topOffenders : [],
        topAlerts: aggregations
            .slice()
            .sort((a, b) => Number(b.count_24h || 0) - Number(a.count_24h || 0))
            .slice(0, 5),
        monthlyPagesManaged: Number(usage.monthlyPagesManaged || 0),
        monthlyPagesUnmanaged: Number(usage.monthlyPagesUnmanaged || 0),
        monthlyPagesTotal: Number(usage.monthlyPagesTotal || 0),
        topUsedDevices: Array.isArray(usage.topUsedDevices) ? usage.topUsedDevices : [],
        topUsedDevicesSource: usage.topUsedDevicesSource || 'impressions'
    };
}

function renderIncidentPanels() {
    const container = document.getElementById('incident-panels');
    if (!container) return;

    const { topOffenders, topAlerts, topUsedDevices, topUsedDevicesSource, monthlyPagesManaged, monthlyPagesUnmanaged, monthlyPagesTotal } = deriveKpis();
    const duplicatesAvailable = dealerState.widgets.duplicates.ok;
    const alertsAvailable = dealerState.widgets.alerts.ok;
    const usageAvailable = dealerState.widgets.usage.ok;

    const duplicateSection = duplicatesAvailable
        ? renderTopOffenders(topOffenders)
        : renderUnavailablePanel('Top Duplicate IP Offenders', dealerState.widgets.duplicates.error);

    const alertsSection = alertsAvailable
        ? renderTopAlerts(topAlerts)
        : renderUnavailablePanel('Top Alert Families (24h)', dealerState.widgets.alerts.error);

    const usageSection = usageAvailable
        ? renderUsageMetricsPanel({
            topUsedDevices,
            topUsedDevicesSource,
            monthlyPagesManaged,
            monthlyPagesUnmanaged,
            monthlyPagesTotal
        })
        : renderUnavailablePanel('Usage Metrics', dealerState.widgets.usage.error);

    container.innerHTML = `${usageSection}${duplicateSection}${alertsSection}`;
}

function renderUsageMetricsPanel({ topUsedDevices, topUsedDevicesSource, monthlyPagesManaged, monthlyPagesUnmanaged, monthlyPagesTotal }) {
    const hasDevices = Array.isArray(topUsedDevices) && topUsedDevices.length > 0;
    const usesPanelFallback = topUsedDevicesSource === 'panel_activity_30d';
    const listTitle = usesPanelFallback ? 'Most Active Devices (30d)' : 'Most Used Devices';

    return `
        <section class="incident-panel">
            <h4><i class="fas fa-gauge-high"></i> Usage Metrics</h4>
            <div class="usage-metric-grid">
                <div class="usage-metric">
                    <span class="usage-label">Managed / Month</span>
                    <strong>${formatCompactNumber(monthlyPagesManaged)}</strong>
                </div>
                <div class="usage-metric">
                    <span class="usage-label">Unmanaged / Month</span>
                    <strong>${formatCompactNumber(monthlyPagesUnmanaged)}</strong>
                </div>
                <div class="usage-metric">
                    <span class="usage-label">Total / Month</span>
                    <strong>${formatCompactNumber(monthlyPagesTotal)}</strong>
                </div>
            </div>
            ${hasDevices ? `
                <div class="usage-subtitle">${escapeHtml(listTitle)}</div>
                <ul class="incident-list usage-list">
                    ${topUsedDevices.slice(0, 6).map((device) => `
                        <li>
                            <div class="incident-title">${escapeHtml(device.deviceLabel || device.serialNumber || 'Unknown device')}</div>
                            <div class="incident-meta">
                                <span>${escapeHtml(device.customerName || device.customerCode || 'Unknown customer')}</span>
                                <span>${usesPanelFallback
                                    ? `${formatCompactNumber(device.panelEvents30d)} panel events`
                                    : `${formatCompactNumber(device.monthlyUsage ?? 0)} pages / month`
                                }</span>
                            </div>
                        </li>
                    `).join('')}
                </ul>
            ` : `<p class="incident-empty">No device impression data in cache.</p>`}
        </section>
    `;
}

function renderTopOffenders(offenders) {
    if (!offenders || offenders.length === 0) {
        return `
            <section class="incident-panel">
                <h4><i class="fas fa-network-wired"></i> Top Duplicate IP Offenders</h4>
                <p class="incident-empty">No duplicate IP conflicts detected.</p>
            </section>
        `;
    }

    return `
        <section class="incident-panel">
            <h4><i class="fas fa-network-wired"></i> Top Duplicate IP Offenders</h4>
            <ul class="incident-list">
                ${offenders.map((offender) => `
                    <li>
                        <div class="incident-title">${escapeHtml(offender.ipAddress || 'Unknown IP')}</div>
                        <div class="incident-meta">
                            <span>${formatNumber(offender.deviceCount)} devices</span>
                            <span>${escapeHtml(offender.customerName || offender.customerCode || 'Unknown customer')}</span>
                            <span class="severity-${escapeHtml(offender.severity || 'low')}">${escapeHtml((offender.severity || 'low').toUpperCase())}</span>
                        </div>
                    </li>
                `).join('')}
            </ul>
        </section>
    `;
}

function renderTopAlerts(alerts) {
    if (!alerts || alerts.length === 0) {
        return `
            <section class="incident-panel">
                <h4><i class="fas fa-bolt"></i> Top Alert Families (24h)</h4>
                <p class="incident-empty">No alert activity in the last 24h.</p>
            </section>
        `;
    }

    return `
        <section class="incident-panel">
            <h4><i class="fas fa-bolt"></i> Top Alert Families (24h)</h4>
            <ul class="incident-list">
                ${alerts.map((alert) => `
                    <li>
                        <div class="incident-title">${escapeHtml(alert.alert_display_name || alert.alert_code || 'Unknown Alert')}</div>
                        <div class="incident-meta">
                            <span>${formatNumber(alert.count_24h)} in 24h</span>
                            <span>${formatNumber(alert.device_count)} devices</span>
                            <span>${escapeHtml(alert.alert_category || 'Uncategorized')}</span>
                        </div>
                    </li>
                `).join('')}
            </ul>
        </section>
    `;
}

function renderUnavailablePanel(title, errorText) {
    return `
        <section class="incident-panel">
            <h4>${escapeHtml(title)}</h4>
            <p class="incident-empty">Unavailable</p>
            <p class="incident-error">${escapeHtml(errorText || 'Endpoint failed')}</p>
        </section>
    `;
}

function renderPortfolioTable(customers) {
    const container = document.getElementById('portfolio-table-container');
    if (!container) return;

    const safeCustomers = Array.isArray(customers) ? customers : [];
    let filtered = safeCustomers.filter((c) => {
        const name = String(c.name || '').toLowerCase();
        const code = String(c.code || '').toLowerCase();
        const term = dealerState.filters.searchTerm.toLowerCase();

        const matchesSearch = !term || name.includes(term) || code.includes(term);
        const score = Number(c.healthScore || 0);
        const matchesHealth = dealerState.filters.healthFilter === 'all'
            || (dealerState.filters.healthFilter === 'healthy' && score >= 90)
            || (dealerState.filters.healthFilter === 'attention' && score >= 70 && score < 90)
            || (dealerState.filters.healthFilter === 'critical' && score < 70);

        return matchesSearch && matchesHealth;
    });

    const { column, direction } = dealerState.sort;
    filtered.sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];

        if (column === 'name' || column === 'code') {
            aVal = String(aVal || '').toLowerCase();
            bVal = String(bVal || '').toLowerCase();
            return direction === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        }

        return direction === 'asc'
            ? Number(aVal || 0) - Number(bVal || 0)
            : Number(bVal || 0) - Number(aVal || 0);
    });

    container.innerHTML = `
        <table class="portfolio-table">
            <thead>
                <tr>
                    <th onclick="sortPortfolio('name')">Customer ${getSortIcon('name')}</th>
                    <th onclick="sortPortfolio('healthScore')">Health ${getSortIcon('healthScore')}</th>
                    <th onclick="sortPortfolio('totalDevices')">Devices ${getSortIcon('totalDevices')}</th>
                    <th onclick="sortPortfolio('offlineDevices')">Offline ${getSortIcon('offlineDevices')}</th>
                    <th onclick="sortPortfolio('ghostDevices')">No Contact &gt;7d ${getSortIcon('ghostDevices')}</th>
                    <th onclick="sortPortfolio('noContactDataDevices')">Contact Missing ${getSortIcon('noContactDataDevices')}</th>
                    <th onclick="sortPortfolio('duplicateIPs')">Duplicate IPs ${getSortIcon('duplicateIPs')}</th>
                    <th onclick="sortPortfolio('panelErrors24h')">Panel 24h ${getSortIcon('panelErrors24h')}</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                ${filtered.length === 0 ? `
                    <tr>
                        <td colspan="9" style="text-align:center; padding:2rem; color: var(--text-secondary);">
                            No customers match the current filters
                        </td>
                    </tr>
                ` : filtered.map((customer) => `
                    <tr>
                        <td class="customer-name">
                            <strong>${escapeHtml(customer.name || customer.code || 'Unknown')}</strong>
                            ${(customer.code && customer.name && customer.code !== customer.name)
                                ? `<br><small class="customer-code">${escapeHtml(customer.code)}</small>`
                                : ''}
                        </td>
                        <td>
                            <div class="health-score health-${escapeHtml(customer.healthStatus || 'fair')}">
                                ${escapeHtml(String(customer.healthScore || 0))}%
                            </div>
                        </td>
                        <td>${formatNumber(customer.totalDevices)}</td>
                        <td class="${Number(customer.offlineDevices || 0) > 0 ? 'text-warning' : ''}">${formatNumber(customer.offlineDevices)}</td>
                        <td class="${Number(customer.ghostDevices || 0) > 0 ? 'text-danger' : ''}">${formatNumber(customer.ghostDevices)}</td>
                        <td class="${Number(customer.noContactDataDevices || 0) > 0 ? 'text-warning' : ''}">${formatNumber(customer.noContactDataDevices)}</td>
                        <td class="${Number(customer.duplicateIPs || 0) > 0 ? 'text-danger' : ''}">${formatNumber(customer.duplicateIPs)}</td>
                        <td class="${Number(customer.panelErrors24h || 0) > 0 ? 'text-warning' : ''}">${formatNumber(customer.panelErrors24h)}</td>
                        <td>
                            <button onclick="drillDownToCustomer('${escapeHtml(customer.code || '')}')" class="btn-view">
                                <i class="fas fa-arrow-right"></i> View
                            </button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        <div class="table-footer">
            Showing ${filtered.length} of ${safeCustomers.length} customers
            ${dealerState.filters.searchTerm || dealerState.filters.healthFilter !== 'all' ? ' (filtered)' : ''}
        </div>
        <div class="table-footer" style="padding-top:0.35rem; color: var(--text-secondary);">
            Offline uses customer contact freshness (48h). No Contact &gt;7d requires valid contact timestamps. Contact Missing means timestamp data is unavailable.
        </div>
    `;
}

function sortPortfolio(column) {
    if (dealerState.sort.column === column) {
        dealerState.sort.direction = dealerState.sort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        dealerState.sort.column = column;
        dealerState.sort.direction = column === 'healthScore' || column === 'name' ? 'asc' : 'desc';
    }

    renderPortfolioTable(dealerState.customers || []);
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
        const customer = Array.isArray(dealerState.customers)
            ? dealerState.customers.find((c) => c.code === customerCode)
            : null;
        await fetchJson('api/save-preferences.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                customerCode,
                ...(customer && customer.name ? { customerName: customer.name } : {})
            })
        });
        window.location.href = `index.php?customerCode=${encodeURIComponent(customerCode)}${
            customer && customer.name ? `&customerName=${encodeURIComponent(customer.name)}` : ''
        }`;
    } catch (error) {
        console.error('[Dealer] Failed to set customer:', error);
        window.location.href = `index.php?customerCode=${encodeURIComponent(customerCode)}`;
    }
}

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

function formatNumber(value) {
    const num = Number(value || 0);
    return Number.isFinite(num) ? num.toLocaleString() : '0';
}

function formatCompactNumber(value) {
    const num = Number(value || 0);
    if (!Number.isFinite(num)) return '0';
    return num.toLocaleString(undefined, { notation: 'compact', maximumFractionDigits: 1 });
}

function formatPercent(value) {
    const num = Number(value || 0);
    return `${Math.max(0, Math.min(100, num)).toFixed(1)}%`;
}

function formatSeconds(seconds) {
    if (seconds < 60) return `${seconds}s`;
    if (seconds < 3600) return `${Math.round(seconds / 60)}m`;
    return `${Math.round(seconds / 3600)}h`;
}

function severityByCount(value, warningThreshold, dangerThreshold) {
    const count = Number(value || 0);
    if (count >= dangerThreshold) return 'danger';
    if (count > warningThreshold) return 'warning';
    return 'success';
}

function severityByPercent(value, successThreshold, warningThreshold) {
    const pct = Number(value || 0);
    if (pct >= successThreshold) return 'success';
    if (pct >= warningThreshold) return 'warning';
    return 'danger';
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, (m) => map[m]);
}

async function fetchJson(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        credentials: 'same-origin'
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const contentType = (response.headers.get('content-type') || '').toLowerCase();
    const rawBody = await response.text();
    if (!contentType.includes('application/json')) {
        const snippet = rawBody.slice(0, 200).replace(/\s+/g, ' ').trim();
        throw new Error(`Non-JSON response from ${url}: ${snippet}`);
    }

    let data;
    try {
        data = JSON.parse(rawBody);
    } catch (parseError) {
        const snippet = rawBody.slice(0, 200).replace(/\s+/g, ' ').trim();
        throw new Error(`Invalid JSON from ${url}: ${snippet}`);
    }

    if (data.success === false) {
        throw new Error(data.error || 'Request failed');
    }

    return data;
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

    fetchJson('api/save-preferences.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ theme: newTheme })
    }).catch((err) => console.error('Failed to save theme:', err));
}

function renderSalesInsights() {
    const container = document.getElementById('sales-insights');
    if (!container) return;

    const usage = dealerState.usage || {};
    const machine = usage.machineMetrics || {};
    const consumables = usage.consumables || {};
    const topUsedDevices = Array.isArray(usage.topUsedDevices) ? usage.topUsedDevices : [];
    const source = usage.topUsedDevicesSource || 'unknown';
    const sourceLabel = source.replaceAll('_', ' ');
    const consumablesSource = String(consumables.dataSource || 'none');
    const usingConsumablesProxy = consumablesSource.includes('proxy');
    const consumablesLabel = usingConsumablesProxy ? 'Alert Signals (30d)' : 'Supply Alerts';
    const consumablesDevicesLabel = usingConsumablesProxy ? 'Devices with Alert Signals' : 'Devices with Supply Alerts';

    container.innerHTML = `
        <div class="sales-metric-grid">
            ${renderSalesMetric('Total Pages / Month', formatNumber(usage.monthlyPagesTotal), 'fa-copy')}
            ${renderSalesMetric('Managed Pages / Month', formatNumber(usage.monthlyPagesManaged), 'fa-file-lines')}
            ${renderSalesMetric('Active Devices', formatNumber(machine.totalActiveDevices), 'fa-print')}
            ${renderSalesMetric('Offline Devices (2d)', `${formatNumber(machine.offlineDevices)} (${formatPercent(machine.offlineRatePct || 0)})`, 'fa-plug-circle-xmark')}
            ${renderSalesMetric('No Contact > 7 Days', `${formatNumber(machine.noContactOver7dDevices)} (${formatPercent(machine.noContactOver7dRatePct || 0)})`, 'fa-user-slash')}
            ${renderSalesMetric('Contact Data Missing', `${formatNumber(machine.noContactDataDevices)} (${formatPercent(machine.noContactDataRatePct || 0)})`, 'fa-circle-question')}
            ${renderSalesMetric(consumablesLabel, formatNumber(consumables.totalAlerts), 'fa-box-open')}
            ${renderSalesMetric(consumablesDevicesLabel, formatNumber(consumables.devicesWithAlerts), 'fa-triangle-exclamation')}
            ${renderSalesMetric('Usage Source', sourceLabel, 'fa-database')}
        </div>
        <p class="sales-note">
            <strong>Definitions:</strong> Offline means flagged offline or no contact in 2+ days. No Contact > 7 Days uses last-contact timestamps. Contact Data Missing means no valid last-contact timestamp was available.
        </p>
        <div class="sales-insight-panels">
            <section class="sales-panel">
                <h4><i class="fas fa-chart-line"></i> Top 10 Most Used Devices</h4>
                ${renderTopUsedDevicesTable(topUsedDevices, source)}
            </section>
            <section class="sales-panel">
                <h4><i class="fas fa-boxes-stacked"></i> Consumables by Family</h4>
                ${renderSimpleRanking(consumables.topFamilies, 'family', 'count', 'No consumable alert families found.')}
            </section>
            <section class="sales-panel">
                <h4><i class="fas fa-building-user"></i> Customers with Most Consumable Alerts</h4>
                ${renderSimpleRanking(consumables.topCustomers, 'customerName', 'count', 'No consumable alert customer data.')}
            </section>
            <section class="sales-panel">
                <h4><i class="fas fa-industry"></i> Fleet by Brand</h4>
                ${renderSimpleRanking(machine.topBrands, 'brand', 'count', 'No brand mix data in cache.')}
            </section>
        </div>
    `;
}

function renderSalesMetric(label, value, icon) {
    return `
        <article class="sales-metric-card">
            <div class="sales-metric-icon"><i class="fas ${icon}"></i></div>
            <div class="sales-metric-value">${escapeHtml(String(value))}</div>
            <div class="sales-metric-label">${escapeHtml(label)}</div>
        </article>
    `;
}

function renderTopUsedDevicesTable(devices, source) {
    if (!devices.length) {
        return '<p class="incident-empty">No device usage metrics available.</p>';
    }

    const usingPanel = source === 'panel_activity_30d';
    const metricLabel = usingPanel ? 'Panel Events (30d)' : 'Monthly Usage (Pages)';

    return `
        <div class="sales-table-wrap">
            <table class="sales-table">
                <thead>
                    <tr>
                        <th>Device</th>
                        <th>Customer</th>
                        <th>${metricLabel}</th>
                    </tr>
                </thead>
                <tbody>
                    ${devices.slice(0, 10).map((row) => `
                        <tr>
                            <td>${escapeHtml(row.deviceLabel || row.serialNumber || 'Unknown')}</td>
                            <td>${escapeHtml(row.customerName || row.customerCode || 'Unknown')}</td>
                            <td>${formatNumber(usingPanel ? row.panelEvents30d : (row.monthlyUsage ?? row.totalImpressions ?? 0))}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function renderSimpleRanking(rows, labelKey, valueKey, emptyText) {
    const safeRows = Array.isArray(rows) ? rows : [];
    if (!safeRows.length) {
        return `<p class="incident-empty">${escapeHtml(emptyText)}</p>`;
    }

    return `
        <ul class="incident-list">
            ${safeRows.slice(0, 8).map((row) => `
                <li>
                    <div class="incident-title">${escapeHtml(row[labelKey] || 'Unknown')}</div>
                    <div class="incident-meta">
                        <span>${formatNumber(row[valueKey])}</span>
                    </div>
                </li>
            `).join('')}
        </ul>
    `;
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        fetch('api/logout.php', { method: 'POST' })
            .then(() => {
                window.location.href = 'login.html';
            })
            .catch((err) => {
                console.error('Logout failed:', err);
                window.location.href = 'login.html';
            });
    }
}
