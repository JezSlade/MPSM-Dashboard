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
        currentDevicePage: 1,
        currentAlertPage: 1,
        debugLogs: []
    };

    let customerSearchTimeout = null;
    let cardSelection = new Set();
    let cardsInitialized = false;
    let userTableInstance = null;

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
            state.devices = snapshot.context?.devices || [];
            const totalDevices = snapshot.headline ? snapshot.headline.value : state.devices.length;
            updateMetricValue('device-count', totalDevices);
            updateMetricValue('banner-device-total', totalDevices);
            const offlineCount = state.devices.filter(device => device.IsOffline).length;
            updateMetricValue('offline-count', offlineCount);
        }

        if (cardId === 'supply-alerts') {
            state.alerts = snapshot.context?.alerts || [];
            const totalAlerts = snapshot.headline ? snapshot.headline.value : state.alerts.length;
            updateMetricValue('alerts-count', totalAlerts);
            updateMetricValue('alert-count', totalAlerts);
        }

        if (cardId === 'integrations') {
            state.connectorsSummary = snapshot.context?.integrations || [];
            const connectorTotal = snapshot.headline ? snapshot.headline.value : state.connectorsSummary.length;
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
            state.connectorsSummary = totalConnectors;
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

            const devices = Array.isArray(data.devices) ? data.devices : [];
            state.devices = devices;

            const totalCount = Number(data.total ?? devices.length ?? 0);

            countEl.textContent = totalCount;
            const bannerDeviceCount = document.getElementById('banner-device-total');
            if (bannerDeviceCount) {
                bannerDeviceCount.textContent = totalCount;
            }

            // Update offline count in header
            const offlineCount = devices.filter(d => d.IsOffline).length;
            const offlineEl = document.getElementById('offline-count');
            if (offlineEl) offlineEl.textContent = offlineCount;

            if (devices.length === 0) {
                container.innerHTML = '<div class="empty-state">No devices found</div>';
                return;
            }

            // Render device table
            const html = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Asset #</th>
                            <th>Model</th>
                            <th>IP Address</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${state.devices.map(device => `
                            <tr onclick="MPSM.openDeviceModal('${device.Id}')" style="cursor: pointer;">
                                <td>${device.AssetNumber || device.SerialNumber || 'N/A'}</td>
                                <td>${device.Product?.Model || 'Unknown'}</td>
                                <td>${device.IpAddress || 'N/A'}</td>
                                <td>${device.Note || device.OfficeDescription || '-'}</td>
                                <td>
                                    <span class="status-badge ${device.IsOffline ? 'status-danger' : 'status-success'}">
                                        ${device.IsOffline ? 'Offline' : 'Online'}
                                    </span>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;

            container.innerHTML = html;

        } catch (error) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load devices</p>
                    <p class="error-message">${error.message}</p>
                </div>
            `;
            countEl.textContent = '0';
        }
    }

    /**
     * Load supply alerts
     */
    async function loadSupplyAlerts() {
        const container = document.getElementById('supply-alerts');
        const countEl = document.getElementById('alert-count');

        container.innerHTML = '<div class="loading">Loading supply alerts...</div>';

        try {
            const params = new URLSearchParams({
                customerCode: state.customerCode || '',
                dealerCode: state.dealerCode || '',
                pageRows: 20,
                sortColumn: 'InitialDate',
                sortOrder: 'Desc'
            });

            const response = await fetch('api/get-supply-alerts.php?' + params.toString());
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            const alertPayload = data.alerts ?? [];
            let alerts = [];

            if (Array.isArray(alertPayload)) {
                alerts = alertPayload;
            } else if (Array.isArray(alertPayload.Items)) {
                alerts = alertPayload.Items;
            }

            state.alerts = alerts;
            const displayedCount = state.alertsTotal ?? alerts.length;
            countEl.textContent = displayedCount;

            if (alerts.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No active supply alerts</p></div>';
                return;
            }

            // Render alerts table
            const html = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Priority</th>
                            <th>Device</th>
                            <th>Supply Type</th>
                            <th>Level</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${alerts.map(alert => {
                            const level = alert.Percentage ?? alert.ActualResidualPercentage ?? alert.InitialResidualPercentage ?? 0;
                            const priority = level < 10 ? 'HIGH' : level < 25 ? 'MED' : 'LOW';
                            const priorityClass = level < 10 ? 'status-danger' : level < 25 ? 'status-warning' : 'status-success';
                            const date = alert.InitialDate ? new Date(alert.InitialDate).toLocaleDateString() : 'N/A';
                            const manageOption = alert.ManageOption || alert.InstallationOption || 'Monitor';

                            return `
                                <tr>
                                    <td><span class="status-badge ${priorityClass}">${priority}</span></td>
                                    <td>${alert.SerialNumber || alert.DeviceSerialNumber || 'Unknown'}</td>
                                    <td>${alert.SupplyTypeDescription || alert.SupplyType || 'Supply'}</td>
                                    <td>
                                        <div class="toner-bar">
                                            <div class="toner-fill ${priorityClass}" style="width: ${level}%"></div>
                                            <span class="toner-text">${level}%</span>
                                        </div>
                                    </td>
                                    <td>${date}</td>
                                    <td>${manageOption}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
                <div class="card-footer">
                    <span>${alerts.length} active alerts | <a href="#" id="view-all-alerts">View All</a></span>
                </div>
            `;

            container.innerHTML = html;

        } catch (error) {
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load supply alerts</p>
                    <p class="error-message">${error.message}</p>
                </div>
            `;
            countEl.textContent = '0';
        }
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
    function openDeviceModal(deviceId) {
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

            const html = `
                <div class="device-snapshot">
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
            `;

            modalBody.innerHTML = html;

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
            modalBody.innerHTML = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Asset #</th>
                            <th>Model</th>
                            <th>IP Address</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${state.devices.map(device => `
                            <tr onclick="MPSM.openDeviceModal('${device.Id}')" style="cursor: pointer;">
                                <td>${device.AssetNumber || device.SerialNumber || 'N/A'}</td>
                                <td>${device.Product?.Model || 'Unknown'}</td>
                                <td>${device.IpAddress || 'N/A'}</td>
                                <td>${device.Note || device.OfficeDescription || '-'}</td>
                                <td>
                                    <span class="status-badge ${device.IsOffline ? 'status-danger' : 'status-success'}">
                                        ${device.IsOffline ? 'Offline' : 'Online'}
                                    </span>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
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
            modalBody.innerHTML = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Asset #</th>
                            <th>Model</th>
                            <th>IP Address</th>
                            <th>Location</th>
                            <th>Last Seen</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${offlineDevices.map(device => `
                            <tr onclick="MPSM.openDeviceModal('${device.Id}')" style="cursor: pointer;">
                                <td>${device.AssetNumber || device.SerialNumber || 'N/A'}</td>
                                <td>${device.Product?.Model || 'Unknown'}</td>
                                <td>${device.IpAddress || 'N/A'}</td>
                                <td>${device.Note || device.OfficeDescription || '-'}</td>
                                <td>${device.LastContact ? new Date(device.LastContact).toLocaleString() : 'N/A'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
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
window.closeDeviceModal = () => MPSM.closeDeviceModal();
