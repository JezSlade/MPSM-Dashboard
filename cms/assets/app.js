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
        devices: [],
        currentDevicePage: 1,
        currentAlertPage: 1,
        debugLogs: []
    };

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

            debugLog(`Preferences loaded: ${state.customerCode}`, 'info');

        } catch (error) {
            debugLog('Failed to load preferences: ' + error.message, 'error');
            // Use defaults
            state.dealerCode = 'NY06AGDWUQ';
            state.dealerId = 'SZ13qRwU5GtFLj0i_CbEgQ2';
            state.customerCode = 'W9OPXL0YDK';
            state.customerName = 'CAPE FEAR VALLEY MED CTR.';
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
            const response = await fetch('api/save-preferences.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    dealerCode: dealerCode,
                    dealerId: dealerId,
                    customerCode: customerCode,
                    customerName: customerName
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
            await Promise.all([
                loadCustomerHeader(),
                loadDevices(),
                loadSupplyAlerts()
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
            const response = await fetch('api/get-supply-alerts.php?customerCode=' + state.customerCode + '&pageRows=20');
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            const alerts = data.alerts?.Items || [];
            countEl.textContent = alerts.length;

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
                            const level = alert.Percentage || 0;
                            const priority = level < 10 ? 'HIGH' : level < 25 ? 'MED' : 'LOW';
                            const priorityClass = level < 10 ? 'status-danger' : level < 25 ? 'status-warning' : 'status-success';
                            const date = alert.InitialDate ? new Date(alert.InitialDate).toLocaleDateString() : 'N/A';

                            return `
                                <tr>
                                    <td><span class="status-badge ${priorityClass}">${priority}</span></td>
                                    <td>${alert.DeviceSerialNumber || 'Unknown'}</td>
                                    <td>${alert.SupplyType || 'Supply'}</td>
                                    <td>
                                        <div class="toner-bar">
                                            <div class="toner-fill ${priorityClass}" style="width: ${level}%"></div>
                                            <span class="toner-text">${level}%</span>
                                        </div>
                                    </td>
                                    <td>${date}</td>
                                    <td>${alert.StatusText || 'Pending'}</td>
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

    // Public API
    return {
        loadDashboard,
        showToast,
        openDeviceModal,
        closeDeviceModal
    };

})();

// Expose functions to window for onclick handlers
window.closeDeviceModal = () => MPSM.closeDeviceModal();
