/**
 * Command Center - Notification Management System
 * Handles notification rules, active notifications, and alert statistics
 */

// State
let autoRefreshInterval = null;
let currentTab = 'notifications';
let notificationFilter = '';
let notificationCustomerFilter = '';
let _aggMap = null; // cache of alert aggregations keyed by device|alert
let _aggMapLoadedAt = 0;
const NOTIFICATION_PAGE_SIZE = 50;
let notificationOffset = 0;
let alertCenterRoot = null;
let isMounted = false;
let tabsInitialized = false;
let controlsInitialized = false;
let definitionFormBound = false;
let visibilityHandlerBound = false;
const REQUEST_TIMEOUTS = {
    initial: 30000,
    refresh: 15000,
    background: 12000
};
const requestGuards = {
    notifications: { token: 0, controller: null, loaded: false, loading: false, lastData: null },
    panel: { token: 0, controller: null, loaded: false, loading: false, lastData: null },
    rules: { token: 0, controller: null, loaded: false, loading: false, lastData: null },
    definitions: { token: 0, controller: null, loaded: false, loading: false, lastData: null },
    statistics: { token: 0, controller: null, loaded: false, loading: false, lastData: null },
    customers: { token: 0, controller: null, loaded: false, loading: false, lastData: null }
};

const NOTIFICATION_COLUMNS = [
    { key: 'severity', label: 'Severity', sortable: true, required: true, sticky: true },
    { key: 'alert_code', label: 'Code', sortable: true },
    { key: 'alert_display_name', label: 'Description', sortable: true },
    { key: 'model', label: 'Device Model', sortable: true },
    { key: 'equipment_id', label: 'Systel Equip ID', sortable: true },
    { key: 'device_serial', label: 'Serial Number', sortable: true, required: true },
    { key: 'department', label: 'Location', sortable: true },
    { key: 'ip_address', label: 'IP Address', sortable: true },
    { key: 'occurrence_count', label: 'Occurrences', sortable: true }
];
const COLUMN_STORAGE_KEY = 'mpsm.alerts.notification.columns.v2';
const SORT_STORAGE_KEY = 'mpsm.alerts.notification.sort.v1';
const FILTER_STORAGE_KEY = 'mpsm.alerts.notification.filters.v1';
let notificationSort = { key: 'last_occurrence_ny', direction: 'desc' };
let notificationColumnVisibility = {};
let notificationSearchFilter = '';
let notificationStatusFilter = 'all';
let notificationTotalCount = 0;

function getScopeRoot() {
    if (alertCenterRoot && document.contains(alertCenterRoot)) {
        return alertCenterRoot;
    }
    return document;
}

function scopedById(id) {
    const root = getScopeRoot();
    if (root && typeof root.querySelector === 'function') {
        const inRoot = root.querySelector(`#${id}`);
        if (inRoot) {
            return inRoot;
        }
    }
    return document.getElementById(id);
}

function scopedQueryAll(selector) {
    const root = getScopeRoot();
    return root && typeof root.querySelectorAll === 'function'
        ? Array.from(root.querySelectorAll(selector))
        : [];
}

function loadStoredNotificationState() {
    try {
        const colsRaw = localStorage.getItem(COLUMN_STORAGE_KEY);
        if (colsRaw) {
            const parsed = JSON.parse(colsRaw);
            if (parsed && typeof parsed === 'object') {
                notificationColumnVisibility = parsed;
            }
        }
        const sortRaw = localStorage.getItem(SORT_STORAGE_KEY);
        if (sortRaw) {
            const parsed = JSON.parse(sortRaw);
            if (parsed && parsed.key && (parsed.direction === 'asc' || parsed.direction === 'desc')) {
                notificationSort = parsed;
            }
        }
        const filtersRaw = localStorage.getItem(FILTER_STORAGE_KEY);
        if (filtersRaw) {
            const parsed = JSON.parse(filtersRaw);
            if (parsed && typeof parsed === 'object') {
                notificationSearchFilter = typeof parsed.deviceSearch === 'string' ? parsed.deviceSearch : '';
                notificationStatusFilter = 'all';
                notificationFilter = '';
                notificationCustomerFilter = typeof parsed.customerCode === 'string' ? parsed.customerCode.trim() : notificationCustomerFilter;
            }
        }
    } catch (_error) {
        notificationColumnVisibility = {};
        notificationSort = { key: 'created_at_ny', direction: 'desc' };
    }
}

function saveNotificationColumns() {
    try {
        localStorage.setItem(COLUMN_STORAGE_KEY, JSON.stringify(notificationColumnVisibility));
    } catch (_error) {}
}

function saveNotificationSort() {
    try {
        localStorage.setItem(SORT_STORAGE_KEY, JSON.stringify(notificationSort));
    } catch (_error) {}
}

function saveNotificationFilters() {
    try {
        localStorage.setItem(FILTER_STORAGE_KEY, JSON.stringify({
            severity: notificationFilter || '',
            status: notificationStatusFilter || 'active',
            deviceSearch: notificationSearchFilter || '',
            customerCode: notificationCustomerFilter || ''
        }));
    } catch (_error) {}
}

function isNotificationColumnVisible(columnKey) {
    const column = NOTIFICATION_COLUMNS.find(c => c.key === columnKey);
    if (!column) return false;
    if (column.required) return true;
    if (!(columnKey in notificationColumnVisibility)) {
        return true;
    }
    return notificationColumnVisibility[columnKey] !== false;
}

function getRequestTimeout(sectionName, silent) {
    const section = requestGuards[sectionName];
    if (!section || !section.loaded) {
        return REQUEST_TIMEOUTS.initial;
    }
    return silent ? REQUEST_TIMEOUTS.background : REQUEST_TIMEOUTS.refresh;
}

function abortSectionRequest(sectionName) {
    const section = requestGuards[sectionName];
    if (section && section.controller) {
        section.controller.abort();
        section.controller = null;
    }
}

// Severity Configuration
const SEVERITY_CONFIG = {
    critical: {
        color: '#e74c3c',
        icon: 'fire',
        label: 'Critical'
    },
    high: {
        color: '#f39c12',
        icon: 'exclamation-circle',
        label: 'High'
    },
    warning: {
        color: '#f39c12',
        icon: 'exclamation-triangle',
        label: 'Warning'
    },
    info: {
        color: '#3498db',
        icon: 'info-circle',
        label: 'Info'
    }
};

function resolveRoot(options = {}) {
    if (options.root && options.root.nodeType === 1) {
        return options.root;
    }
    if (typeof options.root === 'string' && options.root.trim()) {
        return document.querySelector(options.root.trim());
    }

    const bootstrap = window.ALERT_CENTER_BOOTSTRAP || {};
    if (typeof bootstrap.rootSelector === 'string' && bootstrap.rootSelector.trim()) {
        return document.querySelector(bootstrap.rootSelector.trim());
    }

    return document.getElementById('alert-center-root');
}

function getMountOptions(options = {}) {
    const merged = Object.assign({}, window.ALERT_CENTER_BOOTSTRAP || {}, options || {});
    const standalone = document.body?.dataset.alertCenterStandalone === '1';
    const useUrlParams = merged.useUrlParams === true || merged.autoMount === true || standalone;
    if (useUrlParams) {
        const params = new URLSearchParams(window.location.search || '');
        if (!merged.initialTab) {
            merged.initialTab = (params.get('tab') || '').trim();
        }
        if (!merged.customerCode) {
            merged.customerCode = (params.get('customerCode') || '').trim();
        }
    }
    return merged;
}

function loadCurrentTab(silent = false) {
    if (currentTab === 'notifications') {
        loadNotifications(silent);
    } else if (currentTab === 'rules') {
        loadRules(silent);
    } else if (currentTab === 'statistics') {
        loadStatistics(silent);
    } else if (currentTab === 'panel') {
        loadPanelMessages();
    } else if (currentTab === 'definitions') {
        loadDefinitions(silent);
    }
}

function activateTab(target, shouldLoad = true) {
    const tabButtons = scopedQueryAll('.monitor-tab-btn');
    const tabPanels = scopedQueryAll('.tab-panel');
    currentTab = target;

    tabButtons.forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.tab === target);
    });

    tabPanels.forEach((panel) => {
        panel.classList.toggle('active', panel.dataset.tab === target);
    });

    if (target !== 'panel') {
        abortSectionRequest('panel');
    }

    if (shouldLoad) {
        loadCurrentTab();
    }
}

function mountAlertCenter(options = {}) {
    const mountOptions = getMountOptions(options);
    const root = resolveRoot(mountOptions);
    if (!root) {
        return false;
    }

    alertCenterRoot = root;
    loadStoredNotificationState();

    if (!tabsInitialized) {
        initializeTabs();
        tabsInitialized = true;
    }
    if (!controlsInitialized) {
        initializeControls();
        controlsInitialized = true;
    }
    if (!definitionFormBound) {
        const definitionForm = scopedById('definition-form');
        if (definitionForm) {
            definitionForm.addEventListener('submit', handleDefinitionSubmit);
        }
        definitionFormBound = true;
    }
    if (!visibilityHandlerBound) {
        document.addEventListener('visibilitychange', () => {
            if (!isMounted) {
                return;
            }
            if (document.hidden) {
                stopPanelAutoRefresh();
            } else if (currentTab === 'panel') {
                startPanelAutoRefresh();
            }
        });
        visibilityHandlerBound = true;
    }

    initPanelTab();
    isMounted = true;

    if (mountOptions.customerCode) {
        syncNotificationCustomer(mountOptions.customerCode);
        const panelFilter = scopedById('cc-panel-customer');
        if (panelFilter) {
            panelFilter.value = mountOptions.customerCode;
        }
    }

    const validTabs = new Set(['notifications']);
    if (mountOptions.initialTab && validTabs.has(mountOptions.initialTab)) {
        activateTab(mountOptions.initialTab, true);
    } else {
        loadCurrentTab();
    }

    startAutoRefresh();
    startPanelAutoRefresh();
    return true;
}

function unmountAlertCenter() {
    isMounted = false;
    stopAutoRefresh();
    stopPanelAutoRefresh();
    Object.keys(requestGuards).forEach(abortSectionRequest);
    alertCenterRoot = null;
}

// Tab Switching
function initializeTabs() {
    const tabButtons = scopedQueryAll('.monitor-tab-btn');

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const target = button.dataset.tab;
            activateTab(target, true);
        });
    });
}

// Initialize Controls
function initializeControls() {
    // Notification severity filter
    const filterSelect = scopedById('notification-filter');
    if (filterSelect) {
        filterSelect.value = notificationFilter || '';
        filterSelect.addEventListener('change', (e) => {
            notificationFilter = e.target.value;
            saveNotificationFilters();
            loadNotifications();
        });
    }

    // Notification customer filter (populate with Names, value=Code)
    const customerFilterSelect = scopedById('notification-customer-filter');
    if (customerFilterSelect) {
        customerFilterSelect.addEventListener('change', (e) => {
            notificationCustomerFilter = e.target.value;
            saveNotificationFilters();
            resetNotificationData();
            const panelCustomer = scopedById('cc-panel-customer');
            if (panelCustomer) {
                panelCustomer.value = notificationCustomerFilter;
                panelOffset = 0;
            }
            loadNotifications();
            if (currentTab === 'panel') {
                loadPanelMessages();
            }
        });
        loadCustomerOptionsForCC();
    }

    // Auto-refresh toggle
    const autoRefreshCheckbox = scopedById('notification-auto-refresh');
    if (autoRefreshCheckbox) {
        autoRefreshCheckbox.addEventListener('change', (e) => {
            if (e.target.checked) {
                startAutoRefresh();
            } else {
                stopAutoRefresh();
            }
        });
    }

    // Create rule button
    const createRuleBtn = scopedById('create-rule-btn');
    if (createRuleBtn) {
        createRuleBtn.addEventListener('click', () => {
            openRuleModal();
        });
    }

    // Rule form submission
    const ruleForm = scopedById('rule-form');
    if (ruleForm) {
        ruleForm.addEventListener('submit', handleRuleSubmit);
    }

    // Statistics sort
    const statsSort = scopedById('stats-sort');
    if (statsSort) {
        statsSort.addEventListener('change', () => {
            loadStatistics();
        });
    }

    const notifLoadMore = scopedById('notification-load-more');
    if (notifLoadMore) {
        notifLoadMore.addEventListener('click', () => {
            notifLoadMore.disabled = true;
            loadNotifications(true, true);
        });
    }

    const alertsRefresh = scopedById('alerts-refresh');
    if (alertsRefresh) {
        alertsRefresh.addEventListener('click', () => {
            notificationOffset = 0;
            loadNotifications(false, false);
        });
    }

    const columnSettings = scopedById('notification-column-settings');
    if (columnSettings) {
        columnSettings.addEventListener('click', () => {
            const panel = scopedById('notification-column-panel');
            if (panel) {
                panel.hidden = !panel.hidden;
            }
        });
    }
}

function syncNotificationCustomer(code) {
    notificationCustomerFilter = (code || '').trim();
    const notifSel = scopedById('notification-customer-filter');
    if (notifSel) {
        notifSel.value = notificationCustomerFilter;
    }
    const panelSel = scopedById('cc-panel-customer');
    if (panelSel) {
        panelSel.value = notificationCustomerFilter;
    }
    saveNotificationFilters();
}

function resetNotificationData() {
    notificationOffset = 0;
    notificationTotalCount = 0;
    requestGuards.notifications.loaded = false;
    requestGuards.notifications.lastData = null;
}

// Auto-refresh Management
function startAutoRefresh() {
    stopAutoRefresh();
    autoRefreshInterval = setInterval(() => {
        if (!isMounted) {
            return;
        }
        if (currentTab === 'panel') {
            return;
        }
        loadCurrentTab(true);
        // Note: Panel tab has its own 30s auto-refresh via startPanelAutoRefresh()
    }, 10000); // 10 seconds
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// ========================================
// TAB 1: Active Notifications
// ========================================

async function loadNotifications(silent = false, append = false) {
    const container = scopedById('notifications-container');
    if (!container) return;

    const guard = requestGuards.notifications;
    if (guard.loading && !append) {
        return;
    }

    if (!silent && !append && !guard.loaded) {
        container.innerHTML = '<div class="loading">Loading notifications...</div>';
    }

    if (!append) {
        notificationOffset = 0;
    }

    const params = new URLSearchParams({ action: 'get_aggregations', group_by: 'device_alert', limit: String(NOTIFICATION_PAGE_SIZE), offset: String(notificationOffset) });
    if (notificationFilter) {
        params.set('severity', notificationFilter);
    }
    if (notificationCustomerFilter) {
        params.set('customerCode', notificationCustomerFilter);
    }

    const token = ++guard.token;
    const controller = new AbortController();
    const timeoutMs = getRequestTimeout('notifications', silent);
    const timeoutId = setTimeout(() => controller.abort(), timeoutMs);
    guard.controller = controller;
    guard.loading = true;

    try {
        const response = await fetch(`api/command-center.php?${params.toString()}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            signal: controller.signal
        });

        if (token !== guard.token) {
            return;
        }
        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const data = await response.json();
        if (token !== guard.token) {
            return;
        }
        if (!data.success) {
            throw new Error(data.error || 'Failed to load alerts');
        }

        const notifications = (Array.isArray(data.aggregations) ? data.aggregations : []).map(normalizeAlertRow);
        notificationTotalCount = Number(data.total_count || notifications.length || 0);
        notificationOffset += notifications.length;

        const nextRows = append
            ? (((guard.lastData && Array.isArray(guard.lastData.rows)) ? guard.lastData.rows : []).concat(notifications))
            : notifications;

        const filtered = applyNotificationClientFilters(nextRows);
        const sorted = sortNotifications(filtered);
        guard.lastData = { rows: nextRows };
        guard.loaded = true;

        renderNotifications(sorted);
        updateNotificationLoadMore(notifications.length);
    } catch (error) {
        if (token !== guard.token) {
            return;
        }
        console.error('Error loading notifications:', error);
        const hasCachedRows = guard.lastData && Array.isArray(guard.lastData.rows) && guard.lastData.rows.length > 0;
        if (hasCachedRows) {
            const filtered = applyNotificationClientFilters(guard.lastData.rows);
            const sorted = sortNotifications(filtered);
            renderNotifications(sorted, error.name === 'AbortError' ? 'Showing cached data while refresh retries.' : 'Showing cached data; latest refresh failed.');
            return;
        }
        const errorMsg = error.name === 'AbortError' ? 'Request timed out' : error.message;
        container.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ${escapeHtml(errorMsg)}</div>`;
    } finally {
        clearTimeout(timeoutId);
        if (guard.controller === controller) {
            guard.controller = null;
        }
        guard.loading = false;
    }
}

function updateNotificationLoadMore(pageSizeReturned) {
    const loadMoreBtn = scopedById('notification-load-more');
    if (!loadMoreBtn) return;
    if (pageSizeReturned >= NOTIFICATION_PAGE_SIZE && notificationOffset < notificationTotalCount) {
        loadMoreBtn.style.display = 'inline-flex';
        loadMoreBtn.disabled = false;
    } else {
        loadMoreBtn.style.display = 'none';
    }
}

function normalizeAlertRow(row) {
    const alertCode = row.alert_code || row.maintenance_alert_code || '';
    const displayName = row.alert_display_name || row.display_name || row.alert_description || alertCode || 'Alert';
    return {
        ...row,
        id: row.id || `${row.device_serial || ''}:${alertCode}`,
        severity: row.severity || row.severity_override || 'warning',
        alert_code: alertCode,
        alert_display_name: displayName,
        device_serial: row.device_serial || '',
        model: row.model || row.device_model || '',
        equipment_id: row.equipment_id || row.device_equipment_id || row.device_identifier || '',
        department: row.department || row.device_department || '',
        ip_address: row.ip_address || '',
        occurrence_count: Number(row.occurrence_count || row.count_24h || row.count_1h || 1),
        created_at_ny: row.last_occurrence_ny || row.created_at_ny || '',
        last_occurrence_ny: row.last_occurrence_ny || row.created_at_ny || ''
    };
}

function applyNotificationClientFilters(rows) {
    const sourceRows = Array.isArray(rows) ? rows : [];
    return sourceRows.filter((row) => {
        if (notificationSearchFilter) {
            const haystack = [
                row.device_serial,
                row.equipment_id,
                row.customer_description,
                row.customer_code,
                row.message,
                row.model,
                row.equipment_id,
                row.department,
                row.alert_code,
                row.alert_display_name,
                row.ip_address
            ].join(' ').toLowerCase();
            if (!haystack.includes(notificationSearchFilter.toLowerCase())) {
                return false;
            }
        }
        if (notificationFilter && row.severity !== notificationFilter) {
            return false;
        }
        return true;
    });
}

function sortNotifications(rows) {
    const list = [...rows];
    const direction = notificationSort.direction === 'asc' ? 1 : -1;
    const key = notificationSort.key || 'created_at_ny';
    list.sort((a, b) => {
        let av;
        let bv;
        if (key === 'created_at_ny' || key === 'last_occurrence_ny') {
            av = Date.parse(a.last_occurrence_ny || a.created_at_ny || '') || 0;
            bv = Date.parse(b.last_occurrence_ny || b.created_at_ny || '') || 0;
        } else if (key === '_count_1h' || key === 'occurrence_count') {
            av = Number(a[key] || 0);
            bv = Number(b[key] || 0);
        } else if (key === 'severity') {
            const rank = { critical: 4, high: 3, warning: 2, info: 1 };
            av = rank[a.severity] || 0;
            bv = rank[b.severity] || 0;
        } else {
            av = String(a[key] ?? '').toLowerCase();
            bv = String(b[key] ?? '').toLowerCase();
        }
        if (av < bv) return -1 * direction;
        if (av > bv) return 1 * direction;
        return 0;
    });
    return list;
}

function getProblemDevices(rows) {
    const severityRank = { critical: 4, high: 3, warning: 2, info: 1 };
    const tallies = new Map();
    rows.forEach((row) => {
        const serial = row.device_serial || row.equipment_id || 'Unknown Device';
        if (!tallies.has(serial)) {
            tallies.set(serial, {
                serial,
                count: 0,
                severity: row.severity || 'info',
                ip: row.ip_address || '',
                customer: row.customer_description || row.customer_code || '',
                model: row.model || serial,
                department: row.department || '',
                topCode: row.alert_code || ''
            });
        }
        const current = tallies.get(serial);
        current.count += Number(row.occurrence_count || row._count_1h || row._aggregatedTriggers || 1);
        if ((severityRank[row.severity] || 0) > (severityRank[current.severity] || 0)) {
            current.severity = row.severity;
            current.topCode = row.alert_code || current.topCode;
        }
    });
    return Array.from(tallies.values()).sort((a, b) => b.count - a.count).slice(0, 6);
}

function renderSeverityPill(severity) {
    const config = SEVERITY_CONFIG[severity] || SEVERITY_CONFIG.info;
    return `<span class="severity-pill severity-${escapeHtml(severity || 'info')}">${escapeHtml(config.label).toUpperCase()}</span>`;
}

function renderNotifications(notifications, staleMessage = '') {
    const container = scopedById('notifications-container');
    if (!container) return;

    const rows = Array.isArray(notifications) ? notifications : [];
    const visibleColumns = NOTIFICATION_COLUMNS.filter((column) => isNotificationColumnVisible(column.key));
    const problemDevices = getProblemDevices(rows);

    const problemStrip = problemDevices.length
        ? `<div class="alerts-problem-banner">
                <span class="alerts-problem-banner__label">Top Problem Devices</span>
                ${problemDevices.map((item) => `
                    <button class="alerts-problem-card alerts-problem-card--${escapeHtml(item.severity)}" type="button" data-device-filter="${escapeHtml(item.serial)}">
                        <span class="alerts-problem-card__name">${escapeHtml(item.model || item.serial)}</span>
                        ${item.department ? `<span class="alerts-problem-card__dept">${escapeHtml(item.department)}</span>` : ''}
                        <span class="alerts-problem-card__stats">${item.count} alerts &middot; ${escapeHtml(item.topCode || '')}</span>
                        ${renderSeverityPill(item.severity)}
                    </button>
                `).join('')}
            </div>`
        : '';

    const columnPanel = `
        <div id="notification-column-panel" class="alerts-column-panel" hidden>
            <div class="alerts-column-panel__title">Visible columns</div>
            ${NOTIFICATION_COLUMNS.filter((column) => !column.required).map((column) => `
                <label>
                    <input type="checkbox" class="notification-column-toggle" value="${escapeHtml(column.key)}" ${isNotificationColumnVisible(column.key) ? 'checked' : ''}>
                    <span>${escapeHtml(column.label)}</span>
                </label>
            `).join('')}
        </div>
    `;

    const controls = `
        <div class="notifications-toolbar">
            <div class="notifications-toolbar-left">
                ${staleMessage ? `<span class="notification-stale"><i class="fas fa-clock"></i> ${escapeHtml(staleMessage)}</span>` : ''}
            </div>
            <div class="notifications-toolbar-right">
                <span class="badge">${rows.length}${notificationTotalCount > rows.length ? ` of ${notificationTotalCount}` : ''} rows</span>
            </div>
        </div>
    `;

    const tableHtml = rows.length === 0
        ? '<div class="empty-state"><i class="fas fa-check-circle"></i> No active alerts for this customer</div>'
        : `
        <div class="notification-table-wrapper">
            <table class="table notification-table notification-table--compact">
                <thead>
                    <tr>
                        ${visibleColumns.map((column, idx) => {
                            const sortableClass = column.sortable ? 'is-sortable' : '';
                            const sortedClass = notificationSort.key === column.key ? `sorted-${notificationSort.direction}` : '';
                            const stickyClass = column.sticky ? `sticky-col sticky-col-${idx + 1}` : '';
                            const icon = notificationSort.key === column.key ? (notificationSort.direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
                            return `<th class="${sortableClass} ${sortedClass} ${stickyClass}" data-sort-key="${escapeHtml(column.key)}">${escapeHtml(column.label)}${column.sortable ? ` <i class="fas ${icon}"></i>` : ''}</th>`;
                        }).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${rows.map((row) => `
                        <tr class="alert-row-clickable" data-alert-id="${escapeHtml(row.id)}" data-serial="${escapeHtml(row.device_serial || '')}" data-device-id="${escapeHtml(row.device_id || '')}" data-customer-code="${escapeHtml(row.customer_code || notificationCustomerFilter || '')}">
                            ${visibleColumns.map((column, idx) => {
                                const stickyClass = column.sticky ? `sticky-col sticky-col-${idx + 1}` : '';
                                return `<td class="${stickyClass}">${renderNotificationCell(row, column.key)}</td>`;
                            }).join('')}
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;

    container.innerHTML = `<div class="notifications-layout">${controls}${columnPanel}${problemStrip}<div class="notifications-table-area">${tableHtml}</div></div>`;
    bindNotificationTableInteractions();
}

function renderNotificationCell(row, key) {
    if (key === 'severity') return renderSeverityPill(row.severity);
    if (key === 'device_serial') {
        const serial = row.device_serial || 'N/A';
        const customer = row.customer_code || notificationCustomerFilter || '';
        return `<button class="btn-link notification-device-link" type="button" data-serial="${escapeHtml(serial)}" data-device-id="${escapeHtml(row.device_id || row.device_identifier || '')}" data-customer-code="${escapeHtml(customer)}">${escapeHtml(serial)}</button>`;
    }
    if (key === 'ip_address') {
        if (!row.ip_address) return 'N/A';
        const href = /^https?:\/\//i.test(row.ip_address) ? row.ip_address : `http://${row.ip_address}`;
        return `<a href="${escapeHtml(href)}" target="_blank" rel="noopener">${escapeHtml(row.ip_address)}</a>`;
    }
    if (key === 'message') return `<span class="notification-message-cell">${escapeHtml(row.message || '')}</span>`;
    if (key === '_count_1h') return `<strong>${Number(row._count_1h || row._aggregatedTriggers || 0)}</strong>`;
    if (key === 'created_at_ny') return escapeHtml(formatTimestamp(row.created_at_ny));
    if (key === 'actions') {
        return row.status === 'active'
            ? `<div class="notification-actions-inline">
                    <button class="btn-icon" data-action="ack" data-id="${row.id}" title="Acknowledge"><i class="fas fa-check"></i></button>
                    <button class="btn-icon" data-action="dismiss" data-id="${row.id}" title="Dismiss"><i class="fas fa-times"></i></button>
               </div>`
            : `<span class="text-muted">${escapeHtml(row.status || '')}</span>`;
    }
    return escapeHtml(row[key] ?? '');
}

function openAlertDevice(serial, deviceId, customerCode) {
    const resolvedSerial = (serial || '').trim();
    const resolvedCustomer = (customerCode || notificationCustomerFilter || '').trim();
    if (!resolvedSerial && !deviceId) {
        return;
    }
    if (window.MPSM && typeof window.MPSM.openDeviceModal === 'function' && document.getElementById('device-modal')) {
        window.MPSM.openDeviceModal({
            deviceId: deviceId || null,
            serialNumber: resolvedSerial || null,
            customerCode: resolvedCustomer || null
        });
        return;
    }
    const params = new URLSearchParams({ tab: 'dashboard' });
    if (resolvedCustomer) {
        params.set('customerCode', resolvedCustomer);
    }
    window.open(`index.php?${params.toString()}`, '_blank', 'noopener');
}

function bindNotificationTableInteractions() {
    const container = scopedById('notifications-container');
    if (!container) return;

    container.querySelectorAll('th[data-sort-key]').forEach((header) => {
        header.addEventListener('click', () => {
            const key = header.getAttribute('data-sort-key');
            const column = NOTIFICATION_COLUMNS.find((item) => item.key === key);
            if (!column || !column.sortable) return;
            if (notificationSort.key === key) {
                notificationSort.direction = notificationSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                notificationSort = { key, direction: 'desc' };
            }
            saveNotificationSort();
            const rows = requestGuards.notifications.lastData?.rows || [];
            renderNotifications(sortNotifications(applyNotificationClientFilters(rows)));
        });
    });

    container.querySelectorAll('.notification-column-toggle').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const key = checkbox.value;
            notificationColumnVisibility[key] = checkbox.checked;
            saveNotificationColumns();
            const rows = requestGuards.notifications.lastData?.rows || [];
            renderNotifications(sortNotifications(applyNotificationClientFilters(rows)));
        });
    });

    container.querySelectorAll('.alerts-problem-card').forEach((card) => {
        card.addEventListener('click', () => {
            const serial = card.getAttribute('data-device-filter') || '';
            const row = (requestGuards.notifications.lastData?.rows || []).find((item) => (item.device_serial || item.equipment_id || '') === serial);
            openAlertDevice(serial, row?.device_id || row?.device_identifier || '', row?.customer_code || notificationCustomerFilter || '');
        });
    });

    const searchInput = scopedById('notif-table-search');
    if (searchInput) {
        searchInput.addEventListener('input', (event) => {
            notificationSearchFilter = event.target.value || '';
            saveNotificationFilters();
            const rows = requestGuards.notifications.lastData?.rows || [];
            renderNotifications(sortNotifications(applyNotificationClientFilters(rows)));
        });
    }

    container.querySelectorAll('.notification-device-link').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.stopPropagation();
            const serial = btn.getAttribute('data-serial') || '';
            const deviceId = btn.getAttribute('data-device-id') || '';
            const customerCode = btn.getAttribute('data-customer-code') || notificationCustomerFilter || '';
            openAlertDevice(serial, deviceId, customerCode);
        });
    });

    container.querySelectorAll('.notification-table a').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    container.querySelectorAll('.alert-row-clickable').forEach((row) => {
        row.addEventListener('click', () => {
            openAlertDevice(
                row.getAttribute('data-serial') || '',
                row.getAttribute('data-device-id') || '',
                row.getAttribute('data-customer-code') || notificationCustomerFilter || ''
            );
        });
    });

    container.querySelectorAll('button[data-action="ack"]').forEach((btn) => {
        btn.addEventListener('click', () => acknowledgeNotification(Number(btn.getAttribute('data-id'))));
    });
    container.querySelectorAll('button[data-action="dismiss"]').forEach((btn) => {
        btn.addEventListener('click', () => dismissNotification(Number(btn.getAttribute('data-id'))));
    });
}

// Group notifications by device + alert to prevent duplicates
function groupNotificationsForDisplay(notifications) {
    if (!notifications || notifications.length === 0) return [];

    const grouped = new Map();

    notifications.forEach((notif) => {
        const key = `${notif.device_serial || ''}|${notif.alert_code || ''}`;
        const existing = grouped.get(key);

        if (!existing) {
            // First occurrence of this key
            grouped.set(key, { ...notif, _aggregatedTriggers: 1 });
        } else {
            // Duplicate found: prefer higher priority and increment count
            if ((notif.priority || 0) > (existing.priority || 0)) {
                // Replace with higher priority notification, carry over count
                grouped.set(key, { ...notif, _aggregatedTriggers: (existing._aggregatedTriggers || 1) + 1 });
            } else {
                // Keep existing, increment count
                existing._aggregatedTriggers = (existing._aggregatedTriggers || 1) + 1;
                grouped.set(key, existing);
            }
        }
    });

    // Preserve order by severity/priority then time similar to API default
    return Array.from(grouped.values()).sort((a, b) => (b.priority || 0) - (a.priority || 0));
}

async function acknowledgeNotification(id) {
    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                action: 'acknowledge_notification',
                id: id.toString()
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to acknowledge notification');
        }

        showToast('Notification acknowledged', 'success');
        loadNotifications(true);
    } catch (error) {
        console.error('Error acknowledging notification:', error);
        showToast(error.message, 'error');
    }
}

async function dismissNotification(id) {
    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                action: 'dismiss_notification',
                id: id.toString()
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to dismiss notification');
        }

        showToast('Notification dismissed', 'success');
        loadNotifications(true);
    } catch (error) {
        console.error('Error dismissing notification:', error);
        showToast(error.message, 'error');
    }
}

// ========================================
// TAB 2: Notification Rules
// ========================================

async function loadRules(silent = false) {
    const container = scopedById('rules-container');
    if (!container) return;
    const guard = requestGuards.rules;
    if (guard.loading) return;

    if (!silent && !guard.loaded) {
        container.innerHTML = '<div class="loading">Loading rules...</div>';
    }

    const token = ++guard.token;
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), getRequestTimeout('rules', silent));
    guard.controller = controller;
    guard.loading = true;

    try {
        const response = await fetch('api/command-center.php?action=get_rules', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            signal: controller.signal
        });
        if (token !== guard.token) return;

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const data = await response.json();
        if (token !== guard.token) return;

        if (!data.success) {
            throw new Error(data.error || 'Failed to load rules');
        }

        guard.lastData = data.rules || [];
        guard.loaded = true;
        renderRules(data.rules || []);
    } catch (error) {
        if (token !== guard.token) return;
        console.error('Error loading rules:', error);
        if (Array.isArray(guard.lastData) && guard.lastData.length > 0) {
            renderRules(guard.lastData);
            return;
        }
        const errorMsg = error.name === 'AbortError' ? 'Request timed out' : error.message;
        container.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ${escapeHtml(errorMsg)}</div>`;
    } finally {
        clearTimeout(timeoutId);
        if (guard.controller === controller) guard.controller = null;
        guard.loading = false;
    }
}

function renderRules(rules) {
    const container = scopedById('rules-container');
    if (!container) return;

    if (rules.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-cog"></i> No notification rules configured. Create your first rule to get started!</div>';
        return;
    }

    const html = rules.map(rule => {
        const config = SEVERITY_CONFIG[rule.severity] || SEVERITY_CONFIG.info;
        const enabledClass = rule.enabled ? 'enabled' : 'disabled';

        return `
            <div class="rule-card ${enabledClass}" data-id="${rule.id}">
                <div class="rule-header">
                    <div class="rule-icon" style="color: ${config.color}">
                        <i class="fas fa-${config.icon}"></i>
                    </div>
                    <div class="rule-info">
                        <div class="rule-name">${escapeHtml(rule.name)}</div>
                        ${rule.description ? `<div class="rule-description">${escapeHtml(rule.description)}</div>` : ''}
                        <div class="rule-meta">
                            <span class="badge badge-${rule.severity}">${config.label}</span>
                            ${rule.last_triggered_at ? `<span class="rule-last-trigger"><i class="fas fa-bolt"></i> Last triggered: ${formatTimestamp(rule.last_triggered_at)}</span>` : '<span class="text-muted">Never triggered</span>'}
                            ${rule.trigger_count > 0 ? `<span class="rule-trigger-count"><i class="fas fa-chart-line"></i> ${rule.trigger_count} times</span>` : ''}
                        </div>
                    </div>
                    <div class="rule-actions">
                        <label class="toggle-switch" title="${rule.enabled ? 'Disable' : 'Enable'} rule">
                            <input type="checkbox" ${rule.enabled ? 'checked' : ''} onchange="toggleRule(${rule.id}, this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                        <button class="btn-icon" onclick="editRule(${rule.id})" title="Edit rule">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-danger" onclick="deleteRule(${rule.id})" title="Delete rule">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="rule-patterns">
                    ${rule.alert_code_pattern ? `<div class="pattern-tag"><i class="fas fa-tag"></i> Alert: ${escapeHtml(rule.alert_code_pattern)}</div>` : ''}
                    ${rule.device_serial_pattern ? `<div class="pattern-tag"><i class="fas fa-hdd"></i> Device: ${escapeHtml(rule.device_serial_pattern)}</div>` : ''}
                    ${rule.customer_code_pattern ? `<div class="pattern-tag"><i class="fas fa-building"></i> Customer: ${escapeHtml(rule.customer_code_pattern)}</div>` : ''}
                    ${rule.frequency_count && rule.frequency_window_hours ? `<div class="pattern-tag"><i class="fas fa-clock"></i> ${rule.frequency_count}x in ${rule.frequency_window_hours}h (${rule.frequency_type})</div>` : ''}
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = html;
}

async function toggleRule(id, enabled) {
    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                action: 'toggle_rule',
                id: id.toString()
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to toggle rule');
        }

        showToast(`Rule ${enabled ? 'enabled' : 'disabled'}`, 'success');
        loadRules(true);
    } catch (error) {
        console.error('Error toggling rule:', error);
        showToast(error.message, 'error');
        loadRules(true); // Reload to reset toggle state
    }
}

async function deleteRule(id) {
    if (!confirm('Are you sure you want to delete this rule? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                action: 'delete_rule',
                id: id.toString()
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to delete rule');
        }

        showToast('Rule deleted successfully', 'success');
        loadRules();
    } catch (error) {
        console.error('Error deleting rule:', error);
        showToast(error.message, 'error');
    }
}

function editRule(id) {
    // Find rule data
    fetch(`api/command-center.php?action=get_rules`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const rule = (data.rules || []).find(r => Number(r.id) === Number(id));
            if (rule) {
                openRuleModal(rule);
            } else {
                console.warn('Rule not found for edit:', id);
            }
        }
    })
    .catch(error => {
        console.error('Error loading rule:', error);
        showToast('Failed to load rule data', 'error');
    });
}

// Rule Modal Management
function openRuleModal(rule = null) {
    const modal = scopedById('rule-modal');
    const form = scopedById('rule-form');
    const title = scopedById('rule-modal-title');
    if (!modal || !form || !title) return;

    // Reset form
    form.reset();

    // Ensure pattern suggestions are loaded and datalists populated
    ensurePatternSuggestions().then(populatePatternDatalists).catch(() => {});

    if (rule) {
        // Edit mode
        title.textContent = 'Edit Notification Rule';
        scopedById('rule-id').value = rule.id;
        scopedById('rule-name').value = rule.name || '';
        scopedById('rule-description').value = rule.description || '';
        scopedById('rule-severity').value = rule.severity || 'warning';
        scopedById('rule-alert-pattern').value = rule.alert_code_pattern || '';
        scopedById('rule-device-pattern').value = rule.device_serial_pattern || '';
        scopedById('rule-customer-pattern').value = rule.customer_code_pattern || '';
        scopedById('rule-freq-count').value = rule.frequency_count || '';
        scopedById('rule-freq-window').value = rule.frequency_window_hours || '';
        scopedById('rule-freq-type').value = rule.frequency_type || 'same_device';
        scopedById('rule-title').value = rule.notification_title || '';
        scopedById('rule-message').value = rule.notification_message || '';
        scopedById('rule-show-dashboard').checked = rule.show_dashboard == 1;
        scopedById('rule-auto-dismiss').value = rule.auto_dismiss_hours || '';
    } else {
        // Create mode
        title.textContent = 'Create Notification Rule';
        scopedById('rule-id').value = '';
        scopedById('rule-show-dashboard').checked = true;
    }

    modal.style.display = 'flex';
}

function closeRuleModal() {
    const modal = scopedById('rule-modal');
    if (!modal) return;
    modal.style.display = 'none';
}

async function handleRuleSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const ruleId = formData.get('id');
    const action = ruleId ? 'update_rule' : 'create_rule';

    // Build request body
    const body = {
        action: action,
        name: formData.get('name'),
        description: formData.get('description') || null,
        severity: formData.get('severity'),
        enabled: 1, // Default to enabled (no UI toggle currently)
        alert_code_pattern: formData.get('alert_code_pattern') || null,
        device_serial_pattern: formData.get('device_serial_pattern') || null,
        customer_code_pattern: formData.get('customer_code_pattern') || null,
        frequency_count: formData.get('frequency_count') || null,
        frequency_window_hours: formData.get('frequency_window_hours') || null,
        frequency_type: formData.get('frequency_type') || 'same_device',
        notification_title: formData.get('notification_title') || null,
        notification_message: formData.get('notification_message') || null,
        show_dashboard: formData.get('show_dashboard') ? 1 : 0,
        auto_dismiss_hours: formData.get('auto_dismiss_hours') || null
    };

    if (ruleId) {
        body.id = ruleId;
    }

    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to save rule');
        }

        showToast(`Rule ${ruleId ? 'updated' : 'created'} successfully`, 'success');
        closeRuleModal();
        loadRules();
    } catch (error) {
        console.error('Error saving rule:', error);
        showToast(error.message, 'error');
    }
}

// ========================================
// TAB 3: Alert Statistics
// ========================================

async function loadStatistics(silent = false) {
    const container = scopedById('statistics-container');
    if (!container) return;
    const guard = requestGuards.statistics;
    if (guard.loading) return;

    if (!silent && !guard.loaded) {
        container.innerHTML = '<div class="loading">Loading statistics...</div>';
    }

    const token = ++guard.token;
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), getRequestTimeout('statistics', silent));
    guard.controller = controller;
    guard.loading = true;

    try {
        const response = await fetch('api/command-center.php?action=get_aggregations&group_by=alert_only', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            signal: controller.signal
        });
        if (token !== guard.token) return;

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const data = await response.json();
        if (token !== guard.token) return;

        if (!data.success) {
            throw new Error(data.error || 'Failed to load statistics');
        }

        guard.lastData = data.aggregations || [];
        guard.loaded = true;
        renderStatistics(data.aggregations || []);
    } catch (error) {
        if (token !== guard.token) return;
        console.error('Error loading statistics:', error);
        if (Array.isArray(guard.lastData) && guard.lastData.length > 0) {
            renderStatistics(guard.lastData);
            return;
        }
        const errorMsg = error.name === 'AbortError' ? 'Request timed out' : error.message;
        container.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ${escapeHtml(errorMsg)}</div>`;
    } finally {
        clearTimeout(timeoutId);
        if (guard.controller === controller) guard.controller = null;
        guard.loading = false;
    }
}

function renderStatistics(aggregations) {
    const container = scopedById('statistics-container');
    if (!container) return;

    if (!Array.isArray(aggregations) || aggregations.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-chart-bar"></i> No alert data available</div>';
        return;
    }

    const sortBy = scopedById('stats-sort')?.value || 'recent';
    const sorted = [...aggregations].sort((a, b) => {
        if (sortBy === 'recent') return new Date(b.last_occurrence_ny) - new Date(a.last_occurrence_ny);
        if (sortBy === 'frequent') return (b.occurrence_count || 0) - (a.occurrence_count || 0);
        if (sortBy === 'critical') {
            // Sort by most active in last hour (critical activity indicator)
            const bScore = (b.count_1h || 0) * 100 + (b.count_24h || 0);
            const aScore = (a.count_1h || 0) * 100 + (a.count_24h || 0);
            return bScore - aScore;
        }
        return 0;
    });

    const html = `
        <div class="stats-legend"><i class="fas fa-info-circle"></i> Counts show alert occurrences in the last 1h, 24h, 7d, and 30d windows.</div>
        <div class="stats-list">
            ${sorted.map(agg => `
                <div class="stat-row">
                    <div class="stat-main">
                        <div class="stat-title truncate">${escapeHtml(agg.alert_display_name || 'Unknown alert')} (${escapeHtml(agg.alert_code || '')})</div>
                        <div class="stat-subtitle truncate">${escapeHtml(agg.alert_category || '')}</div>
                    </div>
                    <div class="stat-badges">
                        <span class="count-badge"><span class="count-label">1h</span><span class="count-value">${agg.count_1h || 0}</span></span>
                        <span class="count-badge"><span class="count-label">24h</span><span class="count-value">${agg.count_24h || 0}</span></span>
                        <span class="count-badge"><span class="count-label">7d</span><span class="count-value">${agg.count_7d || 0}</span></span>
                        <span class="count-badge"><span class="count-label">30d</span><span class="count-value">${agg.count_30d || 0}</span></span>
                    </div>
                    <div class="stat-meta">
                        <span class="truncate"><i class="fas fa-microchip"></i> ${agg.device_count || 0} devices</span>
                        <span class="truncate"><i class="fas fa-chart-line"></i> ${agg.occurrence_count || 0} total</span>
                        <span class="truncate"><i class="fas fa-clock"></i> ${formatTimestamp(agg.last_occurrence_ny)}</span>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    container.innerHTML = html;
}

// ========================================
// Utility Functions
// ========================================

function formatTimestamp(timestamp) {
    if (!timestamp) return 'N/A';

    const date = new Date(timestamp + ' GMT-0500'); // Assuming NY time
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`;

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    });
}

function escapeHtml(text) {
    if (!text) return '';
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
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${escapeHtml(message)}</span>
        </div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Load and cache alert aggregations so notifications can show accurate 1h tallies
async function ensureAggregationsMap() {
    const now = Date.now();
    if (_aggMap && (now - _aggMapLoadedAt) < 30000) { // 30s cache
        return _aggMap;
    }

    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), getRequestTimeout('statistics', true));
        const res = await fetch('api/command-center.php?action=get_aggregations&limit=1000', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        const data = res.ok ? await res.json() : { success: false };
        const map = new Map();
        if (data.success && Array.isArray(data.aggregations)) {
            data.aggregations.forEach(a => {
                const key = `${a.device_serial || ''}|${a.alert_code || ''}`;
                map.set(key, a);
            });
        }
        _aggMap = map;
        _aggMapLoadedAt = now;
        return _aggMap;
    } catch (_e) {
        _aggMap = new Map();
        _aggMapLoadedAt = now;
        return _aggMap;
    }
}

// ================================
// Pattern Suggestions (Datalists)
// ================================
let _patternLoaded = false;
let _patternSuggestions = { alerts: [], devices: [], customers: [] };

async function ensurePatternSuggestions() {
    if (_patternLoaded) return _patternSuggestions;

    try {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), getRequestTimeout('customers', true));

        // Fetch alert definitions, recent aggregations, and customers (for names)
        const [defsRes, aggsRes, custRes] = await Promise.all([
            fetch('api/command-center.php?action=get_alert_definitions&limit=500', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', signal: controller.signal }),
            fetch('api/command-center.php?action=get_aggregations&limit=500', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', signal: controller.signal }),
            fetch('api/command-center.php?action=get_customers', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', signal: controller.signal })
        ]);

        clearTimeout(timeout);

        const defs = defsRes.ok ? await defsRes.json() : { success: false };
        const aggs = aggsRes.ok ? await aggsRes.json() : { success: false };
        const cust = custRes.ok ? await custRes.json() : { success: false };

        const alertSet = new Map();
        if (defs.success && Array.isArray(defs.definitions)) {
            defs.definitions.forEach(d => {
                if (d.alert_code) alertSet.set(d.alert_code, d.display_name || d.alert_code);
            });
        }
        if (aggs.success && Array.isArray(aggs.aggregations)) {
            aggs.aggregations.forEach(a => {
                if (a.alert_code && !alertSet.has(a.alert_code)) alertSet.set(a.alert_code, a.alert_display_name || a.alert_code);
            });
        }

        const deviceSet = new Set();
        const customerPairs = new Map(); // code => name
        if (aggs.success && Array.isArray(aggs.aggregations)) {
            aggs.aggregations.forEach(a => {
                if (a.device_serial) deviceSet.add(a.device_serial);
                if (a.customer_code && !customerPairs.has(a.customer_code)) customerPairs.set(a.customer_code, a.customer_description || '');
            });
        }
        if (cust.success && Array.isArray(cust.customers)) {
            cust.customers.forEach(c => {
                const code = c.customer_code || c.Code || c.code;
                const name = c.customer_description || c.Description || c.description || '';
                if (code && !customerPairs.has(code)) customerPairs.set(code, name);
            });
        }

        _patternSuggestions = {
            alerts: Array.from(alertSet.entries()).map(([code, name]) => ({ code, name })),
            devices: Array.from(deviceSet.values()),
            customers: Array.from(customerPairs.entries()).map(([code, name]) => ({ code, name }))
        };
        _patternLoaded = true;
        return _patternSuggestions;
    } catch (_e) {
        // Ignore; leave suggestions empty
        _patternLoaded = true;
        return _patternSuggestions;
    }
}

function populatePatternDatalists() {
    try {
        const alertList = scopedById('alert-code-options');
        const deviceList = scopedById('device-serial-options');
        const customerList = scopedById('customer-code-options');
        if (!alertList || !deviceList || !customerList) return;

        alertList.innerHTML = _patternSuggestions.alerts.map(a => `<option value="${escapeHtml(a.code)}">${escapeHtml(a.code)} - ${escapeHtml(a.name)}</option>`).join('');
        deviceList.innerHTML = _patternSuggestions.devices.map(d => `<option value="${escapeHtml(d)}"></option>`).join('');
        customerList.innerHTML = _patternSuggestions.customers.map(c => `<option value="${escapeHtml(c.code)}">${escapeHtml(c.name || c.code)}</option>`).join('');
    } catch (_e) {
        // no-op
    }
}

// Populate Active Notifications customer filter with Names (value=Code)
async function loadCustomerOptionsForCC() {
    const notifSelect = scopedById('notification-customer-filter');
    const panelSelect = scopedById('cc-panel-customer');
    if (!notifSelect && !panelSelect) return;

    const guard = requestGuards.customers;
    if (guard.loading) return;

    const applyOptions = (customers) => {
        const normalized = Array.isArray(customers) ? customers : [];
        const hydrate = (selectEl) => {
            if (!selectEl) return;
            while (selectEl.options.length > 1) selectEl.remove(1);
            normalized.forEach((item) => {
                const code = item.customer_code || item.Code || item.code;
                const name = item.customer_description || item.Description || item.description || code;
                if (!code) return;
                const opt = document.createElement('option');
                opt.value = code;
                opt.textContent = name;
                selectEl.appendChild(opt);
            });
        };
        hydrate(notifSelect);
        hydrate(panelSelect);

        const defaultCode = (notificationCustomerFilter || (window.currentCustomerCode || '')).trim();
        if (defaultCode) {
            const hasDefaultInList = normalized.some((item) => String(item.customer_code) === defaultCode);
            if (!hasDefaultInList) {
                [notifSelect, panelSelect].forEach((selectEl) => {
                    if (!selectEl) return;
                    const opt = document.createElement('option');
                    opt.value = defaultCode;
                    opt.textContent = defaultCode;
                    selectEl.appendChild(opt);
                });
            }
        }
        if (defaultCode) {
            if (notifSelect) notifSelect.value = defaultCode;
            if (panelSelect) panelSelect.value = defaultCode;
            notificationCustomerFilter = defaultCode;
        }
    };

    let timeoutId = null;
    const parseCustomerPayload = (data) => {
        if (!data || !Array.isArray(data.customers)) {
            return [];
        }
        return data.customers
            .map((item) => ({
                customer_code: item.customer_code || item.Code || item.code || '',
                customer_description: item.customer_description || item.Description || item.description || item.customer_code || item.Code || item.code || ''
            }))
            .filter((item) => item.customer_code);
    };
    try {
        if (guard.lastData && Array.isArray(guard.lastData.customers) && guard.lastData.customers.length > 0) {
            applyOptions(guard.lastData.customers);
            return;
        }

        const token = ++guard.token;
        const controller = new AbortController();
        timeoutId = setTimeout(() => controller.abort(), getRequestTimeout('customers', false));
        guard.controller = controller;
        guard.loading = true;

        const res = await fetch('api/command-center.php?action=get_customers', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            signal: controller.signal
        });
        if (token !== guard.token) return;
        let data = res.ok ? await res.json() : { success: false };
        let normalized = parseCustomerPayload(data);

        if (!normalized.length) {
            const fallbackRes = await fetch('api/get-customers.php', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
                signal: controller.signal
            });
            if (token !== guard.token) return;
            data = fallbackRes.ok ? await fallbackRes.json() : { success: false };
            normalized = parseCustomerPayload(data);
        }

        if (token !== guard.token) return;
        if (normalized.length) {
            guard.lastData = { customers: normalized };
            guard.loaded = true;
            applyOptions(normalized);
        }
    } catch (e) {
        console.warn('Failed to load customers for filter:', e);
    } finally {
        if (timeoutId) clearTimeout(timeoutId);
        guard.loading = false;
        guard.controller = null;
    }
}

/*
CHANGELOG
2025-11-26 Claude
- Fixed trigger count aggregation: Changed from Math.max() to COUNT duplicates to show accurate occurrence numbers.

2025-11-26 Codex
- Collapsed duplicate notifications (same device + alert) into a single card; use MAX trigger count to avoid inflation.
- Switched "Alert Aggregations" to a list layout to prevent overflow and improve readability.
- Added searchable pattern suggestions (datalists) for alert codes, device serials, and customer codes in the rule modal.
- Show accurate 1h tallies in notifications using get_aggregations, with 30s client-side cache.
*/


// ========================================
// TAB: Panel Stream (Live Messages)
// ========================================
let panelTimerId = null;
let panelCache = [];
let panelOffset = 0;
let panelHasNext = false;

function initPanelTab() {
    const refreshBtn = scopedById('cc-panel-refresh');
    const limitSel = scopedById('cc-panel-limit');
    const hoursSel = scopedById('cc-panel-hours');
    const customerInput = scopedById('cc-panel-customer');
    const prevBtn = scopedById('cc-panel-prev');
    const nextBtn = scopedById('cc-panel-next');
    const pageBadge = scopedById('cc-panel-page');

    if (!refreshBtn || !limitSel || !hoursSel) return;

    refreshBtn.onclick = () => { panelOffset = 0; loadPanelMessages(); };
    limitSel.onchange = () => { panelOffset = 0; loadPanelMessages(); };
    hoursSel.onchange = () => { panelOffset = 0; loadPanelMessages(); };
    if (customerInput) {
        customerInput.onchange = () => { panelOffset = 0; loadPanelMessages(); syncNotificationCustomer(customerInput.value); };
    }

    if (customerInput && notificationCustomerFilter) {
        customerInput.value = notificationCustomerFilter;
    }

    if (prevBtn) {
        prevBtn.onclick = () => {
            if (panelOffset > 0) {
                panelOffset = Math.max(0, panelOffset - getPanelLimit());
                loadPanelMessages();
            }
        };
    }

    if (nextBtn) {
        nextBtn.onclick = () => {
            if (panelHasNext) {
                panelOffset += getPanelLimit();
                loadPanelMessages();
            }
        };
    }

    if (pageBadge) {
        pageBadge.textContent = 'Page 1';
    }

    loadCustomerOptionsForCC();
    loadPanelMessages();
}

function startPanelAutoRefresh() {
    stopPanelAutoRefresh();
    panelTimerId = setInterval(() => {
        if (!isMounted) {
            return;
        }
        if (currentTab === 'panel') {
            loadPanelMessages();
        }
    }, 30000);
}

function stopPanelAutoRefresh() {
    if (panelTimerId) {
        clearInterval(panelTimerId);
        panelTimerId = null;
    }
}

async function loadPanelMessages() {
    const tbody = scopedById('cc-panel-tbody');
    const last = scopedById('cc-panel-last-refresh');
    const limitSel = scopedById('cc-panel-limit');
    const hoursSel = scopedById('cc-panel-hours');
    const customerInput = scopedById('cc-panel-customer');
    const pageBadge = scopedById('cc-panel-page');
    const prevBtn = scopedById('cc-panel-prev');
    const nextBtn = scopedById('cc-panel-next');
    if (!tbody) return;
    const guard = requestGuards.panel;
    if (guard.loading) {
        return;
    }

    const limit = getPanelLimit();
    const params = new URLSearchParams({ limit: String(limit), offset: String(panelOffset) });
    if (hoursSel?.value) params.set('hours', String(hoursSel.value));
    const customerCode = (customerInput?.value || notificationCustomerFilter || '').trim();
    if (customerCode) params.set('customerCode', customerCode);

    panelHasNext = false;
    const token = ++guard.token;
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), getRequestTimeout('panel', false));
    guard.controller = controller;
    guard.loading = true;

    try {
        const res = await fetch('api/get-panel-messages.php?' + params.toString(), {
            credentials: 'same-origin', headers: { 'Accept': 'application/json' }, signal: controller.signal
        });
        if (token !== guard.token) return;
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (token !== guard.token) return;
        if (!data.success) throw new Error(data.error || 'Failed to load panel messages');
        panelCache = data.messages || [];
        guard.lastData = panelCache;
        guard.loaded = true;
        panelHasNext = panelCache.length === limit;
        renderPanelRows(panelCache);
        if (last) last.textContent = 'Last refresh: ' + new Date().toLocaleTimeString() + ' • Auto 30s';
        if (pageBadge) {
            const page = Math.floor(panelOffset / limit) + 1;
            pageBadge.textContent = `Page ${page}`;
        }
        if (prevBtn) prevBtn.disabled = panelOffset === 0;
        if (nextBtn) nextBtn.disabled = !panelHasNext;
    } catch (err) {
        if (token !== guard.token) return;
        if (Array.isArray(guard.lastData) && guard.lastData.length > 0) {
            renderPanelRows(guard.lastData);
            if (last) last.textContent = 'Showing cached data • refresh retry pending';
            return;
        }
        tbody.innerHTML = `<tr><td colspan="6"><i class="fas fa-exclamation-triangle"></i> ${escapeHtml(err.message || 'Failed to load panel stream')}</td></tr>`;
        if (last) last.textContent = 'Error';
        if (prevBtn) prevBtn.disabled = true;
        if (nextBtn) nextBtn.disabled = true;
    } finally {
        clearTimeout(timeoutId);
        if (guard.controller === controller) guard.controller = null;
        guard.loading = false;
    }
}

function getPanelLimit() {
    const limitSel = scopedById('cc-panel-limit');
    const limit = parseInt(limitSel?.value || '200', 10);
    return Number.isNaN(limit) ? 200 : limit;
}

function renderPanelRows(rows) {
    const tbody = scopedById('cc-panel-tbody');
    if (!tbody) return;
    if (!rows || rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6">No panel messages captured yet.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(row => {
        const customer = [
            row.customer_code ? `<strong>${escapeHtml(row.customer_code)}</strong>` : '',
            row.customer_description ? `<div>${escapeHtml(row.customer_description)}</div>` : ''
        ].join('');
        const displayName = row.display_name || row.panel_configuration || row.maintenance_alert_code || 'Alert';
        const serial = row.device_serial || '';
        const deviceCell = serial
            ? `<button class="btn-link notification-device-link panel-device-link" type="button" data-action="open-device" data-serial="${escapeHtml(serial)}" data-device-id="${escapeHtml(row.equipment_id || '')}" data-customer-code="${escapeHtml(row.customer_code || notificationCustomerFilter || '')}">${escapeHtml(serial)}</button>`
            : '';
        const ipCell = row.ip_address
            ? `<div><a href="${escapeHtml(/^https?:\/\//i.test(row.ip_address) ? row.ip_address : `http://${row.ip_address}`)}" target="_blank" rel="noopener">${escapeHtml(row.ip_address)}</a></div>`
            : '';
        const alertHtml = `
            <div><strong>${escapeHtml(displayName)}</strong></div>
            ${row.maintenance_alert_code ? `<div style="font-size:.85em;color:#64748b;">Code: ${escapeHtml(row.maintenance_alert_code)}</div>` : ''}
            ${row.department ? `<div style=\"color:#334155;\">Dept: ${escapeHtml(row.department)}</div>` : ''}
        `;
        const received = row.received_at ? new Date(row.received_at).toLocaleString() : '';
        return `
            <tr data-id="${row.id}">
                <td>${received}</td>
                <td>${customer || ''}</td>
                <td>${deviceCell}${ipCell}</td>
                <td>${alertHtml}</td>
                <td>${escapeHtml(row.panel_configuration || '')}</td>
                <td><button class="btn btn-secondary btn-small" data-action="view-payload"><i class="fas fa-eye"></i> View</button></td>
            </tr>
        `;
    }).join('');

    tbody.onclick = (ev) => {
        const deviceBtn = ev.target.closest('button[data-action="open-device"]');
        if (deviceBtn) {
            const serial = deviceBtn.getAttribute('data-serial') || '';
            const deviceId = deviceBtn.getAttribute('data-device-id') || '';
            const customerCode = deviceBtn.getAttribute('data-customer-code') || notificationCustomerFilter || '';
            if (window.MPSM && typeof window.MPSM.openDeviceModal === 'function' && document.getElementById('device-modal')) {
                window.MPSM.openDeviceModal({
                    deviceId: deviceId || null,
                    serialNumber: serial || null,
                    customerCode: customerCode || null
                });
            } else {
                const target = customerCode
                    ? `index.php?tab=dashboard&customerCode=${encodeURIComponent(customerCode)}`
                    : 'index.php?tab=dashboard';
                window.open(target, '_blank', 'noopener');
            }
            return;
        }
        const btn = ev.target.closest('button[data-action="view-payload"]');
        if (!btn) return;
        const tr = btn.closest('tr');
        const id = tr?.getAttribute('data-id');
        if (id) ccShowPanelPayload(id);
    };
}

function ccShowPanelPayload(id) {
    const modal = buildPanelModal();
    const viewer = modal.querySelector('#cc-panel-payload');
    const message = panelCache.find(r => String(r.id) === String(id));
    viewer.textContent = message?.payload ? (typeof message.payload === 'object' ? JSON.stringify(message.payload, null, 2) : String(message.payload)) : 'No payload available';
    modal.classList.add('active');
}

function buildPanelModal() {
    let modal = document.getElementById('cc-panel-modal');
    if (modal) return modal;
    modal = document.createElement('div');
    modal.id = 'cc-panel-modal';
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Payload</h3>
                <button id="cc-panel-modal-close" class="btn-icon" title="Close"><i class="fas fa-times"></i></button>
            </div>
            <pre id="cc-panel-payload" class="payload-viewer">{}</pre>
        </div>
    `;
    document.body.appendChild(modal);
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('active'); });
    modal.querySelector('#cc-panel-modal-close')?.addEventListener('click', () => modal.classList.remove('active'));
    return modal;
}// ========================================
// TAB: Alert Definitions (Labels)
// ========================================
const DEFINITIONS_PAGE_SIZE = 200;
let definitionsOffset = 0;

async function loadDefinitions(silent = false, append = false) {
    const container = scopedById('definitions-container');
    if (!container) return;
    const guard = requestGuards.definitions;
    if (guard.loading && !append) return;
    if (!append) {
        definitionsOffset = 0;
    }
    if (!append && !silent && !guard.loaded) {
        container.innerHTML = '<div class="loading">Loading alert labels...</div>';
    }
    const token = ++guard.token;
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), getRequestTimeout('definitions', silent));
    guard.controller = controller;
    guard.loading = true;
    try {
        const params = new URLSearchParams({ action: 'get_alert_definitions', limit: String(DEFINITIONS_PAGE_SIZE), offset: String(definitionsOffset) });
        const res = await fetch('api/command-center.php?' + params.toString(), {
            credentials: 'same-origin',
            headers: { 'Accept':'application/json' },
            signal: controller.signal
        });
        if (token !== guard.token) return;
        const data = await res.json();
        if (token !== guard.token) return;
        if (!data.success) throw new Error(data.error || 'Failed to load alert labels');
        const defs = data.definitions || [];
        guard.loaded = true;
        guard.lastData = defs;

        let table = container.querySelector('table');
        if (!table || !append) {
            container.innerHTML = `
                <div class="table-wrapper">
                    <table class="table">
                        <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Severity</th><th>Actions</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="definitions-actions">
                    <button id="definitions-load-more" class="btn btn-secondary" style="display:none;">Load More</button>
                </div>
            `;
            table = container.querySelector('table');
        }

        const tbody = table.querySelector('tbody');
        if (!append) {
            tbody.innerHTML = '';
        }

        tbody.insertAdjacentHTML('beforeend', defs.map(d => `
            <tr data-definition-id="${d.id}">
                <td><code>${escapeHtml(d.alert_code)}</code></td>
                <td>
                    <input type="text"
                           class="inline-edit-input"
                           value="${escapeHtml(d.display_name || '')}"
                           data-field="display_name"
                           data-definition-id="${d.id}"
                           placeholder="Display name">
                </td>
                <td>
                    <input type="text"
                           class="inline-edit-input"
                           value="${escapeHtml(d.category || '')}"
                           data-field="category"
                           data-definition-id="${d.id}"
                           placeholder="Category (e.g. Paper, Sensor)">
                </td>
                <td>
                    <select class="inline-edit-select"
                            data-field="severity_override"
                            data-definition-id="${d.id}">
                        <option value="">-- None --</option>
                        <option value="info" ${d.severity_override === 'info' ? 'selected' : ''}>Info</option>
                        <option value="warning" ${d.severity_override === 'warning' ? 'selected' : ''}>Warning</option>
                        <option value="high" ${d.severity_override === 'high' ? 'selected' : ''}>High</option>
                        <option value="critical" ${d.severity_override === 'critical' ? 'selected' : ''}>Critical</option>
                    </select>
                </td>
                <td class="status-cell">
                    <span class="save-status" style="display:none;">
                        <i class="fas fa-check-circle" style="color: green;"></i> Saved
                    </span>
                </td>
            </tr>
        `).join(''));

        // Attach inline edit handlers
        attachInlineEditHandlers(tbody);

        definitionsOffset += defs.length;

        const loadMoreBtn = scopedById('definitions-load-more');
        if (loadMoreBtn) {
            if (defs.length >= DEFINITIONS_PAGE_SIZE) {
                loadMoreBtn.style.display = 'inline-flex';
                loadMoreBtn.onclick = () => loadDefinitions(true, true);
            } else {
                loadMoreBtn.style.display = 'none';
            }
        }
    } catch (err) {
        if (token !== guard.token) return;
        if (Array.isArray(guard.lastData) && guard.lastData.length > 0) {
            // Keep existing table on refresh failures when stale data exists.
            return;
        }
        container.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ${escapeHtml(err.message)}</div>`;
    } finally {
        clearTimeout(timeoutId);
        if (guard.controller === controller) guard.controller = null;
        guard.loading = false;
    }
}

function openDefinitionModal(definition = null) {
    const modal = scopedById('definition-modal');
    const form = scopedById('definition-form');
    const title = scopedById('definition-modal-title');
    if (!modal || !form || !title) return;

    form.reset();

    if (definition) {
        title.textContent = 'Edit Alert Label';
        scopedById('definition-id').value = definition.id;
        scopedById('definition-alert-code').value = definition.alert_code || '';
        scopedById('definition-display-name').value = definition.display_name || '';
        scopedById('definition-category').value = definition.category || '';
        scopedById('definition-severity').value = definition.severity_override || '';
        scopedById('definition-description').value = definition.description || '';
    } else {
        title.textContent = 'Create Alert Label';
        scopedById('definition-id').value = '';
    }

    modal.style.display = 'flex';
}

function closeDefinitionModal() {
    const modal = scopedById('definition-modal');
    if (!modal) return;
    modal.style.display = 'none';
}

async function editDefinition(id) {
    try {
        const response = await fetch(`api/command-center.php?action=get_alert_definitions`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        if (data.success) {
            const definition = (data.definitions || []).find(d => Number(d.id) === Number(id));
            if (definition) {
                openDefinitionModal(definition);
            } else {
                showToast('Alert label not found', 'error');
            }
        }
    } catch (error) {
        console.error('Error loading definition:', error);
        showToast('Failed to load alert label', 'error');
    }
}

async function deleteDefinition(id, alertCode) {
    if (!confirm(`Delete alert label "${alertCode}"? This cannot be undone.`)) {
        return;
    }

    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'delete_alert_definition',
                id: id
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to delete alert label');
        }

        showToast('Alert label deleted successfully', 'success');
        loadDefinitions();
    } catch (error) {
        console.error('Error deleting definition:', error);
        showToast(error.message, 'error');
    }
}

// Inline Edit Handlers for Alert Labels
function attachInlineEditHandlers(tbody) {
    const inputs = tbody.querySelectorAll('.inline-edit-input');
    const selects = tbody.querySelectorAll('.inline-edit-select');

    // Debounced save function
    let saveTimeout;
    const debouncedSave = (element) => {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => saveInlineEdit(element), 800); // Save 800ms after user stops typing
    };

    inputs.forEach(input => {
        input.addEventListener('input', () => debouncedSave(input));
        input.addEventListener('blur', () => saveInlineEdit(input));
    });

    selects.forEach(select => {
        select.addEventListener('change', () => saveInlineEdit(select));
    });
}

async function saveInlineEdit(element) {
    const definitionId = element.dataset.definitionId;
    const field = element.dataset.field;
    const value = element.value.trim();

    // Get the row to show status
    const row = element.closest('tr');
    const statusSpan = row.querySelector('.save-status');

    try {
        // Get current definition data
        const response = await fetch(`api/command-center.php?action=get_alert_definitions`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();

        if (!data.success) {
            throw new Error('Failed to load current data');
        }

        const definition = data.definitions.find(d => String(d.id) === String(definitionId));
        if (!definition) {
            throw new Error('Definition not found');
        }

        // Update only the changed field
        const body = {
            action: 'update_alert_definition',
            id: definitionId,
            alert_code: definition.alert_code,
            display_name: field === 'display_name' ? value : definition.display_name,
            category: field === 'category' ? value : definition.category,
            severity_override: field === 'severity_override' ? value : definition.severity_override,
            description: definition.description
        };

        const updateResponse = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });

        const updateData = await updateResponse.json();

        if (!updateData.success) {
            throw new Error(updateData.error || 'Failed to save');
        }

        // Show success indicator
        if (statusSpan) {
            statusSpan.style.display = 'inline-block';
            setTimeout(() => {
                statusSpan.style.display = 'none';
            }, 2000);
        }

        // Refresh pattern suggestions for rule modal
        _patternLoaded = false;
        _patternSuggestions = { alerts: [], devices: [], customers: [] };

    } catch (error) {
        console.error('Error saving inline edit:', error);
        showToast(error.message || 'Failed to save changes', 'error');

        // Reload to revert changes
        loadDefinitions(true);
    }
}

async function handleDefinitionSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const definitionId = formData.get('id');
    const action = definitionId ? 'update_alert_definition' : 'create_alert_definition';

    const body = {
        action: action,
        alert_code: formData.get('alert_code'),
        display_name: formData.get('display_name'),
        category: formData.get('category') || null,
        severity_override: formData.get('severity_override') || null,
        description: formData.get('description') || null
    };

    if (definitionId) {
        body.id = definitionId;
    }

    try {
        const response = await fetch('api/command-center.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to save alert label');
        }

        showToast(`Alert label ${definitionId ? 'updated' : 'created'} successfully`, 'success');
        closeDefinitionModal();
        loadDefinitions();

        // Refresh pattern suggestions for rule modal
        _patternLoaded = false;
        _patternSuggestions = { alerts: [], devices: [], customers: [] };
        ensurePatternSuggestions().then(populatePatternDatalists).catch(() => {});
    } catch (error) {
        console.error('Error saving definition:', error);
        showToast(error.message, 'error');
    }
}

window.AlertCenter = {
    mount: mountAlertCenter,
    unmount: unmountAlertCenter,
    refresh: () => loadCurrentTab(),
    setCustomerCode: (customerCode) => {
        syncNotificationCustomer(customerCode || '');
        if (isMounted) {
            panelOffset = 0;
            resetNotificationData();
            loadCurrentTab();
        }
    },
    getState: () => ({
        mounted: isMounted,
        currentTab,
        customerCode: notificationCustomerFilter
    })
};

document.addEventListener('DOMContentLoaded', () => {
    const bootstrap = window.ALERT_CENTER_BOOTSTRAP || {};
    const standalone = document.body?.dataset.alertCenterStandalone === '1';
    if (bootstrap.autoMount || standalone) {
        mountAlertCenter(bootstrap);
    }
});

/*
CHANGELOG
2025-11-26 Claude
- Fixed trigger count aggregation: Changed from Math.max() to COUNT duplicates to show accurate occurrence numbers.

2025-11-26 Codex
- Collapsed duplicate notifications (same device + alert) into a single card; use MAX trigger count to avoid inflation.
- Switched "Alert Aggregations" to a list layout to prevent overflow and improve readability.
- Added searchable pattern suggestions (datalists) for alert codes, device serials, and customer codes in the rule modal.
- Show accurate 1h tallies in notifications using get_aggregations, with 30s client-side cache.
- Unified Command Center: added Panel Stream and Alert Labels tabs (lazy-loaded) and simple tools tab wiring.

2025-11-27 Codex
- Removed duplicate ccShowPanelPayload function definition (kept working sync version at line 1082).
- Fixed broken async version that had syntax errors (missing backticks, incomplete error message).
- Enhanced DOMContentLoaded handler to properly initialize Panel Stream tab and Alert Labels tab.
- Added deep-link support for tab switching via URL parameters.
- Added customer filter sync between notification and panel stream filters.
- Fixed initializeTabs to load Panel Stream and Alert Labels data when tabs are clicked.
- Implemented "Critical First" sort option (uses 1h + 24h activity as critical indicator).
- Fixed bad character in panel refresh badge (replaced � with proper bullet •).
- CRITICAL FIX: Merged duplicate DOMContentLoaded handlers that caused double initialization.
- CRITICAL FIX: Added Alert Labels tab to auto-refresh (now refreshes every 10s).
- Added silent parameter to loadDefinitions() for background refresh support.

2025-11-28 Codex
- CRITICAL FIX: Added enabled field to rule submission to fix "HTML error toast" when editing rules
- Default enabled to 1 (no UI toggle currently)
- Implemented Alert Labels CRUD: openDefinitionModal, closeDefinitionModal, editDefinition, deleteDefinition, handleDefinitionSubmit
- Added Edit/Delete action buttons to Alert Labels table
- Definition modal supports create and update operations
- Refreshes pattern suggestions after definition changes
- Increased limit to 500 alert labels, added Actions column
*/
