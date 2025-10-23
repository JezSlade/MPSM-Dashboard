/**
 * MPS Monitor Dashboard - Main Application Logic
 */

(function() {
    'use strict';

    // Application state
    const state = {
        currentTab: 'dashboard',
        customerCode: 'W9OPXL0YDK', // Default: Cape Fear Valley Med Ctr
        customerId: '0xUi5WEYLzOCrZ8ILowOvA2',
        customerName: 'CAPE FEAR VALLEY MED CTR.',
        dealerCode: 'NY06AGDWUQ',
        autoRefreshInterval: null,
        devices: [],
        allCustomers: [],
        theme: 'light'
    };

    // Initialize application
    async function init() {
        // Load settings
        const settings = MPSApi.loadSettings();
        state.dealerCode = settings.dealerCode || 'NY06AGDWUQ';
        state.customerCode = settings.customerCode;
        state.theme = localStorage.getItem('mps_theme') || 'light';

        // Apply theme
        document.body.setAttribute('data-theme', state.theme);

        // Setup event listeners
        setupEventListeners();

        // Track visitor
        trackVisitor();

        // Discover customer if not set
        if (!state.customerCode) {
            await discoverDefaultCustomer();
        }

        // Load initial data
        if (state.customerCode) {
            await loadDashboard();
        } else {
            showToast('Please select a customer in the Admin tab', 'warning');
            switchTab('admin');
        }

        // Load admin data
        loadAdminData();
    }

    /**
     * Setup all event listeners
     */
    function setupEventListeners() {
        // Tab navigation
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                const tabName = e.target.dataset.tab;
                switchTab(tabName);
            });
        });

        // Theme toggle
        document.getElementById('theme-toggle').addEventListener('click', toggleTheme);

        // Refresh button
        document.getElementById('refresh-btn').addEventListener('click', refreshDashboard);

        // Admin - Save defaults
        document.getElementById('save-defaults').addEventListener('click', saveDefaults);

        // Admin - Auto refresh toggle
        document.getElementById('auto-refresh').addEventListener('change', (e) => {
            toggleAutoRefresh(e.target.checked);
        });

        // Admin - Refresh interval
        document.getElementById('refresh-interval').addEventListener('change', (e) => {
            const interval = parseInt(e.target.value);
            if (state.autoRefreshInterval) {
                toggleAutoRefresh(false);
                toggleAutoRefresh(true);
            }
        });

        // Admin - Clear cache
        document.getElementById('clear-cache').addEventListener('click', () => {
            MPSApi.clearCache();
            showToast('Cache cleared successfully', 'success');
            refreshDashboard();
        });

        // Admin - Export settings
        document.getElementById('export-settings').addEventListener('click', exportSettings);

        // Admin - Import settings
        document.getElementById('import-settings').addEventListener('click', () => {
            document.getElementById('import-file').click();
        });

        document.getElementById('import-file').addEventListener('change', importSettings);

        // Modal close
        document.querySelector('.modal-close').addEventListener('click', closeModal);
        document.querySelector('.modal-overlay').addEventListener('click', closeModal);

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    }

    /**
     * Switch between tabs
     */
    function switchTab(tabName) {
        // Update state
        state.currentTab = tabName;

        // Update UI
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabName);
        });

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.toggle('active', content.id === `${tabName}-tab`);
        });

        // Load tab-specific data
        if (tabName === 'admin') {
            loadAdminData();
        }
    }

    /**
     * Toggle theme between light and dark
     */
    function toggleTheme() {
        state.theme = state.theme === 'light' ? 'dark' : 'light';
        document.body.setAttribute('data-theme', state.theme);
        localStorage.setItem('mps_theme', state.theme);

        // Update icon
        const icon = document.querySelector('.icon-theme');
        icon.textContent = state.theme === 'light' ? '🌙' : '☀️';
    }

    /**
     * Discover default customer (Cape Fear Medical Center)
     */
    async function discoverDefaultCustomer() {
        try {
            showToast('Discovering default customer...', 'info');

            const result = await MPSApi.discoverCustomerByName('Cape Fear');

            if (result.match) {
                state.customerCode = result.match.code;
                MPSApi.updateSettings({ customerCode: result.match.code });
                showToast(`Found customer: ${result.match.name}`, 'success');
                return result.match.code;
            } else if (result.customers && result.customers.length > 0) {
                // Use first customer as default
                state.customerCode = result.customers[0].code;
                MPSApi.updateSettings({ customerCode: result.customers[0].code });
                showToast(`Using default customer: ${result.customers[0].name}`, 'info');
                return result.customers[0].code;
            } else {
                showToast('No customers found. Please configure in Admin.', 'warning');
                return null;
            }
        } catch (error) {
            console.error('Customer discovery failed:', error);
            showToast('Failed to discover customer: ' + error.message, 'error');
            return null;
        }
    }

    /**
     * Load main dashboard
     */
    async function loadDashboard() {
        try {
            // Load customer dashboard header
            await loadCustomerDashboard();

            // Load all cards in parallel
            await Promise.all([
                loadPrinterList(),
                loadErrorsAlerts(),
                loadTonerLevels(),
                loadMeterReads(),
                loadRecentActivity()
            ]);

        } catch (error) {
            console.error('Dashboard load failed:', error);
            showToast('Failed to load dashboard: ' + error.message, 'error');
        }
    }

    /**
     * Load customer dashboard header
     */
    async function loadCustomerDashboard() {
        const container = document.getElementById('customer-dashboard');
        container.innerHTML = '<div class="loading">Loading customer dashboard...</div>';

        try {
            const data = await MPSApi.getCustomerDashboard(state.customerCode);

            // Extract data from nested structure
            const sdsData = data.SdsDashboard || {};
            const mpsData = data.MpsDashboardCustomer || {};

            // Render customer dashboard
            container.innerHTML = `
                <div class="customer-info">
                    <div class="customer-stat">
                        <div class="customer-stat-label">Customer</div>
                        <div class="customer-stat-value">${state.customerName || 'Unknown'}</div>
                    </div>
                    <div class="customer-stat">
                        <div class="customer-stat-label">Total Devices</div>
                        <div class="customer-stat-value">${sdsData.TotalDevices || 0}</div>
                    </div>
                    <div class="customer-stat">
                        <div class="customer-stat-label">Devices with Errors</div>
                        <div class="customer-stat-value ${sdsData.DevicesWithErrors > 0 ? 'danger' : 'success'}">
                            ${sdsData.DevicesWithErrors || 0}
                        </div>
                    </div>
                    <div class="customer-stat">
                        <div class="customer-stat-label">Devices with Warnings</div>
                        <div class="customer-stat-value ${sdsData.DevicesWithWarnings > 0 ? 'warning' : 'success'}">
                            ${sdsData.DevicesWithWarnings || 0}
                        </div>
                    </div>
                    <div class="customer-stat">
                        <div class="customer-stat-label">Non-Communicating</div>
                        <div class="customer-stat-value ${sdsData.NonCommunicatingDevices > 0 ? 'warning' : 'success'}">
                            ${sdsData.NonCommunicatingDevices || 0}
                        </div>
                    </div>
                    <div class="customer-stat">
                        <div class="customer-stat-label">Actions Pending</div>
                        <div class="customer-stat-value">${sdsData.CommonActionsToComplete || 0}</div>
                    </div>
                </div>
            `;

        } catch (error) {
            console.error('Failed to load customer dashboard:', error);
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">⚠️</div>
                    <p>Failed to load customer dashboard</p>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem;">${error.message}</p>
                </div>
            `;
        }
    }

    /**
     * Load printer list
     */
    async function loadPrinterList() {
        const container = document.getElementById('printer-list');
        const countEl = document.getElementById('printer-count');

        container.innerHTML = '<div class="loading">Loading printers...</div>';

        try {
            const devices = await MPSApi.getDevicesByCustomer(state.customerCode);
            state.devices = devices || [];

            // Filter by customer if needed and sort by external identifier
            const filtered = state.devices.filter(d =>
                !state.customerCode ||
                (d.Customer && d.Customer.Code === state.customerCode)
            );

            const sorted = filtered.sort((a, b) => {
                const idA = a.AssetNumber || a.SerialNumber || '';
                const idB = b.AssetNumber || b.SerialNumber || '';
                return idA.localeCompare(idB);
            });

            countEl.textContent = sorted.length;

            if (sorted.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">🖨️</div>
                        <p>No printers found</p>
                    </div>
                `;
                return;
            }

            // Render printer list
            container.innerHTML = sorted.map(device => {
                const externalId = device.AssetNumber || device.SerialNumber || 'Unknown';
                const model = device.Product?.Model || 'Unknown Model';
                const status = device.LastPingUtc ? 'offline' : 'offline'; // All deleted devices are offline

                return `
                    <div class="printer-item" data-device-id="${device.Id}">
                        <div class="printer-id">${externalId}</div>
                        <div class="printer-model">${model}</div>
                        <span class="printer-status ${status}">${status.toUpperCase()}</span>
                    </div>
                `;
            }).join('');

            // Add click handlers
            container.querySelectorAll('.printer-item').forEach(item => {
                item.addEventListener('click', () => {
                    const deviceId = item.dataset.deviceId;
                    openDeviceModal(deviceId);
                });
            });

        } catch (error) {
            console.error('Failed to load printers:', error);
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">⚠️</div>
                    <p>Failed to load printers</p>
                </div>
            `;
            countEl.textContent = '0';
        }
    }

    /**
     * Load errors and alerts
     */
    async function loadErrorsAlerts() {
        const container = document.getElementById('error-list');
        const countEl = document.getElementById('error-count');

        container.innerHTML = '<div class="loading">Loading errors...</div>';

        try {
            const alerts = await MPSApi.getAlertLimitsDefault(state.customerCode);

            // For now, show empty state
            // In production, you'd parse actual error data
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">✓</div>
                    <p>No active errors</p>
                </div>
            `;
            countEl.textContent = '0';

        } catch (error) {
            console.error('Failed to load errors:', error);
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">⚠️</div>
                    <p>Unable to load errors</p>
                </div>
            `;
            countEl.textContent = '0';
        }
    }

    /**
     * Load toner levels
     */
    async function loadTonerLevels() {
        const container = document.getElementById('toner-list');

        container.innerHTML = '<div class="loading">Loading toner status...</div>';

        try {
            // Use device list to show toner placeholders
            const devices = state.devices.slice(0, 5); // Show top 5

            if (devices.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">🎨</div>
                        <p>No toner data available</p>
                    </div>
                `;
                return;
            }

            // Render mock toner data (in production, fetch actual toner levels)
            container.innerHTML = devices.map(device => {
                const id = device.AssetNumber || device.SerialNumber;
                const percentage = Math.floor(Math.random() * 100); // Mock data
                const level = percentage > 50 ? '' : (percentage > 20 ? 'low' : 'critical');

                return `
                    <div class="toner-item">
                        <div class="toner-label">
                            <span class="toner-device">${id}</span>
                            <span class="toner-percentage">${percentage}%</span>
                        </div>
                        <div class="toner-bar">
                            <div class="toner-fill ${level}" style="width: ${percentage}%"></div>
                        </div>
                    </div>
                `;
            }).join('');

        } catch (error) {
            console.error('Failed to load toner levels:', error);
            container.innerHTML = `
                <div class="empty-state">
                    <p>Unable to load toner data</p>
                </div>
            `;
        }
    }

    /**
     * Load meter reads
     */
    async function loadMeterReads() {
        const container = document.getElementById('meter-list');

        container.innerHTML = '<div class="loading">Loading meter data...</div>';

        // Placeholder - implement actual meter reading logic
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <p>No meter data available</p>
            </div>
        `;
    }

    /**
     * Load recent activity
     */
    async function loadRecentActivity() {
        const container = document.getElementById('activity-list');

        container.innerHTML = '<div class="loading">Loading activity...</div>';

        try {
            const actions = await MPSApi.getDeviceActions(state.customerCode);

            if (!actions || actions.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <p>No recent activity</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = actions.slice(0, 5).map(action => `
                <div class="activity-item">
                    <div>${action.Description || 'Unknown action'}</div>
                    <div class="activity-time">${formatDate(action.CreatedAt)}</div>
                </div>
            `).join('');

        } catch (error) {
            container.innerHTML = `
                <div class="empty-state">
                    <p>No activity data available</p>
                </div>
            `;
        }
    }

    /**
     * Open device detail modal with comprehensive data from all endpoints
     */
    async function openDeviceModal(deviceId) {
        const modal = document.getElementById('device-modal');
        const title = document.getElementById('modal-title');
        const body = document.getElementById('modal-body');

        // Find device in state
        const device = state.devices.find(d => d.Id === deviceId);

        if (!device) {
            showToast('Device not found', 'error');
            return;
        }

        // Show modal
        modal.classList.add('active');
        title.textContent = device.AssetNumber || device.SerialNumber || 'Device Details';
        body.innerHTML = '<div class="loading">Loading comprehensive device details...</div>';

        try {
            // Fetch additional device data from multiple endpoints
            const deviceDataPromises = [
                MPSApi.request('Device/GetDeviceAdditionalInfos', { deviceId: deviceId }).catch(() => null),
                MPSApi.request('Device/GetSuppliesDetails', { deviceId: deviceId }).catch(() => null),
                MPSApi.request('Device/GetSuppliesDetailsInfo', { deviceId: deviceId }).catch(() => null),
                MPSApi.request('Device/GetSuppliesDetailsSummary', { deviceId: deviceId }).catch(() => null),
                MPSApi.request('Counter/Device/List', { deviceId: deviceId }).catch(() => null),
                MPSApi.request('Device/GetDeviceGapInfos', { deviceId: deviceId }).catch(() => null)
            ];

            const [additionalInfo, suppliesDetails, suppliesInfo, suppliesSummary, counters, gapInfo] = await Promise.all(deviceDataPromises);

            // Build comprehensive device view
            let html = `
                <div class="device-detail-section">
                    <h3>Device Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Asset Number</div>
                            <div class="detail-value">${device.AssetNumber || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Serial Number</div>
                            <div class="detail-value">${device.SerialNumber || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">System Name</div>
                            <div class="detail-value">${device.SystemName || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Model</div>
                            <div class="detail-value">${device.Product?.Model || 'Unknown'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Brand</div>
                            <div class="detail-value">${device.Product?.Brand || 'Unknown'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Product ID</div>
                            <div class="detail-value">${device.Product?.Id || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">IP Address</div>
                            <div class="detail-value">${device.IpAddress || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">MAC Address</div>
                            <div class="detail-value">${device.MacAddress || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Firmware</div>
                            <div class="detail-value">${device.Firmware || 'N/A'}</div>
                        </div>
                    </div>
                </div>

                <div class="device-detail-section">
                    <h3>Customer Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Customer</div>
                            <div class="detail-value">${device.Customer?.Description || 'Unknown'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Customer Code</div>
                            <div class="detail-value">${device.Customer?.Code || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Customer ID</div>
                            <div class="detail-value">${device.Customer?.Id || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Country</div>
                            <div class="detail-value">${device.Customer?.CountryName || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">External Identifier</div>
                            <div class="detail-value">${device.Customer?.ExternalIdentifier || 'N/A'}</div>
                        </div>
                    </div>
                </div>

                <div class="device-detail-section">
                    <h3>Status & Activity</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Last Ping</div>
                            <div class="detail-value">${device.LastPingUtc ? formatDate(device.LastPingUtc) : 'Never'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Deleted On</div>
                            <div class="detail-value">${device.DeletedOn ? formatDate(device.DeletedOn) : 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Account</div>
                            <div class="detail-value">${device.Account || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Device ID</div>
                            <div class="detail-value">${device.Id || 'N/A'}</div>
                        </div>
                    </div>
                </div>
            `;

            // Add additional info if available
            if (additionalInfo) {
                html += `
                    <div class="device-detail-section">
                        <h3>Additional Information</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Full Data</div>
                                <div class="detail-value"><pre style="font-size: 0.75rem; max-height: 200px; overflow-y: auto;">${JSON.stringify(additionalInfo, null, 2)}</pre></div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Add supplies info if available
            if (suppliesSummary || suppliesDetails || suppliesInfo) {
                html += `<div class="device-detail-section"><h3>Supply Information</h3>`;

                if (suppliesSummary) {
                    html += `<h4>Supply Summary</h4><pre style="font-size: 0.75rem; max-height: 200px; overflow-y: auto;">${JSON.stringify(suppliesSummary, null, 2)}</pre>`;
                }

                if (suppliesDetails) {
                    html += `<h4>Supply Details</h4><pre style="font-size: 0.75rem; max-height: 200px; overflow-y: auto;">${JSON.stringify(suppliesDetails, null, 2)}</pre>`;
                }

                if (suppliesInfo) {
                    html += `<h4>Supply Info</h4><pre style="font-size: 0.75rem; max-height: 200px; overflow-y: auto;">${JSON.stringify(suppliesInfo, null, 2)}</pre>`;
                }

                html += `</div>`;
            }

            // Add counter info if available
            if (counters) {
                html += `
                    <div class="device-detail-section">
                        <h3>Meter Counters</h3>
                        <pre style="font-size: 0.75rem; max-height: 300px; overflow-y: auto;">${JSON.stringify(counters, null, 2)}</pre>
                    </div>
                `;
            }

            // Add gap info if available
            if (gapInfo) {
                html += `
                    <div class="device-detail-section">
                        <h3>Gap Information</h3>
                        <pre style="font-size: 0.75rem; max-height: 200px; overflow-y: auto;">${JSON.stringify(gapInfo, null, 2)}</pre>
                    </div>
                `;
            }

            body.innerHTML = html;

        } catch (error) {
            console.error('Failed to load device details:', error);
            // Fallback to basic info
            body.innerHTML = `
                <div class="device-detail-section">
                    <h3>Basic Device Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Asset Number</div>
                            <div class="detail-value">${device.AssetNumber || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Serial Number</div>
                            <div class="detail-value">${device.SerialNumber || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Model</div>
                            <div class="detail-value">${device.Product?.Model || 'Unknown'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Brand</div>
                            <div class="detail-value">${device.Product?.Brand || 'Unknown'}</div>
                        </div>
                    </div>
                </div>
                <div class="empty-state" style="margin-top: 2rem;">
                    <p>Additional device details unavailable</p>
                    <p style="font-size: 0.875rem;">${error.message}</p>
                </div>
            `;
        }
    }

    /**
     * Close modal
     */
    function closeModal() {
        document.getElementById('device-modal').classList.remove('active');
    }

    /**
     * Load admin data
     */
    async function loadAdminData() {
        // Load dealer selector
        await loadDealerSelector();

        // Load customer selector
        await loadCustomerSelector();

        // Load traffic metrics
        loadTrafficMetrics();

        // Load engine health
        await loadEngineHealth();

        // Set current values
        document.getElementById('dealer-select').value = state.dealerCode;
        if (state.customerCode) {
            document.getElementById('customer-select').value = state.customerCode;
        }

        // Set auto-refresh checkbox
        const settings = MPSApi.getSettings();
        document.getElementById('auto-refresh').checked = settings.autoRefresh || false;
        document.getElementById('refresh-interval').value = settings.refreshInterval || 60;
    }

    /**
     * Load dealer selector
     */
    async function loadDealerSelector() {
        const select = document.getElementById('dealer-select');

        try {
            // For now, just use the default dealer
            select.innerHTML = `
                <option value="NY06AGDWUQ">NY06AGDWUQ (Default)</option>
            `;

        } catch (error) {
            console.error('Failed to load dealers:', error);
            select.innerHTML = '<option value="">Error loading dealers</option>';
        }
    }

    /**
     * Load customer selector
     */
    async function loadCustomerSelector() {
        const select = document.getElementById('customer-select');
        select.innerHTML = '<option value="">Loading customers...</option>';

        try {
            const customers = await MPSApi.getAllCustomers();
            state.allCustomers = customers;

            if (customers && customers.length > 0) {
                select.innerHTML = customers.map(customer => `
                    <option value="${customer.code}" data-id="${customer.id}" data-name="${customer.name}">
                        ${customer.name} (${customer.deviceCount} devices)
                    </option>
                `).join('');

                // Set current selection
                if (state.customerCode) {
                    select.value = state.customerCode;
                }
            } else {
                select.innerHTML = '<option value="">No customers found</option>';
            }

        } catch (error) {
            console.error('Failed to load customers:', error);
            select.innerHTML = '<option value="">Error loading customers</option>';
        }
    }

    /**
     * Load engine health dashboard
     */
    async function loadEngineHealth() {
        // Check if we're in admin tab
        const adminSection = document.querySelector('#admin-tab .admin-container');
        if (!adminSection) return;

        // Check if engine health section already exists
        let healthSection = document.getElementById('engine-health-section');

        if (!healthSection) {
            healthSection = document.createElement('section');
            healthSection.id = 'engine-health-section';
            healthSection.className = 'admin-section';
            healthSection.innerHTML = '<h3>Engine Health</h3><div id="engine-health-content"></div>';
            adminSection.appendChild(healthSection);
        }

        const content = document.getElementById('engine-health-content');
        content.innerHTML = '<div class="loading">Loading engine status...</div>';

        try {
            const status = await MPSApi.getEngineStatus();

            content.innerHTML = `
                <div class="engine-health">
                    <pre>${JSON.stringify(status, null, 2)}</pre>
                </div>
            `;

        } catch (error) {
            console.error('Failed to load engine health:', error);
            content.innerHTML = `
                <div class="empty-state">
                    <p>Unable to load engine status</p>
                </div>
            `;
        }
    }

    /**
     * Load traffic metrics
     */
    function loadTrafficMetrics() {
        // Simple localStorage-based tracking
        const visits = localStorage.getItem('mps_total_visits') || '0';
        const uniqueId = localStorage.getItem('mps_visitor_id');

        document.getElementById('total-visitors').textContent = visits;
        document.getElementById('unique-visitors').textContent = uniqueId ? '1' : '0';
        document.getElementById('active-sessions').textContent = '1';

        // Load access log
        const log = JSON.parse(localStorage.getItem('mps_access_log') || '[]');
        const logContainer = document.getElementById('access-log-list');

        if (log.length === 0) {
            logContainer.innerHTML = '<div class="empty-state"><p>No access log entries</p></div>';
        } else {
            logContainer.innerHTML = log.slice(-10).reverse().map(entry => `
                <div class="log-entry">
                    <span class="log-ip">${entry.ip || 'Unknown IP'}</span>
                    <span class="log-time">${formatDate(entry.timestamp)}</span>
                </div>
            `).join('');
        }
    }

    /**
     * Track visitor
     */
    function trackVisitor() {
        // Increment total visits
        const visits = parseInt(localStorage.getItem('mps_total_visits') || '0') + 1;
        localStorage.setItem('mps_total_visits', visits.toString());

        // Set unique visitor ID if not exists
        if (!localStorage.getItem('mps_visitor_id')) {
            localStorage.setItem('mps_visitor_id', generateId());
        }

        // Add to access log
        const log = JSON.parse(localStorage.getItem('mps_access_log') || '[]');
        log.push({
            ip: 'Client IP',
            timestamp: new Date().toISOString()
        });

        // Keep only last 100 entries
        if (log.length > 100) {
            log.splice(0, log.length - 100);
        }

        localStorage.setItem('mps_access_log', JSON.stringify(log));
    }

    /**
     * Save defaults
     */
    function saveDefaults() {
        const dealerCode = document.getElementById('dealer-select').value;
        const customerSelect = document.getElementById('customer-select');
        const customerCode = customerSelect.value;

        if (!dealerCode || !customerCode) {
            showToast('Please select both dealer and customer', 'warning');
            return;
        }

        // Get selected customer details
        const selectedOption = customerSelect.options[customerSelect.selectedIndex];
        const customerId = selectedOption.dataset.id;
        const customerName = selectedOption.dataset.name;

        // Update state
        state.dealerCode = dealerCode;
        state.customerCode = customerCode;
        state.customerId = customerId;
        state.customerName = customerName;

        // Save to API settings
        MPSApi.updateSettings({
            dealerCode: dealerCode,
            customerCode: customerCode,
            customerId: customerId,
            customerName: customerName
        });

        showToast(`Defaults saved: ${customerName}`, 'success');

        // Reload dashboard
        refreshDashboard();
    }

    /**
     * Toggle auto-refresh
     */
    function toggleAutoRefresh(enabled) {
        if (enabled) {
            const interval = parseInt(document.getElementById('refresh-interval').value) * 1000;
            state.autoRefreshInterval = setInterval(() => {
                refreshDashboard();
            }, interval);

            MPSApi.updateSettings({ autoRefresh: true });
            showToast('Auto-refresh enabled', 'success');
        } else {
            if (state.autoRefreshInterval) {
                clearInterval(state.autoRefreshInterval);
                state.autoRefreshInterval = null;
            }

            MPSApi.updateSettings({ autoRefresh: false });
            showToast('Auto-refresh disabled', 'info');
        }
    }

    /**
     * Refresh dashboard
     */
    async function refreshDashboard() {
        MPSApi.clearCache();
        await loadDashboard();
        showToast('Dashboard refreshed', 'success');
    }

    /**
     * Export settings
     */
    function exportSettings() {
        const settings = {
            ...MPSApi.getSettings(),
            theme: state.theme,
            exportedAt: new Date().toISOString()
        };

        const blob = new Blob([JSON.stringify(settings, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `mps-settings-${Date.now()}.json`;
        a.click();

        URL.revokeObjectURL(url);
        showToast('Settings exported successfully', 'success');
    }

    /**
     * Import settings
     */
    function importSettings(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (event) => {
            try {
                const settings = JSON.parse(event.target.result);

                MPSApi.updateSettings(settings);

                if (settings.theme) {
                    state.theme = settings.theme;
                    document.body.setAttribute('data-theme', state.theme);
                    localStorage.setItem('mps_theme', state.theme);
                }

                showToast('Settings imported successfully', 'success');

                // Reload admin data and dashboard
                loadAdminData();
                if (settings.customerCode) {
                    state.customerCode = settings.customerCode;
                    refreshDashboard();
                }

            } catch (error) {
                showToast('Failed to import settings: Invalid file', 'error');
            }
        };

        reader.readAsText(file);
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    /**
     * Format date
     */
    function formatDate(dateString) {
        if (!dateString) return '';

        try {
            const date = new Date(dateString);
            return date.toLocaleString();
        } catch (error) {
            return dateString;
        }
    }

    /**
     * Generate unique ID
     */
    function generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    // Initialize on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
