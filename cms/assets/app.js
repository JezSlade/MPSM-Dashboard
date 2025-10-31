/**
 * MPSM Dashboard - Main Application
 * Following Engineering Standards Rule 21: One Global Namespace
 */

const MPSM = (function() {
    'use strict';

    // Application state
    let state = {
        currentTab: 'dashboard',
        theme: 'light',
        dealerCode: null,
        dealerId: null,
        customerCode: null,
        customerName: null,
        customers: [],
        customerSearchTerm: '',
        isLoadingCustomers: false,
        devices: [],
        alerts: [],
        alertSummary: {},  // FIX BUG #3: Initialize as empty object instead of null
        connectorsSummary: null,
        totalDevices: 0,
        offlineDevices: 0,
        connectorsTotal: 0,
        alertsTotal: 0,
        cards: [],
        currentDevicePage: 1,
        currentAlertPage: 1,
        debugLogs: [],
        deviceDetails: {},
        deviceLookup: new Map(),
        isLoadingDevices: false,  // FIX BUG #1: Add loading flag to prevent concurrent requests
        isLoadingAlerts: false,   // FIX BUG #1: Add loading flag for alerts
        endpointCatalog: {
            categories: [],
            statistics: null,
            selectedCategory: null,
            searchTerm: '',
            initialized: false
        }
    };

    let customerSearchTimeout = null;
    let cardSelection = new Set();
    let cardsInitialized = false;
    let userTableInstance = null;
    let connectorPollTimer = null;
    let catalogTableInstance = null;
    let catalogSearchTimeout = null;

    const CARD_LAYOUT_STORAGE_KEY = 'mpsm-dashboard-card-order';

    function getAvailableCardIds() {
        if (typeof CardRegistry !== 'undefined' && typeof CardRegistry.getAll === 'function') {
            return CardRegistry.getAll().map(card => card.id);
        }
        if (typeof CardManager !== 'undefined' && typeof CardManager.getAvailableCards === 'function') {
            return CardManager.getAvailableCards().map(card => card.id);
        }
        return [];
    }

    function sanitizeCardOrder(order) {
        if (!Array.isArray(order)) {
            return [];
        }

        const availableIds = new Set(getAvailableCardIds());
        const seen = new Set();
        const sanitized = [];

        // FIX BUG #4: If card registry not loaded yet, defer sanitization
        if (availableIds.size === 0) {
            debugLog('Card registry not loaded yet, deferring sanitization', 'info');
            // Return order as-is, but still deduplicate and normalize
            order.forEach(id => {
                if (typeof id === 'string' && id.trim() !== '') {
                    const normalized = id.trim();
                    if (!seen.has(normalized)) {
                        sanitized.push(normalized);
                        seen.add(normalized);
                    }
                }
            });
            return sanitized;
        }

        order.forEach(id => {
            if (typeof id !== 'string' || id.trim() === '') {
                return;
            }
            const normalized = id.trim();
            if (seen.has(normalized)) {
                return;
            }
            if (!availableIds.has(normalized)) {
                debugLog(`Removing invalid card ID from layout: ${normalized}`, 'warn');
                return;
            }
            sanitized.push(normalized);
            seen.add(normalized);
        });

        return sanitized;
    }

    function loadCardLayoutFromStorage() {
        if (typeof window === 'undefined' || !window.localStorage) {
            return [];
        }

        try {
            const stored = window.localStorage.getItem(CARD_LAYOUT_STORAGE_KEY);
            if (!stored) {
                return [];
            }

            const parsed = JSON.parse(stored);
            return sanitizeCardOrder(parsed);
        } catch (error) {
            debugLog('Failed to load stored card layout: ' + error.message, 'warn');
            return [];
        }
    }

    function persistCardLayout(order, syncRemote = true) {
        const sanitized = sanitizeCardOrder(order);
        if (typeof window !== 'undefined' && window.localStorage) {
            try {
                window.localStorage.setItem(CARD_LAYOUT_STORAGE_KEY, JSON.stringify(sanitized));
            } catch (error) {
                debugLog('Failed to persist card layout locally: ' + error.message, 'warn');
            }
        }

        if (syncRemote && sanitized.length) {
            savePreference('cards', sanitized).catch(() => {});
        }
    }

    function applyCardLayout(order, options = {}) {
        const sanitized = sanitizeCardOrder(order);
        if (!sanitized.length) {
            return;
        }

        state.cards = sanitized.slice();
        cardSelection = new Set(sanitized);

        if (cardsInitialized) {
            CardManager.setEnabledCards(sanitized);
        }

        if (options.persist !== false) {
            persistCardLayout(sanitized, options.syncRemote !== false);
        }
    }

    const storedCardLayout = loadCardLayoutFromStorage();
    if (storedCardLayout.length) {
        state.cards = storedCardLayout.slice();
        cardSelection = new Set(storedCardLayout);
    }

    const EASTERN_TIMEZONE = 'America/New_York';

    function formatDateTime(value, options = {}) {
        if (value === null || value === undefined || value === '') {
            return 'N/A';
        }

        const date = value instanceof Date ? value : new Date(value);
        if (Number.isNaN(date.getTime())) {
            return 'N/A';
        }

        const hasStyles = options.dateStyle || options.timeStyle;
        const baseOptions = hasStyles
            ? {}
            : { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' };

        const outputOptions = Object.assign({}, baseOptions, options, { timeZone: EASTERN_TIMEZONE });
        return date.toLocaleString('en-US', outputOptions);
    }

    function renderRawJsonDetails(data) {
        try {
            const json = JSON.stringify(data, null, 2);
            if (!json) {
                return '';
            }
            return `
                <details class="endpoint-raw">
                    <summary>View raw response</summary>
                    <pre class="endpoint-raw__pre">${escapeHtml(json)}</pre>
                </details>
            `;
        } catch (error) {
            debugLog('Failed to render raw JSON: ' + error.message, 'warn');
            return '';
        }
    }

    function renderPrimitiveList(items, limit = 50) {
        const display = items.slice(0, limit);
        const remainder = items.length - display.length;
        return `
            <ul class="endpoint-list">
                ${display.map(item => `<li>${escapeHtml(formatDetailValue(item))}</li>`).join('')}
            </ul>
            ${remainder > 0 ? `<div class="endpoint-footnote">Showing ${display.length} of ${items.length} records.</div>` : ''}
        `;
    }

    function renderObjectArrayTable(items, options = {}) {
        if (!Array.isArray(items) || !items.length) {
            return '<div class="snapshot-value">No items returned</div>';
        }

        const maxRows = options.maxRows ?? 25;
        const sample = items.slice(0, maxRows);
        const columnSet = new Set();
        sample.forEach(item => {
            if (item && typeof item === 'object' && !Array.isArray(item)) {
                Object.keys(item).forEach(key => columnSet.add(key));
            }
        });
        const columns = Array.from(columnSet);

        if (!columns.length) {
            return renderPrimitiveList(items, maxRows);
        }

        const rowsHtml = sample.map(item => {
            const safeItem = item && typeof item === 'object' ? item : {};
            return `
                <tr>
                    ${columns.map(column => `<td>${escapeHtml(formatDetailValue(safeItem[column]))}</td>`).join('')}
                </tr>
            `;
        }).join('');

        const footnote = items.length > maxRows
            ? `<div class="endpoint-footnote">Showing ${maxRows} of ${items.length} records.</div>`
            : '';

        return `
            <div class="table-wrapper device-detail-table">
                <table class="table">
                    <thead>
                        <tr>
                            ${columns.map(column => `<th>${escapeHtml(column)}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            </div>
            ${footnote}
        `;
    }

    // Debug logger
    function debugLog(message, type = 'info') {
        const timestamp = new Date().toISOString();
        state.debugLogs.push({ timestamp, message, type });
        console.log(`[${type.toUpperCase()}] ${message}`);

        // Keep only last 50 logs
        if (state.debugLogs.length > 50) {
            state.debugLogs.shift();
        }
    }

    function updateConnectorStatusDisplay(online) {
        const statusEl = document.getElementById('connectors-status');
        if (!statusEl) {
            return;
        }

        statusEl.textContent = online ? 'Online' : 'Offline';
        statusEl.classList.remove('status-muted');
        statusEl.classList.toggle('status-online', online);
        statusEl.classList.toggle('status-offline', !online);
    }

    function resolveEquipmentIdFromParts(asset, external, fallback) {
        const a = (asset ?? '').toString().trim();
        const e = (external ?? '').toString().trim();
        const f = (fallback ?? '').toString().trim();

        if (!a && !e) {
            return f || 'N/A';
        }

        if (!a) {
            return e;
        }

        if (!e) {
            return a;
        }

        if (a.toLowerCase() === e.toLowerCase()) {
            return a;
        }

        return `${a} / ${e}`;
    }

    function getEquipmentIdFromDevice(device) {
        if (!device || typeof device !== 'object') {
            return 'N/A';
        }

        const asset = device.AssetNumber ?? device.Asset ?? device.EquipmentId ?? '';
        const external = device.ExternalIdentifier ?? device.ExternalId ?? '';
        const fallback = device.SerialNumber ?? device.DeviceSerialNumber ?? device.SystemName ?? '';

        return resolveEquipmentIdFromParts(asset, external, fallback);
    }

    function getEquipmentIdFromAlert(alert) {
        if (!alert || typeof alert !== 'object') {
            return 'N/A';
        }

        const directEquipment =
            alert.EquipmentId
            ?? alert.EquipmentID
            ?? alert.DeviceEquipmentId
            ?? alert.DeviceEquipmentID
            ?? alert.InstalledProductEquipmentId
            ?? alert.DeviceKey
            ?? '';

        const assetCandidate = alert.AssetNumber ?? alert.Asset ?? '';
        const externalCandidate = alert.ExternalIdentifier ?? alert.ExternalId ?? '';
        const serialFallback =
            alert.SerialNumber
            ?? alert.DeviceSerialNumber
            ?? alert.DeviceId
            ?? alert.IdDevice
            ?? alert.IdInstalledProduct
            ?? '';

        const asset = assetCandidate || directEquipment;
        const external = externalCandidate;
        const fallback = directEquipment || serialFallback;

        return resolveEquipmentIdFromParts(asset, external, fallback);
    }

    if (typeof window !== 'undefined') {
        window.resolveEquipmentIdFromParts = window.resolveEquipmentIdFromParts || resolveEquipmentIdFromParts;
        window.getEquipmentIdFromDevice = window.getEquipmentIdFromDevice || getEquipmentIdFromDevice;
        window.getEquipmentIdFromAlert = window.getEquipmentIdFromAlert || getEquipmentIdFromAlert;
    }

    function hydrateDeviceLookup(devices) {
        const lookupMap = new Map();

        if (Array.isArray(devices)) {
            devices.forEach(device => {
                if (!device || typeof device !== 'object') {
                    return;
                }

                const keys = new Set();
                const deviceId = device.Id ?? device.IdInstalledProduct ?? device.DeviceId ?? null;
                if (deviceId !== null && deviceId !== undefined) {
                    keys.add(String(deviceId).toLowerCase());
                }

                const equipmentId = getEquipmentIdFromDevice(device);
                if (equipmentId && equipmentId !== 'N/A') {
                    keys.add(String(equipmentId).toLowerCase());
                }

                const aliases = [
                    device.SerialNumber,
                    device.DeviceSerialNumber,
                    device.AssetNumber,
                    device.ExternalIdentifier,
                    device.SystemName,
                    device.DeviceAlias
                ];

                aliases.forEach(alias => {
                    if (alias !== undefined && alias !== null && alias !== '') {
                        keys.add(String(alias).toLowerCase());
                    }
                });

                keys.forEach(key => lookupMap.set(key, device));
            });
        }

        state.deviceLookup = lookupMap;
        return lookupMap;
    }

    if (typeof window !== 'undefined') {
        window.hydrateDeviceLookup = window.hydrateDeviceLookup || hydrateDeviceLookup;
    }

    function resolveSupplyColor(value) {
        const text = (value ?? '').toString().toLowerCase();
        if (!text) {
            return 'neutral';
        }

        if (text.includes('black') || text.includes('mono') || text.includes('k ')) {
            return 'black';
        }
        if (text.includes('cyan') || text.includes('c ')) {
            return 'cyan';
        }
        if (text.includes('magenta') || text.includes('m ')) {
            return 'magenta';
        }
        if (text.includes('yellow') || text.includes('y ')) {
            return 'yellow';
        }

        return 'neutral';
    }

    if (typeof window !== 'undefined') {
        window.resolveSupplyColor = window.resolveSupplyColor || resolveSupplyColor;
    }

    function renderTonerChipMarkup(color, value) {
        const renderFn = (typeof window !== 'undefined' && typeof window.renderTonerBadge === 'function')
            ? window.renderTonerBadge
            : null;

        if (renderFn) {
            return renderFn(color, value);
        }

        const level = Number(value);
        const safeLevel = Number.isFinite(level) ? Math.max(0, Math.min(100, level)) : 0;
        const severityClass = safeLevel <= 10 ? 'status-danger'
            : safeLevel <= 25 ? 'status-warning'
            : 'status-success';

        return `
            <div class="toner-bar">
                <div class="toner-fill ${severityClass}" style="width:${safeLevel}%"></div>
                <span class="toner-text">${safeLevel}%</span>
            </div>
        `;
    }

    if (typeof window !== 'undefined') {
        window.renderTonerChipMarkup = window.renderTonerChipMarkup || renderTonerChipMarkup;
    }

    function renderAlertLevelChip(row, value) {
        const level = Number(value);
        if (!Number.isFinite(level)) {
            return '--';
        }
        const safeLevel = Math.max(0, Math.min(100, level));
        const colorSource = row?.SupplyTypeDescription || row?.SupplyType || row?.Color || '';
        const color = resolveSupplyColor(colorSource);
        return renderTonerChipMarkup(color, safeLevel);
    }

    function resolveAlertDeviceKey(alert) {
        if (!alert || typeof alert !== 'object') {
            return null;
        }

        const candidates = [
            alert.DeviceId,
            alert.IdDevice,
            alert.IdInstalledProduct,
            alert.Id,
            alert.AssetNumber,
            alert.ExternalIdentifier,
            alert.SerialNumber
        ];

        for (const candidate of candidates) {
            if (candidate !== undefined && candidate !== null && candidate !== '') {
                return candidate.toString();
            }
        }

        const equipmentId = getEquipmentIdFromAlert(alert);
        return equipmentId !== 'N/A' ? equipmentId : null;
    }

    function computeAlertDeviceSummary(alerts) {
        const deviceMap = new Map();

        if (Array.isArray(alerts)) {
            alerts.forEach(alert => {
                const key = resolveAlertDeviceKey(alert);
                if (!key) {
                    return;
                }

                const level = Number(alert.Percentage ?? alert.ActualResidualPercentage ?? alert.InitialResidualPercentage ?? 0);
                const normalizedLevel = Number.isFinite(level) ? level : 100;

                const existing = deviceMap.get(key);
                if (!existing) {
                    deviceMap.set(key, {
                        level: normalizedLevel,
                        alerts: [alert]
                    });
                } else {
                    existing.level = Math.min(existing.level, normalizedLevel);
                    existing.alerts.push(alert);
                }
            });
        }

        let critical = 0;
        let low = 0;
        let ok = 0;

        deviceMap.forEach(entry => {
            if (entry.level <= 10) {
                critical += 1;
            } else if (entry.level <= 25) {
                low += 1;
            } else {
                ok += 1;
            }
        });

        return {
            totalDevices: deviceMap.size,
            critical,
            low,
            ok,
            deviceMap
        };
    }

    if (typeof window !== 'undefined') {
        window.resolveAlertDeviceKey = window.resolveAlertDeviceKey || resolveAlertDeviceKey;
        window.computeAlertDeviceSummary = window.computeAlertDeviceSummary || computeAlertDeviceSummary;
    }

    function findDeviceIdForAlert(alert) {
        if (!alert || typeof alert !== 'object') {
            return null;
        }

        const direct = alert.DeviceId ?? alert.IdDevice ?? alert.IdInstalledProduct ?? null;
        if (direct !== null && direct !== undefined) {
            return direct;
        }

        const lookupKeys = [];
        const equipmentId = getEquipmentIdFromAlert(alert);
        if (equipmentId && equipmentId !== 'N/A') {
            lookupKeys.push(equipmentId);
        }
        if (alert.SerialNumber) lookupKeys.push(alert.SerialNumber);
        if (alert.DeviceSerialNumber) lookupKeys.push(alert.DeviceSerialNumber);
        if (alert.AssetNumber) lookupKeys.push(alert.AssetNumber);
        if (alert.ExternalIdentifier) lookupKeys.push(alert.ExternalIdentifier);

        for (const key of lookupKeys) {
            if (!key) continue;
            const normalized = key.toString().toLowerCase();
            if (state.deviceLookup.has(normalized)) {
                const device = state.deviceLookup.get(normalized);
                if (device && typeof device === 'object') {
                    return device.Id ?? device.IdInstalledProduct ?? device.DeviceId ?? null;
                }
            }
        }

        return null;
    }

    if (typeof window !== 'undefined') {
        window.findDeviceIdForAlert = window.findDeviceIdForAlert || findDeviceIdForAlert;
    }

    function formatDetailValue(value) {
        if (value === null || value === undefined || value === '') {
            return 'N/A';
        }

        if (typeof value === 'number') {
            return Number(value).toLocaleString();
        }

        if (typeof value === 'boolean') {
            return value ? 'Yes' : 'No';
        }

        if (value instanceof Date) {
            return formatDateTime(value);
        }

        if (typeof value === 'string') {
            const isoDatePattern = /^\d{4}-\d{2}-\d{2}T/;
            if (isoDatePattern.test(value)) {
                return formatDateTime(value);
            }
            const numeric = Number(value);
            if (!Number.isNaN(numeric) && String(numeric) === value) {
                return Number(value).toLocaleString();
            }
            return value;
        }

        if (Array.isArray(value)) {
            return `${value.length} item(s)`;
        }

        if (typeof value === 'object') {
            let serialized;
            try {
                serialized = JSON.stringify(value);
            } catch (error) {
                return '[Object]';
            }
            if (serialized.length > 200) {
                serialized = serialized.slice(0, 197) + '...';
            }
            return serialized;
        }

        return String(value);
    }

    function renderKeyValueSnapshot(data, limit = 12) {
        if (!data || typeof data !== 'object') {
            return '<div class="snapshot-value">No details available</div>';
        }

        const allEntries = Object.entries(data);
        if (!allEntries.length) {
            return '<div class="snapshot-value">No details available</div>';
        }

        const entries = allEntries.slice(0, limit);
        const remaining = allEntries.length - entries.length;

        return `
            <div class="device-snapshot">
                ${entries.map(([key, value]) => `
                    <div class="snapshot-item">
                        <div class="snapshot-label">${escapeHtml(key)}</div>
                        <div class="snapshot-value">${escapeHtml(formatDetailValue(value))}</div>
                    </div>
                `).join('')}
            </div>
            ${remaining > 0 ? `<div class="endpoint-footnote">+${remaining} more field${remaining === 1 ? '' : 's'} not shown. See raw data for full details.</div>` : ''}
        `;
    }

    function renderEndpointDataPreview(entry) {
        const data = entry.data;

        if (data === null || data === undefined) {
            return '<div class="snapshot-value">No data returned</div>';
        }

        if (Array.isArray(data)) {
            if (!data.length) {
                return '<div class="snapshot-value">No items returned</div>';
            }

            const hasObject = data.some(item => item && typeof item === 'object' && !Array.isArray(item));
            const preview = hasObject
                ? renderObjectArrayTable(data)
                : renderPrimitiveList(data);

            return preview + renderRawJsonDetails(data);
        }

        if (typeof data === 'object') {
            return renderKeyValueSnapshot(data) + renderRawJsonDetails(data);
        }

        return `<div class="snapshot-value">${escapeHtml(formatDetailValue(data))}</div>` + renderRawJsonDetails(data);
    }

    function renderEndpointMetaFooter(entry) {
        const parts = [];

        const duration = entry.duration_ms ?? entry.duration ?? null;
        if (duration !== null && duration !== undefined) {
            parts.push(`Duration: ${formatDetailValue(duration)} ms`);
        }

        if (Array.isArray(entry.data)) {
            parts.push(`Items: ${formatDetailValue(entry.data.length)}`);
        }

        const meta = entry.meta;
        if (meta && typeof meta === 'object') {
            const keys = ['items_returned', 'total_rows', 'total_count', 'total', 'count'];
            keys.forEach(key => {
                if (meta[key] !== undefined && meta[key] !== null) {
                    const label = key.replace(/_/g, ' ');
                    parts.push(`${label}: ${formatDetailValue(meta[key])}`);
                }
            });
        }

        if (!parts.length) {
            return '';
        }

        return `
            <div class="card-footer">
                ${parts.map(part => `<span>${escapeHtml(part)}</span>`).join(' &bull; ')}
            </div>
        `;
    }

    function renderEndpointSection(entry) {
        const statusClass = entry.success ? 'status-success' : 'status-danger';
        const statusLabel = entry.success ? 'Success' : 'Failed';
        const bodyContent = entry.success
            ? renderEndpointDataPreview(entry)
            : `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${escapeHtml(entry.error || 'Endpoint request failed')}</p>
                </div>
            `;

        const footer = renderEndpointMetaFooter(entry);

        return `
            <div class="card device-detail-card">
                <div class="card-header">
                    <h3>${escapeHtml(entry.action || 'Endpoint')}</h3>
                    <span class="status-badge ${statusClass}">${statusLabel}</span>
                </div>
                <div class="card-body">
                    ${bodyContent}
                </div>
                ${footer}
            </div>
        `;
    }

    function renderEndpointSections(results) {
        if (!Array.isArray(results) || results.length === 0) {
            return '<div class="empty-state"><i class="fas fa-info-circle"></i><p>No endpoint data available</p></div>';
        }

        return results.map(renderEndpointSection).join('');
    }

    async function fetchDeviceDetails(deviceId) {
        if (state.deviceDetails && state.deviceDetails[deviceId]) {
            return state.deviceDetails[deviceId];
        }

        const params = new URLSearchParams({
            deviceId: deviceId,
            dealerId: state.dealerId || '',
            customerCode: state.customerCode || ''
        });

        const response = await fetch('api/get-device-details.php?' + params.toString());
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Device detail request failed');
        }

        state.deviceDetails[deviceId] = data;
        return data;
    }

    /**
     * Initialize application
     * Following Rule 22: No Callback Hell (using async/await)
     */
    async function init() {
        setupEventListeners();
        loadTheme();
        initGlobalDeviceSearch();  // Initialize global device search

        try {
            await loadPreferences();
            await loadCustomerOptions();
            await loadDashboard();
        } catch (error) {
            showToast('Failed to initialize: ' + error.message, 'error');
        }
    }

    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        // Tab navigation
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                switchTab(e.target.dataset.tab);
            });
        });

        // Theme toggle
        document.getElementById('theme-toggle').addEventListener('click', toggleTheme);

        // Refresh
        document.getElementById('refresh-btn').addEventListener('click', loadDashboard);

        // Logout
        document.getElementById('logout-btn').addEventListener('click', logout);

        // Save settings
        document.getElementById('save-settings').addEventListener('click', saveSettings);

        // Test health
        document.getElementById('test-health').addEventListener('click', testSystemHealth);

        // Refresh visitors
        document.getElementById('refresh-visitors').addEventListener('click', loadVisitorLogs);

        // Admin section navigation
        document.querySelectorAll('.admin-nav-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const section = e.currentTarget.dataset.section;
                switchAdminSection(section);
            });
        });

        const userForm = document.getElementById('user-create-form');
        if (userForm) {
            userForm.addEventListener('submit', createUser);
        }

        const userTable = document.getElementById('user-table');
        if (userTable) {
            userTable.addEventListener('click', handleUserTableClick);
        }

        const customerSelect = document.getElementById('customer-select');
        if (customerSelect) {
            customerSelect.addEventListener('change', handleCustomerSelection);
        }

        const customerSearch = document.getElementById('customer-search');
        if (customerSearch) {
            customerSearch.addEventListener('input', handleCustomerSearch);
        }

        const catalogSearch = document.getElementById('catalog-search');
        if (catalogSearch) {
            catalogSearch.addEventListener('input', handleCatalogSearch);
        }

        const catalogCategories = document.getElementById('catalog-category-list');
        if (catalogCategories) {
            catalogCategories.addEventListener('click', handleCatalogCategoryClick);
        }
    }

    /**
     * Switch admin sections
     */
    function switchAdminSection(sectionName) {
        document.querySelectorAll('.admin-nav-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.section === sectionName);
        });

        document.querySelectorAll('.admin-section').forEach(section => {
            section.classList.toggle('active', section.id === 'admin-' + sectionName);
        });

        if (sectionName === 'dashboard') {
            renderCardConfig();
        } else if (sectionName === 'users') {
            loadUsers();
        } else if (sectionName === 'catalog') {
            loadEndpointCatalog({
                category: state.endpointCatalog.selectedCategory,
                search: state.endpointCatalog.searchTerm,
                silent: state.endpointCatalog.initialized
            });
        }
    }

    /**
     * Switch tabs
     */
    function switchTab(tabName) {
        state.currentTab = tabName;

        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabName);
        });

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.toggle('active', content.id === tabName + '-tab');
        });

        // Load visitor logs when switching to admin tab
        if (tabName === 'admin') {
            loadVisitorLogs();
        }
    }

    /**
     * Load theme
     */
    function loadTheme() {
        const theme = document.body.getAttribute('data-theme') || 'light';
        state.theme = theme;
        updateThemeIcon();
    }

    /**
     * Toggle theme
     */
    function toggleTheme() {
        state.theme = state.theme === 'light' ? 'dark' : 'light';
        document.body.setAttribute('data-theme', state.theme);
        updateThemeIcon();

        // Save to preferences
        savePreference('theme', state.theme);
    }

    /**
     * Update theme icon
     */
    function updateThemeIcon() {
        const icon = document.querySelector('#theme-toggle i');
        icon.className = state.theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    }

    /**
     * Load user preferences
     * Following Rule 22: async/await
     */
    async function loadPreferences() {
        try {
            debugLog('Loading user preferences...');
            const response = await fetch('api/get-preferences.php');
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            const prefs = data.preferences;
            state.dealerCode = prefs.dealerCode || 'NY06AGDWUQ';
            state.dealerId = prefs.dealerId || 'SZ13qRwU5GtFLj0i_CbEgQ2';
            state.customerCode = prefs.customerCode || 'W9OPXL0YDK';
            state.customerName = prefs.customerName || 'CAPE FEAR VALLEY MED CTR.';
            state.theme = prefs.theme || 'light';
            const serverLayout = sanitizeCardOrder(Array.isArray(prefs.cards) ? prefs.cards : []);

            if (!state.cards.length && serverLayout.length) {
                state.cards = serverLayout.slice();
                cardSelection = new Set(state.cards);
                persistCardLayout(state.cards, false);
            } else if (state.cards.length) {
                serverLayout.forEach(id => {
                    if (!state.cards.includes(id)) {
                        state.cards.push(id);
                    }
                });
                cardSelection = new Set(state.cards);
            }

            debugLog(`Preferences loaded: ${state.customerCode}`, 'info');
            syncPreferenceInputs();
            initializeCards();

        } catch (error) {
            debugLog('Failed to load preferences: ' + error.message, 'error');
            // Use defaults
            state.dealerCode = 'NY06AGDWUQ';
            state.dealerId = 'SZ13qRwU5GtFLj0i_CbEgQ2';
            state.customerCode = 'W9OPXL0YDK';
            state.customerName = 'CAPE FEAR VALLEY MED CTR.';
            state.theme = 'light';
            syncPreferenceInputs();
            initializeCards();
        }
    }

    function handleCardData(cardId, snapshot) {
        if (!snapshot) {
            return;
        }

        if (!cardSelection.size && Array.isArray(state.cards) && state.cards.length) {
            cardSelection = new Set(state.cards);
        }

        if (cardId === 'device-inventory') {
            const snapshotDevices = Array.isArray(snapshot.context?.devices) ? snapshot.context.devices : [];
            if (snapshotDevices.length && (state.devices.length === 0 || snapshotDevices.length >= state.devices.length)) {
                state.devices = snapshotDevices;
            }

            hydrateDeviceLookup(state.devices);

            const headlineTotal = Number(snapshot.headline?.value ?? 0);
            const contextTotal = Number(snapshot.context?.total ?? 0);
            const fallbackTotal = Number(state.totalDevices ?? 0);
            const totalDevices = Math.max(headlineTotal, contextTotal, fallbackTotal);

            state.totalDevices = totalDevices;
            updateMetricValue('device-count', totalDevices);
            updateMetricValue('banner-device-total', totalDevices);

            const metricOffline = snapshot.metrics?.find(metric => metric.label === 'Offline');
            const offlineFromMetric = Number(metricOffline?.value ?? 0);
            const offlineFromState = Array.isArray(state.devices)
                ? state.devices.filter(device => device.IsOffline).length
                : 0;
            const fallbackOffline = Number(state.offlineDevices ?? 0);
            const offlineCount = Math.max(offlineFromMetric, offlineFromState, fallbackOffline);

            state.offlineDevices = offlineCount;
            updateMetricValue('offline-count', offlineCount);
        }

        if (cardId === 'supply-alerts') {
            const snapshotAlerts = Array.isArray(snapshot.context?.alerts) ? snapshot.context.alerts : [];
            if (snapshotAlerts.length) {
                state.alerts = snapshotAlerts;
            }

            const snapshotSummary = snapshot.context?.summary;
            const summary = snapshotSummary || computeAlertDeviceSummary(state.alerts);

            state.alertSummary = summary;
            state.alertsTotal = summary.totalDevices;

            updateMetricValue('alerts-count', summary.totalDevices);
            updateMetricValue('alert-count', summary.totalDevices);
        }

        if (cardId === 'integrations') {
            const integrations = Array.isArray(snapshot.context?.integrations) ? snapshot.context.integrations : [];
            if (integrations.length) {
                state.connectorsSummary = integrations;
            }

            const headlineConnectors = Number(snapshot.headline?.value ?? 0);
            const fallbackConnectors = Number(state.connectorsTotal ?? 0);
            const connectorTotal = Math.max(headlineConnectors, fallbackConnectors, integrations.length);

            state.connectorsTotal = connectorTotal;
            updateMetricValue('connectors-count', connectorTotal);
            updateMetricValue('connectors-hidden-count', connectorTotal);
        }
    }

    function updateMetricValue(elementId, value) {
        const el = document.getElementById(elementId);
        if (!el) return;
        if (value === null || value === undefined) {
            el.textContent = '0';
            return;
        }
        const num = Number(value);
        el.textContent = Number.isFinite(num) ? num.toLocaleString() : String(value);
    }

    function renderCardConfig() {
        const container = document.getElementById('dashboard-card-config');
        if (!container) return;

        const definitions = CardManager.getAvailableCards();

        if (!cardSelection.size && Array.isArray(state.cards) && state.cards.length) {
            cardSelection = new Set(state.cards);
        }

        if (!cardSelection.size) {
            cardSelection = new Set(CardManager.getEnabledCards());
            state.cards = sanitizeCardOrder(Array.from(cardSelection));
        }

        container.innerHTML = '';

        definitions
            .slice()
            .sort((a, b) => a.title.localeCompare(b.title))
            .forEach(card => {
                const active = cardSelection.has(card.id) || (!cardSelection.size && card.defaultVisible);
                if (!cardSelection.size && card.defaultVisible) {
                    cardSelection.add(card.id);
                }

                const label = document.createElement('label');
                label.className = 'config-card' + (active ? ' active' : '');

                label.innerHTML = `
                    <input type="checkbox" data-card-id="${card.id}" ${active ? 'checked' : ''}>
                    <i class="${card.icon}"></i>
                    <h3>${card.title}</h3>
                    <p>${card.description || ''}</p>
                `;

                label.addEventListener('change', (event) => {
                    const checkbox = event.currentTarget.querySelector('input[type="checkbox"]');
                    const { cardId } = checkbox.dataset;
                    const checked = checkbox.checked;

                    if (!checked && state.cards.includes(cardId) && state.cards.length <= 1) {
                        checkbox.checked = true;
                        showToast('At least one card must remain enabled', 'warning');
                        return;
                    }

                    if (checked) {
                        if (!cardSelection.has(cardId)) {
                            cardSelection.add(cardId);
                        }
                        if (!state.cards.includes(cardId)) {
                            state.cards.push(cardId);
                        }
                        label.classList.add('active');
                    } else {
                        cardSelection.delete(cardId);
                        state.cards = state.cards.filter(id => id !== cardId);
                        label.classList.remove('active');

                        if (!state.cards.length) {
                            const fallback = sanitizeCardOrder(CardManager.getEnabledCards());
                            if (fallback.length) {
                                state.cards = fallback.slice();
                                cardSelection = new Set(state.cards);
                            }
                        }
                    }

                    applyCardLayout(state.cards);
                    CardManager.refreshAll().catch(error => debugLog('Card refresh failed: ' + error.message, 'error'));
                });

                container.appendChild(label);
            });
    }

    async function loadUsers() {
        try {
            const response = await fetch('api/users/list.php');
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            renderUserTable(data.users || []);
        } catch (error) {
            debugLog('Failed to load users: ' + error.message, 'error');
            showToast('Failed to load users: ' + error.message, 'error');
        }
    }

    function renderUserTable(users) {
        const table = document.getElementById('user-table');
        if (!table) return;

        const columns = `
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
        `;

        const rows = (users || []).map(user => `
            <tr>
                <td>${escapeHtml(user.username)}</td>
                <td>${formatDateTime(user.created_at, { dateStyle: 'short', timeStyle: 'short' })}</td>
                <td>
                    <div class="user-actions">
                        <button class="btn btn-secondary" data-action="reset" data-id="${user.id}">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                        <button class="btn btn-danger" data-action="delete" data-id="${user.id}">
                            <i class="fas fa-user-slash"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        const body = rows || `
            <tr>
                <td colspan="3">
                    <div class="empty-state"><i class="fas fa-info-circle"></i><p>No users found</p></div>
                </td>
            </tr>
        `;

        table.innerHTML = columns + '<tbody>' + body + '</tbody>';
    }

    async function createUser(event) {
        event.preventDefault();

        const usernameInput = document.getElementById('create-username');
        const passwordInput = document.getElementById('create-password');

        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();

        if (!username || !password) {
            showToast('Username and password are required', 'warning');
            return;
        }

        try {
            const response = await fetch('api/users/create.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error);
            }

            usernameInput.value = '';
            passwordInput.value = '';
            showToast('User created successfully', 'success');
            await loadUsers();
        } catch (error) {
            debugLog('Failed to create user: ' + error.message, 'error');
            showToast('Failed to create user: ' + error.message, 'error');
        }
    }

    async function handleUserTableClick(event) {
        const target = event.target.closest('button[data-action]');
        if (!target) return;

        const userId = Number(target.dataset.id);
        const action = target.dataset.action;

        if (action === 'reset') {
            const password = prompt('Enter a new password for this user:');
            if (!password) return;
            await resetUserPassword(userId, password);
        }

        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete this user?')) {
                return;
            }
            await deleteUser(userId);
        }
    }

    async function resetUserPassword(userId, password) {
        try {
            const response = await fetch('api/users/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId, password })
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error);
            }

            showToast('Password updated successfully', 'success');
            await loadUsers();
        } catch (error) {
            debugLog('Failed to update password: ' + error.message, 'error');
            showToast('Failed to update password: ' + error.message, 'error');
        }
    }

    async function deleteUser(userId) {
        try {
            const response = await fetch('api/users/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId })
            });

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error);
            }

            showToast('User deleted successfully', 'success');
            await loadUsers();
        } catch (error) {
            debugLog('Failed to delete user: ' + error.message, 'error');
            showToast('Failed to delete user: ' + error.message, 'error');
        }
    }

    function openCardModal(cardId) {
        const targetCard = document.querySelector(`.dashboard-card[data-card-id="${cardId}"]`);
        if (targetCard) {
            targetCard.click();
            return true;
        }
        return false;
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /**
     * Ensure admin inputs reflect current preference state
     */
    function syncPreferenceInputs() {
        const dealerCodeInput = document.getElementById('dealer-code');
        if (dealerCodeInput) {
            dealerCodeInput.value = state.dealerCode || '';
        }

        const dealerIdInput = document.getElementById('dealer-id');
        if (dealerIdInput) {
            dealerIdInput.value = state.dealerId || '';
        }

        const customerCodeInput = document.getElementById('customer-code');
        if (customerCodeInput) {
            customerCodeInput.value = state.customerCode || '';
        }

        const customerNameInput = document.getElementById('customer-name');
        if (customerNameInput) {
            customerNameInput.value = state.customerName || '';
        }
    }

    function initializeCards() {
        const initialOrder = state.cards.length ? state.cards.slice() : Array.from(cardSelection);

        if (!cardsInitialized) {
            CardManager.init({
                container: '#dashboard-card-container',
                preferencesProvider: () => ({ cards: initialOrder }),
                onCardData: handleCardData
            });
            cardsInitialized = true;
        }

        if (!cardSelection.size && Array.isArray(state.cards) && state.cards.length) {
            cardSelection = new Set(state.cards);
        }

        CardManager.setContext({
            dealerCode: state.dealerCode,
            dealerId: state.dealerId,
            customerCode: state.customerCode
        });

        const desiredOrder = state.cards.length ? state.cards.slice() : Array.from(cardSelection);
        if (desiredOrder.length) {
            applyCardLayout(desiredOrder, { persist: false, syncRemote: false });
        } else {
            const fallbackOrder = CardManager.getEnabledCards();
            if (fallbackOrder.length) {
                applyCardLayout(fallbackOrder, { persist: false, syncRemote: false });
            }
        }

        renderCardConfig();
    }

    /**
     * Save single preference
     */
    async function savePreference(key, value) {
        try {
            const prefs = {};
            prefs[key] = value;

            const response = await fetch('api/save-preferences.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(prefs)
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

        } catch (error) {
            console.error('Failed to save preference:', error);
        }
    }

    /**
     * Load customers for the selector
     */
    async function loadCustomerOptions(searchTerm = '') {
        const select = document.getElementById('customer-select');
        if (!select) {
            return;
        }

        const shouldFilter = searchTerm && searchTerm.length >= 2;
        const query = shouldFilter ? `?search=${encodeURIComponent(searchTerm)}` : '';

        state.customerSearchTerm = searchTerm;
        state.isLoadingCustomers = true;

        select.innerHTML = '<option value="">Loading customers...</option>';

        try {
            const response = await fetch('api/get-customers.php' + query);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            state.customers = Array.isArray(data.customers) ? data.customers : [];
            populateCustomerSelect();

        } catch (error) {
            debugLog('Failed to load customers: ' + error.message, 'error');
            showToast('Failed to load customers: ' + error.message, 'error');
            populateCustomerSelect();
        } finally {
            state.isLoadingCustomers = false;
        }
    }

    /**
     * Populate customer select element
     */
    function populateCustomerSelect() {
        const select = document.getElementById('customer-select');
        if (!select) {
            return;
        }

        const currentCode = (document.getElementById('customer-code')?.value || state.customerCode || '').trim();
        const currentName = (document.getElementById('customer-name')?.value || state.customerName || '').trim();

        select.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = state.isLoadingCustomers ? 'Loading customers...' : 'Select a customer';
        placeholder.disabled = true;
        placeholder.selected = !currentCode;
        select.appendChild(placeholder);

        const customers = Array.isArray(state.customers) ? state.customers : [];
        let hasMatch = false;

        if (!customers.length) {
            placeholder.textContent = state.isLoadingCustomers ? 'Loading customers...' : 'No customers found';
            if (currentCode) {
                const manualOption = document.createElement('option');
                manualOption.value = currentCode;
                manualOption.textContent = currentName ? `${currentName} (${currentCode})` : currentCode;
                manualOption.selected = true;
                manualOption.dataset.manual = 'true';
                select.appendChild(manualOption);
            }
            return;
        }

        customers.forEach(customer => {
            const option = document.createElement('option');
            option.value = customer.Code;
            const label = customer.Description ? `${customer.Description} (${customer.Code})` : customer.Code;
            option.textContent = label;
            if (customer.Code === currentCode) {
                option.selected = true;
                hasMatch = true;
            }
            select.appendChild(option);
        });

        if (!hasMatch && currentCode) {
            const manualOption = document.createElement('option');
            manualOption.value = currentCode;
            manualOption.textContent = currentName ? `${currentName} (${currentCode})` : currentCode;
            manualOption.selected = true;
            manualOption.dataset.manual = 'true';
            select.appendChild(manualOption);
        }
    }

    /**
     * Handle customer selection change
     */
    function handleCustomerSelection(event) {
        const code = event.target.value;
        if (!code) {
            return;
        }

        const selected = (state.customers || []).find(customer => customer.Code === code);
        const description = selected ? (selected.Description || '') : '';

        const codeInput = document.getElementById('customer-code');
        const nameInput = document.getElementById('customer-name');

        if (codeInput) {
            codeInput.value = code;
        }

        if (nameInput) {
            nameInput.value = description || nameInput.value || '';
        }

        state.customerCode = code;
        state.customerName = description || state.customerName || '';
        state.devices = [];
        state.alerts = [];
        state.connectorsSummary = null;
        state.deviceDetails = {};
        state.totalDevices = 0;
        state.offlineDevices = 0;
        state.alertsTotal = 0;
        state.connectorsTotal = 0;

        CardManager.setContext({
            dealerCode: state.dealerCode,
            dealerId: state.dealerId,
            customerCode: state.customerCode
        });

        debugLog(`Customer selected: ${code}`, 'info');
        loadDashboard();
    }

    /**
     * Handle customer search input with debounce
     */
    function handleCustomerSearch(event) {
        const term = event.target.value.trim();

        if (customerSearchTimeout) {
            clearTimeout(customerSearchTimeout);
        }

        customerSearchTimeout = setTimeout(() => {
            if (term.length === 0) {
                loadCustomerOptions();
            } else if (term.length >= 2) {
                loadCustomerOptions(term);
            }
        }, 300);
    }

    /**
     * Handle endpoint catalog search input with debounce
     */
    function handleCatalogSearch(event) {
        const term = event.target.value.trim();

        if (catalogSearchTimeout) {
            clearTimeout(catalogSearchTimeout);
        }

        catalogSearchTimeout = setTimeout(() => {
            state.endpointCatalog.searchTerm = term;
            loadEndpointCatalog({
                category: state.endpointCatalog.selectedCategory,
                search: term,
                silent: false
            });
        }, 300);
    }

    /**
     * Handle catalog category selection
     */
    function handleCatalogCategoryClick(event) {
        const button = event.target.closest('button[data-category]');
        if (!button) {
            return;
        }

        const value = button.dataset.category || '';
        const category = value === '' ? null : value;
        state.endpointCatalog.selectedCategory = category;

        loadEndpointCatalog({
            category,
            search: state.endpointCatalog.searchTerm,
            silent: false
        });
    }

    /**
     * Load endpoint catalog data from API
     */
    async function loadEndpointCatalog(options = {}) {
        const category = options.category !== undefined ? options.category : state.endpointCatalog.selectedCategory;
        const search = options.search !== undefined ? options.search : state.endpointCatalog.searchTerm;
        const silent = options.silent ?? false;

        const categoryContainer = document.getElementById('catalog-category-list');
        const tableContainer = document.getElementById('catalog-table');
        const statsContainer = document.getElementById('catalog-stats');

        if (!categoryContainer || !tableContainer) {
            return;
        }

        if (!silent && !catalogTableInstance) {
            tableContainer.innerHTML = '<div class="loading">Loading endpoints...</div>';
            if (statsContainer) {
                statsContainer.innerHTML = '';
            }
        }

        try {
            const params = new URLSearchParams({ limit: 200 });
            if (category) {
                params.set('category', category);
            }
            if (search) {
                params.set('search', search);
            }

            const response = await fetch('api/get-endpoint-catalog.php?' + params.toString());
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Catalog request failed');
            }

            state.endpointCatalog.categories = Array.isArray(data.categories) ? data.categories : [];
            state.endpointCatalog.statistics = data.statistics || null;
            state.endpointCatalog.selectedCategory = category;
            state.endpointCatalog.searchTerm = search;
            state.endpointCatalog.initialized = true;

            renderEndpointCatalog(data);
        } catch (error) {
            if (!silent && !catalogTableInstance) {
                tableContainer.innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Failed to load endpoint catalog</p>
                        <p class="error-message">${escapeHtml(error.message)}</p>
                    </div>
                `;
            }
            showToast('Failed to load endpoint catalog: ' + error.message, 'error');
        }
    }

    /**
     * Render endpoint catalog UI
     */
    function renderEndpointCatalog(payload) {
        const categoryContainer = document.getElementById('catalog-category-list');
        const statsContainer = document.getElementById('catalog-stats');
        const tableContainer = document.getElementById('catalog-table');
        const searchInput = document.getElementById('catalog-search');

        if (!categoryContainer || !tableContainer) {
            return;
        }

        if (searchInput && searchInput.value !== state.endpointCatalog.searchTerm) {
            searchInput.value = state.endpointCatalog.searchTerm;
        }

        const categories = Array.isArray(payload.categories) ? payload.categories : [];
        const stats = payload.statistics || {};
        const selectedCategory = state.endpointCatalog.selectedCategory || null;

        const totalEndpoints = stats.total_endpoints ?? categories.reduce((acc, item) => acc + (item.endpoint_count || 0), 0);
        const workingEndpoints = stats.working ?? null;
        const failedEndpoints = stats.failed ?? null;
        const averageDuration = stats.avg_response_time ?? null;
        const lastTested = stats.test_date ? formatDateTime(stats.test_date) : null;

        const augmentedCategories = [
            {
                id: '',
                description: 'All Endpoints',
                endpoint_count: totalEndpoints
            },
            ...categories
        ];

        categoryContainer.innerHTML = augmentedCategories.map(category => {
            const id = category.id ?? '';
            const isActive = (selectedCategory ?? '') === id;
            return `
                <li>
                    <button type="button" class="catalog-category ${isActive ? 'active' : ''}" data-category="${escapeHtml(id)}">
                        <span class="catalog-category-name">${escapeHtml(category.description ?? '')}</span>
                        <span class="catalog-category-count">${category.endpoint_count ?? 0}</span>
                    </button>
                </li>
            `;
        }).join('');

        if (statsContainer) {
            statsContainer.innerHTML = `
                <div class="catalog-stat">
                    <span class="catalog-stat-label">Total</span>
                    <span class="catalog-stat-value">${Number(totalEndpoints || 0).toLocaleString()}</span>
                </div>
                <div class="catalog-stat">
                    <span class="catalog-stat-label">Working</span>
                    <span class="catalog-stat-value">${workingEndpoints !== null ? Number(workingEndpoints).toLocaleString() : 'N/A'}</span>
                </div>
                <div class="catalog-stat">
                    <span class="catalog-stat-label">Failed</span>
                    <span class="catalog-stat-value">${failedEndpoints !== null ? Number(failedEndpoints).toLocaleString() : 'N/A'}</span>
                </div>
                <div class="catalog-stat">
                    <span class="catalog-stat-label">Avg. Response (ms)</span>
                    <span class="catalog-stat-value">${averageDuration !== null ? Number(averageDuration).toLocaleString() : 'N/A'}</span>
                </div>
                <div class="catalog-stat">
                    <span class="catalog-stat-label">Last Tested</span>
                    <span class="catalog-stat-value">${lastTested || 'N/A'}</span>
                </div>
            `;
        }

        const rows = Array.isArray(payload.endpoints) ? payload.endpoints : [];
        const normalizedRows = rows.map(entry => {
            const status = entry.success === null || entry.success === undefined
                ? 'Unknown'
                : entry.success
                    ? 'Working'
                    : 'Failed';
            const tone = status === 'Working' ? 'success' : (status === 'Failed' ? 'danger' : 'muted');
            return Object.assign({}, entry, {
                status,
                statusTone: tone,
                data_count: entry.data_count !== null && entry.data_count !== undefined
                    ? Number(entry.data_count)
                    : null,
                duration_ms: entry.duration_ms !== null && entry.duration_ms !== undefined
                    ? Number(entry.duration_ms)
                    : null
            });
        });

        const columns = [
            { id: 'action', label: 'Action', sortable: true },
            { id: 'category', label: 'Category', sortable: true },
            {
                id: 'status',
                label: 'Status',
                accessor: row => row.status,
                format: (value, row) => `<span class="status-badge status-${row.statusTone || 'muted'}">${value}</span>`,
                sortable: true
            },
            {
                id: 'data_type',
                label: 'Data Type',
                sortable: true
            },
            {
                id: 'data_count',
                label: 'Items',
                accessor: row => row.data_count ?? '',
                sortable: true
            },
            {
                id: 'duration_ms',
                label: 'Duration (ms)',
                accessor: row => row.duration_ms ?? '',
                sortable: true
            },
            { id: 'use_case', label: 'Use Case', sortable: true }
        ];

        if (catalogTableInstance) {
            catalogTableInstance.updateRows(normalizedRows);
        } else {
            catalogTableInstance = TableUtils.renderTable(tableContainer, {
                columns,
                rows: normalizedRows,
                pageSize: 50,
                defaultSort: { column: 'action', direction: 'asc' }
            });
        }
    }

    /**
     * Save settings from admin panel
     */
    async function saveSettings() {
        const dealerCode = document.getElementById('dealer-code').value;
        const dealerId = document.getElementById('dealer-id').value;
        const customerCode = document.getElementById('customer-code').value;
        const customerName = document.getElementById('customer-name').value;

        if (!dealerCode || !dealerId || !customerCode || !customerName) {
            showToast('All fields are required', 'error');
            return;
        }

        try {
            debugLog('Saving settings...', 'info');
            const enabledCards = Array.from(cardSelection);
            const response = await fetch('api/save-preferences.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    dealerCode: dealerCode,
                    dealerId: dealerId,
                    customerCode: customerCode,
                    customerName: customerName,
                    cards: enabledCards
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            state.dealerCode = dealerCode;
            state.dealerId = dealerId;
            state.customerCode = customerCode;
            state.customerName = customerName;
            const sanitizedLayout = sanitizeCardOrder(enabledCards);
            state.cards = sanitizedLayout.slice();
            cardSelection = new Set(state.cards);

            CardManager.setContext({
                dealerCode: state.dealerCode,
                dealerId: state.dealerId,
                customerCode: state.customerCode
            });
            if (state.cards.length) {
                applyCardLayout(state.cards, { persist: false, syncRemote: false });
                persistCardLayout(state.cards, false);
            }

            debugLog('Settings saved successfully', 'info');
            showToast('Settings saved successfully', 'success');
            await loadDashboard();

        } catch (error) {
            debugLog('Failed to save settings: ' + error.message, 'error');
            showToast('Failed to save settings: ' + error.message, 'error');
        }
    }

    /**
     * Load dashboard data
     */
    async function loadDashboard() {
        try {
            CardManager.setContext({
                dealerCode: state.dealerCode,
                dealerId: state.dealerId,
                customerCode: state.customerCode
            });
            if (state.cards.length) {
                applyCardLayout(state.cards, { persist: false, syncRemote: false });
            }
            await loadCustomerHeader();
            await CardManager.refreshAll();
        } catch (error) {
            showToast('Failed to load dashboard: ' + error.message, 'error');
        }
    }

    /**
     * Load customer header
     */
    async function loadCustomerHeader() {
        const container = document.getElementById('customer-header');
        container.innerHTML = '<div class="loading">Loading customer data...</div>';

        try {
            const response = await fetch('api/get-customer-dashboard.php?customerCode=' + state.customerCode);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            const dashboard = data.dashboard || {};
            const sdsData = dashboard.SdsDashboard || {};
            const mpsData = dashboard.MpsDashboardCustomer || {};
            const totalsSource = dashboard.MpsDashboardCustomer || dashboard;
            const resolvedName = state.customerName
                || totalsSource.CustomerDescription
                || totalsSource.Description
                || dashboard.CustomerDescription
                || 'Unknown Customer';
            state.customerName = resolvedName;

            container.innerHTML = `
                <div class="customer-banner">
                    <div class="customer-banner-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="customer-banner-content">
                        <div class="customer-banner-label">Customer</div>
                        <h2 class="customer-banner-name">${resolvedName}</h2>
                        <div class="customer-banner-code">${state.customerCode}</div>
                    </div>
                </div>

                    <div class="metrics-grid">
                    <div class="metric-card clickable" onclick="MPSM.expandDevices()" style="cursor:pointer">
                        <div class="metric-icon"><i class="fas fa-print"></i></div>
                        <div class="metric-value" id="banner-device-total">${totalsSource.TotalManagedDevices ?? 0}</div>
                        <div class="metric-label">Total Devices</div>
                    </div>
                    <div class="metric-card clickable" onclick="MPSM.expandOffline()" style="cursor:pointer">
                        <div class="metric-icon"><i class="fas fa-wifi-slash"></i></div>
                        <div class="metric-value" id="offline-count">0</div>
                        <div class="metric-label">Offline</div>
                    </div>
                    <div class="metric-card clickable status-warning" onclick="MPSM.expandAlerts()" style="cursor:pointer">
                        <div class="metric-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <div class="metric-value" id="alerts-count">0</div>
                        <div class="metric-label">Alerts</div>
                    </div>
                    <div class="metric-card clickable" onclick="MPSM.expandConnectors()" style="cursor:pointer">
                        <div class="metric-icon"><i class="fas fa-link"></i></div>
                        <div class="metric-value" id="connectors-count">${totalsSource.TotalConnectors ?? 0}</div>
                        <div class="metric-label">Connectors</div>
                        <div class="metric-meta status-pill status-muted" id="connectors-status">Checking…</div>
                    </div>
                </div>
            `;

            // Hydrate dynamic metrics after template injection
            const totalDevices = Number(
                totalsSource.TotalManagedDevices ??
                dashboard.TotalManagedDevices ??
                0
            );
            const totalConnectors = Number(
                totalsSource.TotalConnectors ??
                dashboard.TotalConnectors ??
                0
            );

            const offlineCandidates = [
                dashboard.NonCommunicatingDevices,
                sdsData.NonCommunicatingDevices,
                dashboard.OfflineDevices,
                totalsSource.OfflineDevices
            ];
            const offlineCount = Number(
                offlineCandidates.find(value => typeof value === 'number') ?? 0
            );

            const bannerDeviceCount = document.getElementById('banner-device-total');
            if (bannerDeviceCount) {
                bannerDeviceCount.textContent = totalDevices;
            }

            const dashboardDeviceCount = document.getElementById('device-count');
            if (dashboardDeviceCount) {
                dashboardDeviceCount.textContent = totalDevices;
            }

            const connectorsBannerEl = document.getElementById('connectors-count');
            if (connectorsBannerEl) {
                connectorsBannerEl.textContent = totalConnectors;
            }

            const connectorsHiddenEl = document.getElementById('connectors-hidden-count');
            if (connectorsHiddenEl) {
                connectorsHiddenEl.textContent = totalConnectors;
            }

            const offlineEl = document.getElementById('offline-count');
            if (offlineEl) {
                offlineEl.textContent = offlineCount;
            }

            const supplySummary = Array.isArray(totalsSource.SupplyAlerts)
                ? totalsSource.SupplyAlerts
                : Array.isArray(dashboard.SupplyAlerts)
                    ? dashboard.SupplyAlerts
                    : [];
            const toManageEntry = supplySummary.find(item => (item.Key || '').toLowerCase() === 'tomanage');
            const alertsTotal = Number(toManageEntry?.Value ?? 0);

            const alertsBannerEl = document.getElementById('alerts-count');
            if (alertsBannerEl) {
                alertsBannerEl.textContent = alertsTotal;
            }

            const alertsHiddenEl = document.getElementById('alert-count');
            if (alertsHiddenEl) {
                alertsHiddenEl.textContent = alertsTotal;
            }

            state.totalDevices = totalDevices;
            state.connectorsTotal = totalConnectors;
            state.offlineDevices = offlineCount;
            state.alertsTotal = alertsTotal;

        } catch (error) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load customer data</p>
                    <p class="error-message">${error.message}</p>
                </div>
            `;
        }
    }

    /**
     * Load devices
     */
    async function loadDevices() {
        const container = document.getElementById('device-list');
        const countEl = document.getElementById('device-count');

        if (!container || !countEl) {
            return;
        }

        // FIX BUG #1: Prevent concurrent device loading requests
        if (state.isLoadingDevices) {
            debugLog('Device loading already in progress, skipping duplicate request', 'info');
            return;
        }

        state.isLoadingDevices = true;
        container.innerHTML = '<div class="loading">Loading devices...</div>';

        try {
            const { devices, total } = await fetchAllDevices({
                customerCode: state.customerCode || '',
                dealerCode: state.dealerCode || '',
                dealerId: state.dealerId || '',
                sortColumn: 'AssetNumber',
                sortOrder: 'Asc'
            });

            if (!Array.isArray(devices) || devices.length === 0) {
                state.devices = [];
                hydrateDeviceLookup([]);
                state.totalDevices = Math.max(Number(state.totalDevices ?? 0), 0);
                state.offlineDevices = 0;
                updateMetricValue('device-count', state.totalDevices);
                updateMetricValue('banner-device-total', state.totalDevices);
                updateMetricValue('offline-count', state.offlineDevices);
                container.innerHTML = '<div class="empty-state">No devices found</div>';
                return;
            }

            state.devices = devices;
            hydrateDeviceLookup(state.devices);

            const resolvedTotal = Number.isFinite(Number(total))
                ? Number(total)
                : state.devices.length;
            const totalDevices = Math.max(
                resolvedTotal,
                state.devices.length,
                Number(state.totalDevices ?? 0)
            );

            state.totalDevices = totalDevices;
            updateMetricValue('device-count', totalDevices);
            updateMetricValue('banner-device-total', totalDevices);

            const offlineCount = state.devices.filter(device => device.IsOffline).length;
            state.offlineDevices = offlineCount;
            updateMetricValue('offline-count', offlineCount);

            container.innerHTML = '';
            const tableContainer = document.createElement('div');
            container.appendChild(tableContainer);

            TableUtils.renderTable(tableContainer, {
                columns: buildDeviceTableColumns(),
                rows: state.devices,
                pageSize: 50,
                defaultSort: { column: 'EquipmentId', direction: 'asc' },
                onRowClick: row => {
                    if (!row) {
                        return;
                    }
                    const directId = row.Id ?? row.IdInstalledProduct ?? row.DeviceId ?? null;
                    if (directId) {
                        openDeviceModal(directId);
                        return;
                    }
                    const equipmentId = getEquipmentIdFromDevice(row);
                    if (equipmentId && equipmentId !== 'N/A') {
                        const cached = state.deviceLookup.get(String(equipmentId).toLowerCase());
                        const cachedId = cached
                            ? (cached.Id ?? cached.IdInstalledProduct ?? cached.DeviceId ?? null)
                            : null;
                        if (cachedId) {
                            openDeviceModal(cachedId);
                        }
                    }
                }
            });

        } catch (error) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load devices</p>
                    <p class="error-message">${escapeHtml(error.message)}</p>
                </div>
            `;
        } finally {
            // FIX BUG #1: Always reset loading flag
            state.isLoadingDevices = false;
        }
    }

    /**
     * Load supply alerts
     */
    async function fetchAllDevices(options = {}) {
        const devices = [];
        const seenKeys = new Set();
        const pageRows = Math.max(1, Math.min(Number(options.pageRows || 200), 100));
        let pageNumber = Math.max(1, Number(options.pageNumber || 1));
        let totalExpected = null;
        let lastMeta = {};
        let safetyCounter = 0;

        // Debug logging for search
        if (options.allCustomers) {
            debugLog('fetchAllDevices called with allCustomers=true', 'info');
        }

        while (true) {
            safetyCounter += 1;
            if (safetyCounter > 100) {
                debugLog('Device pagination aborted after 100 iterations', 'warn');
                break;
            }

            const params = new URLSearchParams({
                customerCode: options.customerCode ?? state.customerCode ?? '',
                dealerCode: options.dealerCode ?? state.dealerCode ?? '',
                dealerId: options.dealerId ?? state.dealerId ?? '',
                pageRows: pageRows,
                pageNumber: pageNumber,
                sortColumn: options.sortColumn ?? 'AssetNumber',
                sortOrder: options.sortOrder ?? 'Asc'
            });

            // Add allCustomers parameter if specified
            if (options.allCustomers === true) {
                params.append('allCustomers', 'true');
            }

            debugLog(`Fetching page ${pageNumber}, pageRows=${pageRows}, allCustomers=${options.allCustomers ? 'true' : 'false'}`, 'info');

            const response = await fetch('api/get-devices.php?' + params.toString());
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Device request failed');
            }

            const meta = data.meta || {};
            lastMeta = meta;

            debugLog(`Page ${pageNumber} returned ${data.devices?.length || 0} devices, total=${data.total}`, 'info');

            const chunk = Array.isArray(data.devices)
                ? data.devices
                : Array.isArray(data.devices?.Items)
                    ? data.devices.Items
                    : [];

            const beforeCount = devices.length;

            chunk.forEach(device => {
                if (!device || typeof device !== 'object') {
                    return;
                }

                const identifier =
                    device.Id
                    ?? device.IdInstalledProduct
                    ?? device.DeviceId
                    ?? getEquipmentIdFromDevice(device)
                    ?? device.SerialNumber
                    ?? device.IpAddress
                    ?? `${pageNumber}-${devices.length}`;

                const key = String(identifier).toLowerCase();
                if (key && !seenKeys.has(key)) {
                    seenKeys.add(key);
                    devices.push(device);
                }
            });

            const addedThisPage = devices.length - beforeCount;

            const metaTotal = Number(
                meta.total_rows
                ?? meta.total_count
                ?? meta.total
                ?? data.total
                ?? totalExpected
            );

            if (Number.isFinite(metaTotal)) {
                totalExpected = metaTotal;
            }

            if (totalExpected !== null && devices.length >= totalExpected) {
                break;
            }

            if (!chunk.length) {
                break;
            }

            if (addedThisPage === 0) {
                debugLog(`No new devices returned for page ${pageNumber}; stopping pagination.`, 'warn');
                break;
            }

            if (chunk.length < pageRows) {
                break;
            }

            pageNumber += 1;
        }

        if (totalExpected === null) {
            totalExpected = devices.length;
        }

        return {
            devices,
            total: totalExpected,
            meta: lastMeta
        };
    }

    async function fetchAllSupplyAlerts(options = {}) {
        const alerts = [];
        const seenAlertKeys = new Set();
        let pageNumber = Math.max(1, Number(options.pageNumber || 1));
        const pageRows = Math.max(1, Math.min(Number(options.pageRows || 500), 500));
        let totalExpected = null;
        let lastMeta = {};
        let safetyCounter = 0;

        while (true) {
            safetyCounter += 1;
            if (safetyCounter > 200) {
                debugLog('Supply alert pagination aborted after 200 iterations', 'warn');
                break;
            }

            const params = new URLSearchParams({
                customerCode: options.customerCode ?? state.customerCode ?? '',
                dealerCode: options.dealerCode ?? state.dealerCode ?? '',
                sortColumn: options.sortColumn ?? 'InitialDate',
                sortOrder: options.sortOrder ?? 'Desc',
                pageRows: pageRows,
                pageNumber: pageNumber
            });

            const response = await fetch('api/get-supply-alerts.php?' + params.toString());
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Supply alerts request failed');
            }

            const meta = data.meta || {};
            lastMeta = meta;

            const chunk = Array.isArray(data.alerts)
                ? data.alerts
                : Array.isArray(data.alerts?.Items)
                    ? data.alerts.Items
                    : [];

            const beforeCount = alerts.length;

            chunk.forEach(alert => {
                if (!alert || typeof alert !== 'object') {
                    return;
                }

                const deviceId = alert.DeviceId
                    ?? alert.IdDevice
                    ?? alert.IdInstalledProduct
                    ?? alert.AssetNumber
                    ?? alert.SerialNumber
                    ?? '';

                const supplyKey = alert.SupplyTypeDescription
                    ?? alert.SupplyType
                    ?? alert.SupplierType
                    ?? '';

                const initialDate = alert.InitialDate ?? alert.GeneratedOn ?? alert.CreatedOn ?? '';
                const manageOption = alert.ManageOption ?? alert.InstallationOption ?? '';

                const keyParts = [deviceId, supplyKey, initialDate, manageOption]
                    .filter(part => part !== null && part !== undefined && part !== '');

                if (!keyParts.length) {
                    keyParts.push(JSON.stringify(alert));
                }

                const key = keyParts.join('|').toLowerCase();

                if (!seenAlertKeys.has(key)) {
                    seenAlertKeys.add(key);
                    alerts.push(alert);
                }
            });

            totalExpected = Number(
                meta.total_rows
                ?? meta.total_count
                ?? meta.total
                ?? data.total
                ?? totalExpected
                ?? alerts.length
            );

            const addedThisPage = alerts.length - beforeCount;

            if (totalExpected !== null && alerts.length >= totalExpected) {
                break;
            }

            if (!chunk.length) {
                break;
            }

            if (addedThisPage === 0) {
                debugLog(`No new supply alerts returned for page ${pageNumber}; stopping pagination.`, 'warn');
                break;
            }

            if (chunk.length < pageRows) {
                break;
            }

            pageNumber += 1;
        }

        return {
            alerts,
            total: totalExpected ?? alerts.length,
            meta: Object.assign({}, lastMeta, { page_rows: pageRows })
        };
    }

    async function loadSupplyAlerts() {
        const container = document.getElementById('supply-alerts');
        const countEl = document.getElementById('alert-count');

        if (!container || !countEl) {
            return;
        }

        container.innerHTML = '<div class="loading">Loading supply alerts...</div>';

        try {
            const { alerts } = await fetchAllSupplyAlerts();

            state.alerts = alerts;
            state.alertSummary = computeAlertDeviceSummary(alerts);
            state.alertsTotal = state.alertSummary.totalDevices;

            updateMetricValue('alerts-count', state.alertsTotal);
            updateMetricValue('alert-count', state.alertsTotal);

            if (!alerts.length) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No active supply alerts</p></div>';
                return;
            }

            container.innerHTML = '';
            const tableContainer = document.createElement('div');
            container.appendChild(tableContainer);

            TableUtils.renderTable(tableContainer, {
                columns: buildAlertTableColumns(),
                rows: alerts,
                pageSize: 50,
                defaultSort: { column: 'EquipmentId', direction: 'asc' },
                onRowClick: alert => {
                    const deviceId = findDeviceIdForAlert(alert);
                    if (deviceId) {
                        openDeviceModal(deviceId);
                    } else {
                        showToast('Device details are not available for this alert yet.', 'info');
                    }
                }
            });
        } catch (error) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load supply alerts</p>
                    <p class="error-message">${escapeHtml(error.message)}</p>
                </div>
            `;
        }
    }

    async function resolveDeviceIdForExports() {
        const getIdentifier = device => {
            if (!device || typeof device !== 'object') {
                return null;
            }
            return device.Id
                ?? device.IdInstalledProduct
                ?? device.DeviceId
                ?? device.IdDevice
                ?? null;
        };

        if (Array.isArray(state.devices) && state.devices.length) {
            const candidate = state.devices.map(getIdentifier).find(Boolean);
            if (candidate) {
                return candidate.toString();
            }
        }

        try {
            const params = new URLSearchParams({
                customerCode: state.customerCode || '',
                dealerCode: state.dealerCode || '',
                dealerId: state.dealerId || '',
                pageRows: 1,
                sortColumn: 'AssetNumber',
                sortOrder: 'Asc'
            });

            const response = await fetch('api/get-devices.php?' + params.toString());
            const data = await response.json();

            if (data.success && Array.isArray(data.devices) && data.devices.length) {
                const device = data.devices[0];
                if (!Array.isArray(state.devices) || !state.devices.length) {
                    state.devices = data.devices;
                }
                const identifier = getIdentifier(device);
                if (identifier) {
                    return identifier.toString();
                }
            }
        } catch (error) {
            debugLog('Failed to resolve device ID for export: ' + error.message, 'error');
        }

        return null;
    }

    function buildAlertTableColumns() {
        return [
            {
                id: 'EquipmentId',
                label: 'Equipment ID',
                accessor: row => getEquipmentIdFromAlert(row),
                sortable: true
            },
            {
                id: 'DeviceModel',
                label: 'Model',
                accessor: row => row.Product?.Model || row.ProductModel || row.DeviceModel || 'Unknown',
                sortable: true
            },
            {
                id: 'SupplyType',
                label: 'Supply',
                accessor: row => row.SupplyTypeDescription || row.SupplyType || 'Supply',
                sortable: true
            },
            {
                id: 'Level',
                label: 'Level',
                accessor: row => Number(row.Percentage ?? row.ActualResidualPercentage ?? row.InitialResidualPercentage ?? 0),
                sortable: true,
                format: (value, row) => renderAlertLevelChip(row, value)
            },
            {
                id: 'InitialDate',
                label: 'Date',
                accessor: row => row.InitialDate ? new Date(row.InitialDate).getTime() : 0,
                sortable: true,
                format: (value, row) => formatDateTime(row.InitialDate, { dateStyle: 'short', timeStyle: 'short' })
            },
            {
                id: 'ManageOption',
                label: 'Action',
                accessor: row => row.ManageOption || row.InstallationOption || 'Monitor',
                sortable: true,
                format: value => {
                    const option = (value || 'Monitor').toString();
                    const badgeClass = option.toLowerCase() === 'replace' ? 'badge-warning' : 'badge-info';
                    return `<span class="badge ${badgeClass}">${escapeHtml(option)}</span>`;
                }
            }
        ];
    }

    /**
     * Test system health
     */
    async function testSystemHealth() {
        const container = document.getElementById('health-status');
        container.innerHTML = '<div class="loading">Testing system health...</div>';

        try {
            const response = await fetch('api/system-health.php');
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            const health = data;

            container.innerHTML = `
                <div class="health-grid">
                    <div class="health-item">
                        <div class="health-icon ${health.database.connected ? 'status-success' : 'status-danger'}">
                            <i class="fas fa-${health.database.connected ? 'check-circle' : 'times-circle'}"></i>
                        </div>
                        <div class="health-content">
                            <div class="health-label">Database</div>
                            <div class="health-value">${health.database.connected ? 'Connected' : 'Disconnected'}</div>
                            ${health.database.error ? `<div class="health-error">${health.database.error}</div>` : ''}
                        </div>
                    </div>

                    <div class="health-item">
                        <div class="health-icon ${health.mpsApi.connected ? 'status-success' : 'status-danger'}">
                            <i class="fas fa-${health.mpsApi.connected ? 'check-circle' : 'times-circle'}"></i>
                        </div>
                        <div class="health-content">
                            <div class="health-label">MPS API</div>
                            <div class="health-value">${health.mpsApi.connected ? 'Connected' : 'Disconnected'}</div>
                            ${health.mpsApi.error ? `<div class="health-error">${health.mpsApi.error}</div>` : ''}
                        </div>
                    </div>

                    <div class="health-item">
                        <div class="health-icon ${health.session.active ? 'status-success' : 'status-warning'}">
                            <i class="fas fa-${health.session.active ? 'check-circle' : 'exclamation-circle'}"></i>
                        </div>
                        <div class="health-content">
                            <div class="health-label">Session</div>
                            <div class="health-value">${health.session.active ? 'Active' : 'Inactive'}</div>
                        </div>
                    </div>
                </div>

                <div class="health-timestamp">
                    Last checked: ${formatDateTime(health.timestamp)}
                </div>
            `;

        } catch (error) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Health check failed</p>
                    <p class="error-message">${error.message}</p>
                </div>
            `;
        }
    }

    /**
     * Load visitor logs
     */
    async function loadVisitorLogs() {
        const container = document.getElementById('visitor-logs');
        container.innerHTML = '<div class="loading">Loading visitor logs...</div>';

        try {
            const response = await fetch('api/get-visitor-logs.php?limit=10');
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            const logs = data.logs || [];

            if (logs.length === 0) {
                container.innerHTML = '<div class="empty-state">No visitor logs found</div>';
                return;
            }

            // Render visitor table
            const html = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Username</th>
                            <th>IP Address</th>
                            <th>Page</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${logs.map(log => `
                            <tr>
                                <td>${formatDateTime(log.visited_at)}</td>
                                <td>${log.username}</td>
                                <td><strong>${log.ip_address}</strong></td>
                                <td>${log.page_url}</td>
                                <td title="${log.user_agent}">${log.user_agent.substring(0, 50)}...</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <div class="health-timestamp">
                    Showing last ${logs.length} visits
                </div>
            `;

            container.innerHTML = html;

        } catch (error) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load visitor logs</p>
                    <p class="error-message">${error.message}</p>
                </div>
            `;
        }
    }

    /**
     * Logout
     */
    async function logout() {
        try {
            await fetch('api/logout.php', { method: 'POST' });
            window.location.href = 'login.html';
        } catch (error) {
            showToast('Logout failed: ' + error.message, 'error');
        }
    }

    /**
     * Show toast notification
     * Following Rule 10: Functions Must Be Short
     */
    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => toast.remove(), 3000);
    }

    // Initialize on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    /**
     * Open device detail modal
     */
    async function openDeviceModal(deviceId) {
        debugLog(`Opening device modal: ${deviceId}`, 'info');
        const modal = document.getElementById('device-modal');
        const modalBody = document.getElementById('modal-device-body');
        const modalName = document.getElementById('modal-device-name');

        modal.classList.add('active');
        modalBody.innerHTML = '<div class="loading">Loading device details...</div>';

        try {
            const matchesDevice = (candidate) => {
                if (!candidate || typeof candidate !== 'object') {
                    return false;
                }
                const identifiers = [
                    candidate.Id,
                    candidate.IdInstalledProduct,
                    candidate.DeviceId
                ];
                return identifiers.some(id => id !== undefined && id !== null && String(id) === String(deviceId));
            };

            let device = state.devices.find(matchesDevice);

            if (!device && state.deviceLookup.size < Math.max(Number(state.totalDevices ?? 0), state.deviceLookup.size || 0)) {
                try {
                    const refreshed = await fetchAllDevices({
                        customerCode: state.customerCode || '',
                        dealerCode: state.dealerCode || '',
                        dealerId: state.dealerId || '',
                        sortColumn: 'AssetNumber',
                        sortOrder: 'Asc'
                    });
                    if (Array.isArray(refreshed.devices) && refreshed.devices.length) {
                        state.devices = refreshed.devices;
                        hydrateDeviceLookup(state.devices);
                        const refreshedTotal = Number.isFinite(Number(refreshed.total))
                            ? Number(refreshed.total)
                            : state.devices.length;
                        state.totalDevices = Math.max(Number(state.totalDevices ?? 0), refreshedTotal, state.devices.length);
                        device = state.devices.find(matchesDevice);
                    }
                } catch (refreshError) {
                    debugLog('Device cache refresh failed: ' + refreshError.message, 'warn');
                }
            }

            if (!device) {
                throw new Error('Device not found');
            }

            modalName.textContent = device.Product?.Model || 'Device Details';

            const equipmentId = getEquipmentIdFromDevice(device);
            const detailContainerId = 'device-endpoint-sections';

            const resolveTonerValueForDevice = (keys) => {
                if (typeof resolveTonerValue === 'function') {
                    const resolved = resolveTonerValue(device, keys);
                    if (Number.isFinite(resolved)) {
                        return resolved;
                    }
                }
                for (const key of keys) {
                    const candidate = device[key];
                    if (candidate !== undefined && candidate !== null && Number.isFinite(Number(candidate))) {
                        return Number(candidate);
                    }
                }
                return null;
            };

            const tonerItems = [
                { color: 'black', label: 'Black Toner', value: resolveTonerValueForDevice(['BlackToner', 'BlackToner1', 'BlackToner2', 'BlackToner3']) },
                { color: 'cyan', label: 'Cyan Toner', value: resolveTonerValueForDevice(['CyanToner', 'CyanToner1']) },
                { color: 'magenta', label: 'Magenta Toner', value: resolveTonerValueForDevice(['MagentaToner', 'MagentaToner1']) },
                { color: 'yellow', label: 'Yellow Toner', value: resolveTonerValueForDevice(['YellowToner', 'YellowToner1']) }
            ].filter(item => item.value !== null && !Number.isNaN(item.value));

            const supplyMarkup = tonerItems.length
                ? tonerItems.map(item => `
                        <div class="supply-item supply-item--${item.color}">
                            <div class="supply-name">${item.label}</div>
                            <div class="supply-chip">
                                ${renderTonerChipMarkup(item.color, item.value)}
                            </div>
                        </div>
                    `).join('')
                : '<div class="supply-empty">No supply telemetry available</div>';

            const html = `
                <div class="device-snapshot">
                    <div class="snapshot-item">
                        <div class="snapshot-label">Equipment ID</div>
                        <div class="snapshot-value">${escapeHtml(equipmentId)}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Serial Number</div>
                        <div class="snapshot-value">${device.SerialNumber || 'N/A'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Asset Number</div>
                        <div class="snapshot-value">${device.AssetNumber || device.ExternalIdentifier || 'N/A'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">IP Address</div>
                        <div class="snapshot-value">${device.IpAddress || 'N/A'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">MAC Address</div>
                        <div class="snapshot-value">${device.MacAddress || 'N/A'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Location</div>
                        <div class="snapshot-value">${device.Note || device.OfficeDescription || 'N/A'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Status</div>
                        <div class="snapshot-value">
                            <span class="status-badge ${device.IsOffline ? 'status-danger' : 'status-success'}">
                                ${device.IsOffline ? 'Offline' : 'Online'}
                            </span>
                        </div>
                    </div>
                </div>

                <h3>Counters</h3>
                <div class="device-snapshot">
                    <div class="snapshot-item">
                        <div class="snapshot-label">Total Mono</div>
                        <div class="snapshot-value">${device.CounterMono?.toLocaleString() || '0'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Total Color</div>
                        <div class="snapshot-value">${device.CounterColor?.toLocaleString() || '0'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Monthly Mono</div>
                        <div class="snapshot-value">${device.MonthlyMonoVolume?.toLocaleString() || '0'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Monthly Color</div>
                        <div class="snapshot-value">${device.MonthlyColorVolume?.toLocaleString() || '0'}</div>
                    </div>
                </div>

                <h3>Supply Levels</h3>
                <div class="supply-grid">
                    ${supplyMarkup}
                </div>

                <h3>Device Information</h3>
                <div class="device-snapshot">
                    <div class="snapshot-item">
                        <div class="snapshot-label">Brand</div>
                        <div class="snapshot-value">${device.Product?.Brand || 'Unknown'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Model</div>
                        <div class="snapshot-value">${device.Product?.Model || 'Unknown'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Firmware</div>
                        <div class="snapshot-value">${device.Firmware || 'N/A'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Install Date</div>
                        <div class="snapshot-value">${formatDateTime(device.Install, { dateStyle: 'short' })}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Last Update</div>
                        <div class="snapshot-value">${formatDateTime(device.LastUpdate, { dateStyle: 'short', timeStyle: 'short' })}</div>
                    </div>
                </div>

                <div class="device-detail-sections">
                    <h3>Endpoint Data</h3>
                    <div id="${detailContainerId}">
                        <div class="loading">Loading endpoint data...</div>
                    </div>
                </div>
            `;

            modalBody.innerHTML = html;

            try {
                const detailPayload = await fetchDeviceDetails(deviceId);
                const detailContainer = document.getElementById(detailContainerId);

                if (detailContainer) {
                    detailContainer.innerHTML = renderEndpointSections(detailPayload.results || []);
                }
            } catch (detailError) {
                debugLog('Failed to load endpoint data: ' + detailError.message, 'error');
                const detailContainer = document.getElementById(detailContainerId);
                if (detailContainer) {
                    detailContainer.innerHTML = `
                        <div class="error-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Failed to load endpoint data</p>
                            <p class="error-message">${escapeHtml(detailError.message)}</p>
                        </div>
                    `;
                }
            }

        } catch (error) {
            debugLog('Failed to load device details: ' + error.message, 'error');
            modalBody.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load device details</p>
                    <p class="error-message">${error.message}</p>
                </div>
            `;
        }
    }

    /**
     * Close device modal
     */
    function closeDeviceModal() {
        const modal = document.getElementById('device-modal');
        modal.classList.remove('active');
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('device-modal');
        if (event.target === modal) {
            closeDeviceModal();
        }
    });

    /**
     * Expand to show all devices in modal
     */
    function buildDeviceTableColumns() {
        return [
            { id: 'EquipmentId', label: 'Equipment ID', accessor: row => getEquipmentIdFromDevice(row), sortable: true },
            { id: 'AssetNumber', label: 'Asset #', accessor: row => row.AssetNumber || '', hidden: true },
            {
                id: 'ProductModel',
                label: 'Model',
                accessor: row => (row.Product && row.Product.Model) || row.ProductModel || 'Unknown',
                sortable: true
            },
            { id: 'SerialNumber', label: 'Serial', sortable: true },
            { id: 'IpAddress', label: 'IP Address' },
            {
                id: 'OfficeDescription',
                label: 'Location',
                accessor: row => row.Note || row.OfficeDescription || '-'
            },
            {
                id: 'IsOffline',
                label: 'Status',
                sortable: true,
                accessor: row => row.IsOffline ? 'Offline' : 'Online',
                format: value => value === 'Offline'
                    ? '<span class="status-badge status-danger">Offline</span>'
                    : '<span class="status-badge status-success">Online</span>'
            },
            {
                id: 'BlackToner',
                label: 'K',
                accessor: row => typeof resolveTonerValue === 'function'
                    ? resolveTonerValue(row, ['BlackToner', 'BlackToner1', 'BlackToner2', 'BlackToner3'])
                    : row.BlackToner,
                format: value => typeof renderTonerBadge === 'function'
                    ? renderTonerBadge('black', value)
                    : (value ?? '--')
            },
            {
                id: 'CyanToner',
                label: 'C',
                accessor: row => typeof resolveTonerValue === 'function'
                    ? resolveTonerValue(row, ['CyanToner'])
                    : row.CyanToner,
                format: value => typeof renderTonerBadge === 'function'
                    ? renderTonerBadge('cyan', value)
                    : (value ?? '--')
            },
            {
                id: 'MagentaToner',
                label: 'M',
                accessor: row => typeof resolveTonerValue === 'function'
                    ? resolveTonerValue(row, ['MagentaToner'])
                    : row.MagentaToner,
                format: value => typeof renderTonerBadge === 'function'
                    ? renderTonerBadge('magenta', value)
                    : (value ?? '--')
            },
            {
                id: 'YellowToner',
                label: 'Y',
                accessor: row => typeof resolveTonerValue === 'function'
                    ? resolveTonerValue(row, ['YellowToner'])
                    : row.YellowToner,
                format: value => typeof renderTonerBadge === 'function'
                    ? renderTonerBadge('yellow', value)
                    : (value ?? '--')
            }
        ];
    }

    function expandDevices() {
        if (openCardModal('device-inventory')) {
            return;
        }
        debugLog('Expanding all devices view', 'info');
        const modal = document.getElementById('device-modal');
        const modalTitle = document.getElementById('modal-device-name');
        const modalBody = document.getElementById('modal-device-body');

        modalTitle.textContent = 'All Devices';

        if (state.devices.length === 0) {
            modalBody.innerHTML = '<div class="empty-state">No devices found</div>';
        } else {
            modalBody.innerHTML = '';
            const tableContainer = document.createElement('div');
            modalBody.appendChild(tableContainer);
            TableUtils.renderTable(tableContainer, {
                columns: buildDeviceTableColumns(),
                rows: state.devices,
                pageSize: 50,
                defaultSort: { column: 'EquipmentId', direction: 'asc' },
                onRowClick: row => {
                    if (row && row.Id) {
                        openDeviceModal(row.Id);
                    }
                }
            });
        }

        modal.classList.add('active');
    }

    /**
     * Expand to show offline devices in modal
     */
    function expandOffline() {
        debugLog('Expanding offline devices view', 'info');
        const modal = document.getElementById('device-modal');
        const modalTitle = document.getElementById('modal-device-name');
        const modalBody = document.getElementById('modal-device-body');

        const offlineDevices = state.devices.filter(d => d.IsOffline);

        modalTitle.textContent = 'Offline Devices';

        if (offlineDevices.length === 0) {
            modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No offline devices</p></div>';
        } else {
            modalBody.innerHTML = '';
            const tableContainer = document.createElement('div');
            modalBody.appendChild(tableContainer);
            const enriched = offlineDevices.map(device => Object.assign({}, device, {
                LastContactFormatted: formatDateTime(device.LastContact, { dateStyle: 'short', timeStyle: 'short' })
            }));
            TableUtils.renderTable(tableContainer, {
                columns: [
                    ...buildDeviceTableColumns(),
                    {
                        id: 'LastContactFormatted',
                        label: 'Last Seen',
                        accessor: row => row.LastContactFormatted || 'N/A',
                        sortable: true
                    }
                ],
                rows: enriched,
                pageSize: 50,
                defaultSort: { column: 'EquipmentId', direction: 'asc' },
                onRowClick: row => {
                    if (row && row.Id) {
                        openDeviceModal(row.Id);
                    }
                }
            });
        }

        modal.classList.add('active');
    }

    /**
     * Expand to show connector summary in modal
     */
    async function expandConnectors() {
        if (openCardModal('integrations')) {
            return;
        }
        debugLog('Expanding connectors view', 'info');
        const modal = document.getElementById('device-modal');
        const modalTitle = document.getElementById('modal-device-name');
        const modalBody = document.getElementById('modal-device-body');

        modalTitle.textContent = 'Connectors';
        modalBody.innerHTML = '<div class="loading">Loading connector data...</div>';
        modal.classList.add('active');

        try {
            const params = new URLSearchParams({
                customerCode: state.customerCode || ''
            });

            const response = await fetch('api/get-connectors.php?' + params.toString());
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            const summary = data.connectors || {};
            state.connectorsSummary = summary;

            if (!summary || Object.keys(summary).length === 0) {
                modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-info-circle"></i><p>No connector data available for this customer</p></div>';
                return;
            }

            modalBody.innerHTML = `
                <table class="table">
                    <tbody>
                        <tr>
                            <th scope="row">Windows Connectors</th>
                            <td>${summary.TotalWin ?? 0}</td>
                        </tr>
                        <tr>
                            <th scope="row">Windows (Last 24h)</th>
                            <td>${summary.LastDay ?? 0}</td>
                        </tr>
                        <tr>
                            <th scope="row">Windows (Last Period)</th>
                            <td>${summary.LastPeriod ?? 0}</td>
                        </tr>
                        <tr>
                            <th scope="row">Windows (Over Period)</th>
                            <td>${summary.OverPeriod ?? 0}</td>
                        </tr>
                        <tr>
                            <th scope="row">SDS Connectors</th>
                            <td>${summary.SdsTotalWin ?? 0}</td>
                        </tr>
                        <tr>
                            <th scope="row">Connector Clusters</th>
                            <td>${summary.TotalClusters ?? 0}</td>
                        </tr>
                        <tr>
                            <th scope="row">SDS Clusters</th>
                            <td>${summary.SdsTotalClusters ?? 0}</td>
                        </tr>
                    </tbody>
                </table>
            `;
        } catch (error) {
            modalBody.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load connector data</p>
                    <p class="error-message">${error.message}</p>
                </div>
            `;
        }
    }

    /**
     * Expand to show supply alerts in modal
     */
    async function expandAlerts() {
        if (openCardModal('supply-alerts')) {
            return;
        }
        debugLog('Expanding supply alerts view', 'info');
        const modal = document.getElementById('device-modal');
        const modalTitle = document.getElementById('modal-device-name');
        const modalBody = document.getElementById('modal-device-body');

        modalTitle.textContent = 'Supply Alerts';
        modalBody.innerHTML = '<div class="loading">Loading alerts...</div>';
        modal.classList.add('active');

        try {
            const { alerts } = await fetchAllSupplyAlerts();

            state.alerts = alerts;
            state.alertSummary = computeAlertDeviceSummary(alerts);
            state.alertsTotal = state.alertSummary.totalDevices;
            updateMetricValue('alerts-count', state.alertsTotal);
            updateMetricValue('alert-count', state.alertsTotal);

            if (alerts.length === 0) {
                modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No active supply alerts</p></div>';
            } else {
                modalBody.innerHTML = '';

                const supplyTypes = Array.from(new Set(alerts.map(alert => alert.SupplyTypeDescription || alert.SupplyType || 'Other')));
                const activeTypes = new Set(supplyTypes);

                const filterBar = document.createElement('div');
                filterBar.className = 'table-filters';
                filterBar.innerHTML = '<span class="filter-label"><i class="fas fa-filter"></i> Supply Types</span>';

                const tableContainer = document.createElement('div');
                const modalColumns = buildAlertTableColumns();
                modalColumns.splice(1, 0, {
                    id: 'SerialNumber',
                    label: 'Serial',
                    accessor: row => row.SerialNumber || row.DeviceSerialNumber || 'N/A',
                    sortable: true
                });

                const tableHandle = TableUtils.renderTable(tableContainer, {
                    columns: modalColumns,
                    rows: alerts,
                    pageSize: 50,
                    defaultSort: { column: 'EquipmentId', direction: 'asc' },
                    onRowClick: alert => {
                        const deviceId = findDeviceIdForAlert(alert);
                        if (deviceId) {
                            openDeviceModal(deviceId);
                        } else {
                            showToast('Device details are not available for this alert yet.', 'info');
                        }
                    }
                });

                supplyTypes.forEach(type => {
                    const label = document.createElement('label');
                    label.className = 'filter-checkbox';
                    label.innerHTML = `
                        <input type="checkbox" value="${escapeHtml(type)}" checked>
                        <span>${escapeHtml(type)}</span>
                    `;
                    const checkbox = label.querySelector('input');
                    checkbox.addEventListener('change', () => {
                        if (checkbox.checked) {
                            activeTypes.add(type);
                        } else {
                            activeTypes.delete(type);
                        }

                        if (!activeTypes.size) {
                            activeTypes.add(type);
                            checkbox.checked = true;
                            showToast('At least one supply type must remain visible', 'warning');
                            return;
                        }

                        const filtered = alerts.filter(alert => activeTypes.has(alert.SupplyTypeDescription || alert.SupplyType || 'Other'));
                        tableHandle.updateRows(filtered);
                    });
                    filterBar.appendChild(label);
                });

                modalBody.appendChild(filterBar);
                modalBody.appendChild(tableContainer);
            }
        } catch (error) {
            modalBody.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load alerts</p>
                    <p class="error-message">${error.message}</p>
                </div>
            `;
        }
    }

    /**
     * Global device search with autocomplete
     * Simple pagination approach - manually loop through pages
     */
    let globalSearchTimeout = null;
    let globalSearchCache = [];
    let globalSearchLastFetch = 0;
    const GLOBAL_SEARCH_CACHE_DURATION = 300000; // Cache for 5 minutes

    async function fetchAllDevicesForSearch() {
        // Return cached data if fresh
        const now = Date.now();
        if (globalSearchCache.length > 0 && (now - globalSearchLastFetch) < GLOBAL_SEARCH_CACHE_DURATION) {
            debugLog(`Using cached device data (${globalSearchCache.length} devices)`, 'info');
            return globalSearchCache;
        }

        debugLog('Fetching all devices for search...', 'info');

        // Simple approach: Manually paginate through all pages
        const allDevices = [];
        let pageNumber = 1;
        const maxPages = 50; // Safety limit

        while (pageNumber <= maxPages) {
            debugLog(`[SEARCH] Fetching page ${pageNumber}...`, 'info');
            const response = await fetch(`api/get-devices.php?pageRows=100&pageNumber=${pageNumber}&allCustomers=true`);

            if (!response.ok) {
                debugLog(`[SEARCH] ERROR: HTTP ${response.status} on page ${pageNumber}`, 'error');
                break;
            }

            const data = await response.json();
            debugLog(`[SEARCH] Page ${pageNumber} response: success=${data.success}, devices=${data.devices?.length || 0}, total=${data.total}`, 'info');

            if (!data.success || !data.devices || data.devices.length === 0) {
                debugLog(`[SEARCH] STOP: No devices on page ${pageNumber}`, 'warn');
                break;
            }

            allDevices.push(...data.devices);
            debugLog(`[SEARCH] Loaded page ${pageNumber}: ${data.devices.length} devices (cumulative: ${allDevices.length} of ${data.total})`, 'info');

            // Stop if we got fewer devices than requested (last page)
            if (data.devices.length < 100) {
                debugLog(`[SEARCH] STOP: Last page (got ${data.devices.length} devices)`, 'info');
                break;
            }

            // Stop if we've loaded all devices
            if (data.total && allDevices.length >= data.total) {
                debugLog(`[SEARCH] STOP: Loaded all devices (${allDevices.length} >= ${data.total})`, 'info');
                break;
            }

            pageNumber++;
        }

        globalSearchCache = allDevices;
        globalSearchLastFetch = now;
        debugLog(`Search cache loaded: ${allDevices.length} devices from ${pageNumber} pages`, 'info');
        return allDevices;
    }

    async function initGlobalDeviceSearch() {
        const searchInput = document.getElementById('global-device-search');
        const resultsContainer = document.getElementById('global-search-results');

        if (!searchInput || !resultsContainer) {
            return;
        }

        searchInput.addEventListener('input', async (e) => {
            const query = e.target.value.trim();

            if (query.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }

            // Debounce search
            clearTimeout(globalSearchTimeout);
            globalSearchTimeout = setTimeout(async () => {
                try {
                    resultsContainer.innerHTML = '<div class="search-loading"><i class="fas fa-spinner fa-spin"></i> Searching all devices...</div>';
                    resultsContainer.style.display = 'block';

                    // Fetch all devices across all customers with pagination
                    const devices = await fetchAllDevicesForSearch();
                    debugLog(`Searching ${devices.length} devices for "${query}"`, 'info');
                    const queryLower = query.toLowerCase();

                    // Search across multiple fields including ExternalIdentifier
                    let matchCount = 0;
                    const matches = devices.filter(device => {
                        const equipmentId = getEquipmentIdFromDevice(device).toLowerCase();
                        const serial = (device.SerialNumber || device.DeviceSerialNumber || '').toLowerCase();
                        const model = (device.ProductModel || device.Product?.Model || '').toLowerCase();
                        const customer = (device.CustomerDescription || '').toLowerCase();
                        const externalId = (device.ExternalIdentifier || device.ExternalId || '').toLowerCase();
                        const assetNumber = (device.AssetNumber || device.Asset || '').toLowerCase();

                        const isMatch = equipmentId.includes(queryLower) ||
                               serial.includes(queryLower) ||
                               model.includes(queryLower) ||
                               customer.includes(queryLower) ||
                               externalId.includes(queryLower) ||
                               assetNumber.includes(queryLower);

                        // Debug: Log first few matches
                        if (isMatch && matchCount < 3) {
                            debugLog(`Match found: ExtId="${externalId}", Serial="${serial}", Equipment="${equipmentId}"`, 'info');
                            matchCount++;
                        }

                        return isMatch;
                    }).slice(0, 10); // Limit to 10 results

                    debugLog(`Found ${matches.length} matches for "${query}"`, 'info');

                    if (matches.length === 0) {
                        resultsContainer.innerHTML = `<div class="search-empty">No devices found (searched ${devices.length} devices)</div>`;
                        return;
                    }

                    resultsContainer.innerHTML = matches.map(device => {
                        const equipmentId = escapeHtml(getEquipmentIdFromDevice(device));
                        const model = escapeHtml(device.ProductModel || device.Product?.Model || 'Unknown Model');
                        const customer = escapeHtml(device.CustomerDescription || 'Unknown Customer');
                        const serial = escapeHtml(device.SerialNumber || device.DeviceSerialNumber || '');
                        const deviceId = device.Id || device.IdInstalledProduct || device.DeviceId;

                        return `
                            <div class="search-result-item" data-device-id="${deviceId}">
                                <div class="search-result-main">
                                    <strong>${equipmentId}</strong>
                                    <span class="search-result-model">${model}</span>
                                </div>
                                <div class="search-result-sub">
                                    ${customer}${serial ? ' • SN: ' + serial : ''}
                                </div>
                            </div>
                        `;
                    }).join('');

                    // Add click handlers
                    resultsContainer.querySelectorAll('.search-result-item').forEach(item => {
                        item.addEventListener('click', () => {
                            const deviceId = item.dataset.deviceId;
                            if (deviceId) {
                                openDeviceModal(deviceId);
                                searchInput.value = '';
                                resultsContainer.style.display = 'none';
                            }
                        });
                    });

                } catch (error) {
                    resultsContainer.innerHTML = `<div class="search-error">Search error: ${escapeHtml(error.message)}</div>`;
                }
            }, 300);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });
    }

    // Public API
    return {
        loadDashboard,
        showToast,
        openDeviceModal,
        closeDeviceModal,
        expandDevices,
        expandOffline,
        expandConnectors,
        expandAlerts,
        fetchAllDevices,
        fetchAllSupplyAlerts,
        hydrateDeviceLookup,
        resolveDeviceIdForExports,
        formatDateTime,
        initGlobalDeviceSearch
    };

})();

// Expose functions to window for onclick handlers
window.MPSM = MPSM;
window.closeDeviceModal = () => MPSM.closeDeviceModal();
