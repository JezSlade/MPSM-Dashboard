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
        connectorsSummary: null,
        totalDevices: 0,
        offlineDevices: 0,
        connectorsTotal: 0,
        alertsTotal: 0,
        currentDevicePage: 1,
        currentAlertPage: 1,
        debugLogs: [],
        deviceDetails: {},
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

        const asset = alert.AssetNumber ?? alert.Asset ?? '';
        const external = alert.ExternalIdentifier ?? alert.ExternalId ?? '';
        const fallback = alert.SerialNumber ?? alert.DeviceSerialNumber ?? alert.EquipmentId ?? '';

        return resolveEquipmentIdFromParts(asset, external, fallback);
    }

    if (typeof window !== 'undefined') {
        window.resolveEquipmentIdFromParts = window.resolveEquipmentIdFromParts || resolveEquipmentIdFromParts;
        window.getEquipmentIdFromDevice = window.getEquipmentIdFromDevice || getEquipmentIdFromDevice;
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

        if (typeof value === 'string') {
            const isoDatePattern = /^\d{4}-\d{2}-\d{2}T/;
            if (isoDatePattern.test(value)) {
                const date = new Date(value);
                if (!Number.isNaN(date.getTime())) {
                    return date.toLocaleString();
                }
            }
            return value;
        }

        if (Array.isArray(value)) {
            return `${value.length} item(s)`;
        }

        if (typeof value === 'object') {
            let serialized = JSON.stringify(value);
            if (serialized.length > 200) {
                serialized = serialized.slice(0, 197) + '...';
            }
            return serialized;
        }

        return String(value);
    }

    function renderKeyValueSnapshot(data, limit = 8) {
        if (!data || typeof data !== 'object') {
            return '<div class="snapshot-value">No details available</div>';
        }

        const entries = Object.entries(data).slice(0, limit);
        if (!entries.length) {
            return '<div class="snapshot-value">No details available</div>';
        }

        return `
            <div class="device-snapshot">
                ${entries.map(([key, value]) => `
                    <div class="snapshot-item">
                        <div class="snapshot-label">${escapeHtml(key)}</div>
                        <div class="snapshot-value">${escapeHtml(formatDetailValue(value))}</div>
                    </div>
                `).join('')}
            </div>
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

            const firstObject = data.find(item => item && typeof item === 'object' && !Array.isArray(item));
            if (firstObject) {
                return renderKeyValueSnapshot(firstObject);
            }

            return `<div class="snapshot-value">${escapeHtml(formatDetailValue(data[0]))}</div>`;
        }

        if (typeof data === 'object') {
            return renderKeyValueSnapshot(data);
        }

        return `<div class="snapshot-value">${escapeHtml(formatDetailValue(data))}</div>`;
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
            state.cards = Array.isArray(prefs.cards) ? prefs.cards : [];
            cardSelection = state.cards.length ? new Set(state.cards) : cardSelection;

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
            if (snapshotDevices.length) {
                state.devices = snapshotDevices;
            }

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

            const headlineAlerts = Number(snapshot.headline?.value ?? 0);
            const contextAlerts = Number(snapshot.context?.total ?? 0);
            const fallbackAlerts = Number(state.alertsTotal ?? 0);
            const totalAlerts = Math.max(headlineAlerts, contextAlerts, fallbackAlerts, state.alerts?.length ?? 0);

            state.alertsTotal = totalAlerts;
            updateMetricValue('alerts-count', totalAlerts);
            updateMetricValue('alert-count', totalAlerts);
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

                    if (!checked && cardSelection.size <= 1 && cardSelection.has(cardId)) {
                        checkbox.checked = true;
                        showToast('At least one card must remain enabled', 'warning');
                        return;
                    }

                    if (checked) {
                        cardSelection.add(cardId);
                        label.classList.add('active');
                    } else {
                        cardSelection.delete(cardId);
                        label.classList.remove('active');
                    }

                    CardManager.setEnabledCards(Array.from(cardSelection));
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
                <td>${user.created_at ? new Date(user.created_at).toLocaleString() : 'N/A'}</td>
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
        if (!cardsInitialized) {
            CardManager.init({
                container: '#dashboard-card-container',
                preferencesProvider: () => ({ cards: Array.from(cardSelection) }),
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

        if (cardSelection.size) {
            CardManager.setEnabledCards(Array.from(cardSelection));
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
        const lastTested = stats.test_date
            ? (() => {
                const date = new Date(stats.test_date);
                return Number.isNaN(date.getTime()) ? null : date.toLocaleString();
            })()
            : null;

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
                pageSize: 25,
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
            state.cards = enabledCards;
            cardSelection = new Set(enabledCards);

            CardManager.setContext({
                dealerCode: state.dealerCode,
                dealerId: state.dealerId,
                customerCode: state.customerCode
            });
            if (cardSelection.size) {
                CardManager.setEnabledCards(Array.from(cardSelection));
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

        container.innerHTML = '<div class="loading">Loading devices...</div>';

        try {
            const params = new URLSearchParams({
                customerCode: state.customerCode || '',
                dealerCode: state.dealerCode || '',
                dealerId: state.dealerId || '',
                pageRows: 50,
                sortColumn: 'AssetNumber',
                sortOrder: 'Asc'
            });

            const response = await fetch('api/get-devices.php?' + params.toString());
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            const meta = data.meta || {};
            const fetchedDevices = Array.isArray(data.devices) ? data.devices : [];

            if (fetchedDevices.length) {
                state.devices = fetchedDevices;
            }

            const totalCount = Number(
                meta.total_rows
                ?? meta.total_count
                ?? meta.total
                ?? data.total
                ?? (state.devices ? state.devices.length : 0)
                ?? 0
            );

            state.totalDevices = Math.max(Number(state.totalDevices ?? 0), totalCount);
            updateMetricValue('device-count', state.totalDevices);
            updateMetricValue('banner-device-total', state.totalDevices);

            const offlineFromFetch = fetchedDevices.filter(device => device.IsOffline).length;
            const offlineCount = Math.max(Number(state.offlineDevices ?? 0), offlineFromFetch);
            state.offlineDevices = offlineCount;
            updateMetricValue('offline-count', offlineCount);

            const displayDevices = (state.devices && state.devices.length)
                ? state.devices
                : fetchedDevices;

            if (!displayDevices.length) {
                container.innerHTML = '<div class="empty-state">No devices found</div>';
                return;
            }

            container.innerHTML = '';
            const tableContainer = document.createElement('div');
            container.appendChild(tableContainer);

            TableUtils.renderTable(tableContainer, {
                columns: buildDeviceTableColumns(),
                rows: displayDevices,
                pageSize: 50,
                defaultSort: { column: 'EquipmentId', direction: 'asc' },
                onRowClick: row => {
                    if (row && row.Id) {
                        openDeviceModal(row.Id);
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
        }
    }

    /**
     * Load supply alerts
     */
    async function loadSupplyAlerts() {
        const container = document.getElementById('supply-alerts');
        const countEl = document.getElementById('alert-count');

        if (!container || !countEl) {
            return;
        }

        container.innerHTML = '<div class="loading">Loading supply alerts...</div>';

        try {
            const params = new URLSearchParams({
                customerCode: state.customerCode || '',
                dealerCode: state.dealerCode || '',
                pageRows: 50,
                sortColumn: 'InitialDate',
                sortOrder: 'Desc'
            });

            const response = await fetch('api/get-supply-alerts.php?' + params.toString());
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Supply alerts request failed');
            }

            const meta = data.meta || {};
            const alertPayload = Array.isArray(data.alerts) ? data.alerts : [];

            state.alerts = alertPayload;

            const totalAlerts = Number(
                meta.total_rows
                ?? meta.total_count
                ?? meta.total
                ?? data.total
                ?? (state.alerts ? state.alerts.length : 0)
                ?? 0
            );

            state.alertsTotal = Math.max(Number(state.alertsTotal ?? 0), totalAlerts);
            updateMetricValue('alerts-count', state.alertsTotal);
            updateMetricValue('alert-count', state.alertsTotal);

            if (!state.alerts.length) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No active supply alerts</p></div>';
                return;
            }

            container.innerHTML = '';
            const tableContainer = document.createElement('div');
            container.appendChild(tableContainer);

            TableUtils.renderTable(tableContainer, {
                columns: buildAlertTableColumns(),
                rows: state.alerts,
                pageSize: 50,
                defaultSort: { column: 'EquipmentId', direction: 'asc' }
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
                format: value => {
                    const level = Number(value);
                    const safeLevel = Number.isFinite(level) ? Math.max(0, Math.min(100, level)) : 0;
                    const levelClass = safeLevel <= 10 ? 'status-danger'
                        : safeLevel <= 20 ? 'status-warning'
                        : 'status-success';
                    return `
                        <div class="toner-bar">
                            <div class="toner-fill ${levelClass}" style="width: ${safeLevel}%"></div>
                            <span class="toner-text">${safeLevel}%</span>
                        </div>
                    `;
                }
            },
            {
                id: 'InitialDate',
                label: 'Date',
                accessor: row => row.InitialDate ? new Date(row.InitialDate).toLocaleDateString() : 'N/A',
                sortable: true
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
                    Last checked: ${new Date(health.timestamp).toLocaleString()}
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
                                <td>${new Date(log.visited_at).toLocaleString()}</td>
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
            const device = state.devices.find(d => d.Id === deviceId);
            if (!device) {
                throw new Error('Device not found');
            }

            modalName.textContent = device.Product?.Model || 'Device Details';

            const equipmentId = getEquipmentIdFromDevice(device);
            const detailContainerId = 'device-endpoint-sections';

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
                    ${device.BlackToner !== null ? `
                        <div class="supply-item">
                            <div class="supply-name">Black Toner</div>
                            <div class="toner-bar">
                                <div class="toner-fill ${device.BlackToner < 10 ? 'status-danger' : device.BlackToner < 25 ? 'status-warning' : 'status-success'}" style="width: ${device.BlackToner}%"></div>
                                <span class="toner-text">${device.BlackToner}%</span>
                            </div>
                        </div>
                    ` : ''}
                    ${device.CyanToner !== null ? `
                        <div class="supply-item">
                            <div class="supply-name">Cyan Toner</div>
                            <div class="toner-bar">
                                <div class="toner-fill ${device.CyanToner < 10 ? 'status-danger' : device.CyanToner < 25 ? 'status-warning' : 'status-success'}" style="width: ${device.CyanToner}%"></div>
                                <span class="toner-text">${device.CyanToner}%</span>
                            </div>
                        </div>
                    ` : ''}
                    ${device.MagentaToner !== null ? `
                        <div class="supply-item">
                            <div class="supply-name">Magenta Toner</div>
                            <div class="toner-bar">
                                <div class="toner-fill ${device.MagentaToner < 10 ? 'status-danger' : device.MagentaToner < 25 ? 'status-warning' : 'status-success'}" style="width: ${device.MagentaToner}%"></div>
                                <span class="toner-text">${device.MagentaToner}%</span>
                            </div>
                        </div>
                    ` : ''}
                    ${device.YellowToner !== null ? `
                        <div class="supply-item">
                            <div class="supply-name">Yellow Toner</div>
                            <div class="toner-bar">
                                <div class="toner-fill ${device.YellowToner < 10 ? 'status-danger' : device.YellowToner < 25 ? 'status-warning' : 'status-success'}" style="width: ${device.YellowToner}%"></div>
                                <span class="toner-text">${device.YellowToner}%</span>
                            </div>
                        </div>
                    ` : ''}
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
                        <div class="snapshot-value">${device.Install ? new Date(device.Install).toLocaleDateString() : 'N/A'}</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-label">Last Update</div>
                        <div class="snapshot-value">${device.LastUpdate ? new Date(device.LastUpdate).toLocaleString() : 'N/A'}</div>
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
                LastContactFormatted: device.LastContact ? new Date(device.LastContact).toLocaleString() : 'N/A'
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
                pageSize: 25,
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
            const params = new URLSearchParams({
                customerCode: state.customerCode || '',
                dealerCode: state.dealerCode || '',
                pageRows: 100,
                sortColumn: 'InitialDate',
                sortOrder: 'Desc'
            });

            const response = await fetch('api/get-supply-alerts.php?' + params.toString());
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            const payload = data.alerts ?? [];
            const alerts = Array.isArray(payload)
                ? payload
                : Array.isArray(payload.Items)
                    ? payload.Items
                    : [];

            if (alerts.length === 0) {
                modalBody.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No active supply alerts</p></div>';
            } else {
                modalBody.innerHTML = `
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Supply</th>
                                <th>Level</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${alerts.map(alert => {
                                const level = Number(alert.Percentage ?? alert.ActualResidualPercentage ?? alert.InitialResidualPercentage ?? 0);
                                const levelClass = level <= 10 ? 'toner-critical' : level <= 20 ? 'toner-low' : '';
                                const model = alert.Product?.Model || alert.ProductModel || 'Unknown';
                                const supply = alert.SupplyTypeDescription || alert.SupplyType || 'N/A';
                                const manageOption = alert.ManageOption || alert.InstallationOption || 'Monitor';
                                return `
                                    <tr>
                                        <td>${model}</td>
                                        <td>${supply}</td>
                                        <td>
                                            <div class="toner-bar-container">
                                                <div class="toner-bar ${levelClass}">
                                                    <div class="toner-fill" style="width: ${level}%"></div>
                                                </div>
                                                <span class="toner-label">${level}%</span>
                                            </div>
                                        </td>
                                        <td>${alert.InitialDate ? new Date(alert.InitialDate).toLocaleDateString() : 'N/A'}</td>
                                        <td>
                                            <span class="badge ${manageOption === 'Replace' ? 'badge-warning' : 'badge-info'}">
                                                ${manageOption}
                                            </span>
                                        </td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                `;
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

    // Public API
    return {
        loadDashboard,
        showToast,
        openDeviceModal,
        closeDeviceModal,
        expandDevices,
        expandOffline,
        expandConnectors,
        expandAlerts
    };

})();

// Expose functions to window for onclick handlers
window.MPSM = MPSM;
window.closeDeviceModal = () => MPSM.closeDeviceModal();
