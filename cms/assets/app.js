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
        customerCode: null,
        devices: []
    };

    /**
     * Initialize application
     * Following Rule 22: No Callback Hell (using async/await)
     */
    async function init() {
        setupEventListeners();
        loadTheme();

        try {
            await loadPreferences();
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
            const response = await fetch('api/get-preferences.php');
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            const prefs = data.preferences;
            state.customerCode = prefs.customerCode;
            state.theme = prefs.theme;

        } catch (error) {
            console.error('Failed to load preferences:', error);
            // Use defaults
            state.customerCode = 'W9OPXL0YDK';
        }
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
     * Save settings from admin panel
     */
    async function saveSettings() {
        const customerCode = document.getElementById('customer-code').value;
        const customerName = document.getElementById('customer-name').value;

        if (!customerCode || !customerName) {
            showToast('Customer code and name are required', 'error');
            return;
        }

        try {
            const response = await fetch('api/save-preferences.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    customerCode: customerCode,
                    customerName: customerName
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            state.customerCode = customerCode;
            showToast('Settings saved successfully', 'success');
            await loadDashboard();

        } catch (error) {
            showToast('Failed to save settings: ' + error.message, 'error');
        }
    }

    /**
     * Load dashboard data
     */
    async function loadDashboard() {
        try {
            await Promise.all([
                loadCustomerHeader(),
                loadDevices()
            ]);
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

            const dashboard = data.dashboard;
            const sdsData = dashboard.SdsDashboard || {};

            container.innerHTML = `
                <div class="customer-banner">
                    <div class="customer-banner-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="customer-banner-content">
                        <div class="customer-banner-label">Customer</div>
                        <h2 class="customer-banner-name">${dashboard.CustomerName || 'Unknown'}</h2>
                    </div>
                </div>

                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-icon"><i class="fas fa-print"></i></div>
                        <div class="metric-value">${sdsData.TotalDevices || 0}</div>
                        <div class="metric-label">Total Devices</div>
                    </div>
                    <div class="metric-card ${sdsData.DevicesWithErrors > 0 ? 'status-danger' : 'status-success'}">
                        <div class="metric-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="metric-value">${sdsData.DevicesWithErrors || 0}</div>
                        <div class="metric-label">Errors</div>
                    </div>
                    <div class="metric-card ${sdsData.DevicesWithWarnings > 0 ? 'status-warning' : 'status-success'}">
                        <div class="metric-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <div class="metric-value">${sdsData.DevicesWithWarnings || 0}</div>
                        <div class="metric-label">Warnings</div>
                    </div>
                    <div class="metric-card ${sdsData.NonCommunicatingDevices > 0 ? 'status-warning' : 'status-success'}">
                        <div class="metric-icon"><i class="fas fa-wifi-slash"></i></div>
                        <div class="metric-value">${sdsData.NonCommunicatingDevices || 0}</div>
                        <div class="metric-label">Offline</div>
                    </div>
                </div>
            `;

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
            const response = await fetch('api/get-devices.php?customerCode=' + state.customerCode);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            state.devices = data.devices || [];
            countEl.textContent = state.devices.length;

            if (state.devices.length === 0) {
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
                            <tr>
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

    // Public API
    return {
        loadDashboard,
        showToast
    };

})();
