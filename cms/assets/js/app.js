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
        state.customerId = settings.customerId;
        state.customerName = settings.customerName;
        state.theme = localStorage.getItem('mps_theme') || 'light';

        // Apply theme
        document.body.setAttribute('data-theme', state.theme);

        // Initialize Card Manager
        if (typeof CardManager !== 'undefined') {
            await CardManager.init();

            // Set default parameters for card data fetching
            CardManager.setParams({
                dealerId: 'SZ13qRwU5GtFLj0i_CbEgQ2',
                dealerCode: state.dealerCode,
                customerCode: state.customerCode,
                customerId: state.customerId
            });
        }

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

        // Admin sub-tab navigation
        document.querySelectorAll('.admin-subtab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                const subTabName = e.target.dataset.subtab;
                switchAdminSubTab(subTabName);
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

        // Cache Dashboard - Refresh stats
        document.getElementById('refresh-cache-stats').addEventListener('click', loadCacheStats);

        // Cache Dashboard - Warm cache
        document.getElementById('warm-cache').addEventListener('click', async () => {
            showToast('Warming cache...', 'info');
            const btn = document.getElementById('warm-cache');
            btn.disabled = true;
            btn.textContent = '🔥 Warming...';

            const result = await MPSApi.warmCache();

            btn.disabled = false;
            btn.textContent = '🔥 Warm Cache';

            if (result.success) {
                showToast(`Cache warmed in ${result.duration}ms`, 'success');
                loadCacheStats();
            } else {
                showToast('Cache warming failed: ' + result.error, 'error');
            }
        });

        // Cache Dashboard - Clear all cache
        document.getElementById('clear-all-cache').addEventListener('click', () => {
            if (confirm('Clear all cached data? This will reload the dashboard.')) {
                MPSApi.clearCache();
                showToast('All cache cleared', 'success');
                loadCacheStats();
                setTimeout(() => refreshDashboard(), 500);
            }
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
            // Admin tab now has sub-tabs, load first sub-tab by default
            switchAdminSubTab('settings');
        }
    }

    /**
     * Switch between admin sub-tabs
     */
    function switchAdminSubTab(subTabName) {
        // Update UI
        document.querySelectorAll('.admin-subtab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.subtab === subTabName);
        });

        document.querySelectorAll('.admin-subtab-content').forEach(content => {
            content.classList.toggle('active', content.id === `${subTabName}-subtab`);
        });

        // Load sub-tab-specific data
        if (subTabName === 'settings') {
            loadAdminData();
        } else if (subTabName === 'cards') {
            // Render card management UI
            if (typeof CardManager !== 'undefined') {
                CardManager.renderAdminPanel('.card-management-container');
            }
        } else if (subTabName === 'cache') {
            loadCacheStats();
        } else if (subTabName === 'traffic') {
            loadTrafficMetrics();
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
        if (state.theme === 'light') {
            icon.className = 'icon-theme fas fa-moon';
        } else {
            icon.className = 'icon-theme fas fa-sun';
        }
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
            // Always load customer dashboard header
            await loadCustomerDashboard();

            // Use CardManager if available
            if (typeof CardManager !== 'undefined') {
                // First, load device list to get deviceId for cards that need it
                console.log('[loadDashboard] Loading devices first for CardManager params');
                const devices = await MPSApi.getDevicesByCustomer(state.customerCode, state.customerId);
                state.devices = devices || [];
                console.log('[loadDashboard] Loaded devices:', state.devices.length);

                // Update CardManager params with deviceId if we have devices
                if (state.devices.length > 0) {
                    const firstDevice = state.devices[0];
                    CardManager.setParams({
                        dealerId: 'SZ13qRwU5GtFLj0i_CbEgQ2',
                        dealerCode: state.dealerCode,
                        customerCode: state.customerCode,
                        customerId: state.customerId,
                        deviceId: firstDevice.Id,
                        idInstalledProduct: firstDevice.IdInstalledProduct
                    });
                    console.log('[loadDashboard] Set deviceId:', firstDevice.Id);
                }

                await CardManager.renderDashboard('.dashboard-grid');
                return;
            }

            // Fallback to original implementation
            // FIRST: Load printer list to populate state.devices
            await loadPrinterList();

            // THEN: Load all other cards in parallel (they depend on state.devices)
            await Promise.all([
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
            console.log('[loadPrinterList] Starting. Customer:', state.customerCode);
            const devices = await MPSApi.getDevicesByCustomer(state.customerCode, state.customerId);
            state.devices = devices || [];
            console.log('[loadPrinterList] Loaded devices:', state.devices.length);

            countEl.textContent = state.devices.length;

            if (state.devices.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">🖨️</div>
                        <p>No printers found</p>
                    </div>
                `;
                return;
            }

            // Prepare data for TableUtils
            const tableData = state.devices.map(device => ({
                id: device.Id,
                identifier: device.ExternalIdentifier || device.AssetNumber || device.SerialNumber || 'Unknown',
                model: device.Product?.Model || 'Unknown',
                ip: device.IpAddress || '-',
                location: device.Note || device.OfficeDescription || '-',
                monoCounter: device.CounterMono || 0,
                colorCounter: device.CounterColor || 0,
                isOffline: device.IsOffline
            }));

            // Define table columns
            const columns = [
                {key: 'identifier', label: 'Identifier'},
                {key: 'model', label: 'Model'},
                {key: 'ip', label: 'IP Address'},
                {key: 'location', label: 'Location'},
                {key: 'monoCounter', label: 'Mono', render: TableUtils.renderCounter},
                {key: 'colorCounter', label: 'Color', render: TableUtils.renderCounter},
                {key: 'isOffline', label: 'Status', render: (value) => TableUtils.renderStatus(null, value)}
            ];

            // Pagination state
            let pageSize = 50;
            let currentPage = 1;

            // Function to render table with current page
            function renderTable() {
                const table = TableUtils.createPaginatedTable(tableData, columns, {
                    pageSize: pageSize,
                    currentPage: currentPage,
                    className: 'printer-table'
                });

                container.innerHTML = table.html;

                // Setup table with callbacks
                table.setup(container, {
                    onRowClick: (device) => openDeviceModal(device.id),
                    onPageSizeChange: (newSize) => {
                        pageSize = parseInt(newSize);
                        currentPage = 1;
                        renderTable();
                    },
                    onPageChange: (direction) => {
                        const totalPages = Math.ceil(tableData.length / pageSize);
                        if (direction === 'prev' && currentPage > 1) {
                            currentPage--;
                        } else if (direction === 'next' && currentPage < totalPages) {
                            currentPage++;
                        }
                        renderTable();
                    }
                });
            }

            // Initial render
            renderTable();

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

        container.innerHTML = '<div class="loading">Loading alerts...</div>';

        try {
            console.log('[loadErrorsAlerts] Starting. state.devices count:', state.devices?.length);
            const devices = state.devices;

            // Extract devices with issues
            const alerts = devices.filter(d =>
                d.IsOffline ||
                (d.BlackToner !== null && d.BlackToner < 20) ||
                (d.CyanToner !== null && d.CyanToner < 20) ||
                (d.MagentaToner !== null && d.MagentaToner < 20) ||
                (d.YellowToner !== null && d.YellowToner < 20)
            ).map(d => {
                const issues = [];
                if (d.IsOffline) issues.push('Offline');
                if (d.BlackToner !== null && d.BlackToner < 20) issues.push('Low Black Toner');
                if (d.CyanToner !== null && d.CyanToner < 20) issues.push('Low Cyan Toner');
                if (d.MagentaToner !== null && d.MagentaToner < 20) issues.push('Low Magenta Toner');
                if (d.YellowToner !== null && d.YellowToner < 20) issues.push('Low Yellow Toner');

                return {
                    id: d.Id,
                    identifier: d.ExternalIdentifier || d.AssetNumber || 'Unknown',
                    issues: issues.join(', '),
                    severity: d.IsOffline ? 'Critical' : 'Warning',
                    lastUpdate: d.LastUpdate
                };
            });

            countEl.textContent = alerts.length;

            const critical = alerts.filter(a => a.severity === 'Critical').length;
            const warnings = alerts.filter(a => a.severity === 'Warning').length;

            const snapshotHtml = `
                <div class="snapshot-grid">
                    <div class="snapshot-item">
                        <div class="snapshot-value">${alerts.length}</div>
                        <div class="snapshot-label">Total Alerts</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-value" style="color: #dc3545;">${critical}</div>
                        <div class="snapshot-label">Critical</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-value" style="color: #ffc107;">${warnings}</div>
                        <div class="snapshot-label">Warnings</div>
                    </div>
                </div>
            `;

            const columns = [
                {key: 'identifier', label: 'Device'},
                {key: 'issues', label: 'Issues'},
                {key: 'severity', label: 'Severity', render: (v) => `<span class="status-badge ${v.toLowerCase()}">${v}</span>`},
                {key: 'lastUpdate', label: 'Last Update', render: TableUtils.renderDate}
            ];

            // Pagination state
            let pageSize = 25;
            let currentPage = 1;

            function renderTable() {
                const table = TableUtils.createPaginatedTable(alerts, columns, {
                    pageSize: pageSize,
                    currentPage: currentPage
                });

                container.innerHTML = TableUtils.createExpandableCard(
                    'Errors & Alerts',
                    snapshotHtml,
                    table.html
                );

                TableUtils.setupExpandable(container);

                const detailsSection = container.querySelector('.card-details');
                if (detailsSection) {
                    table.setup(detailsSection, {
                        onRowClick: (alert) => openDeviceModal(alert.id),
                        onPageSizeChange: (newSize) => {
                            pageSize = parseInt(newSize);
                            currentPage = 1;
                            renderTable();
                        },
                        onPageChange: (direction) => {
                            const totalPages = Math.ceil(alerts.length / pageSize);
                            if (direction === 'prev' && currentPage > 1) {
                                currentPage--;
                            } else if (direction === 'next' && currentPage < totalPages) {
                                currentPage++;
                            }
                            renderTable();
                        }
                    });
                }
            }

            renderTable();

        } catch (error) {
            console.error('Failed to load alerts:', error);
            container.innerHTML = '<div class="empty-state">Failed to load alerts</div>';
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
            console.log('[loadTonerLevels] Starting. state.devices count:', state.devices?.length);
            const devices = state.devices;

            // Extract devices with toner data
            const tonerData = devices.filter(d =>
                d.BlackToner !== null || d.CyanToner !== null ||
                d.MagentaToner !== null || d.YellowToner !== null
            ).map(d => ({
                id: d.Id,
                identifier: d.ExternalIdentifier || d.AssetNumber || 'Unknown',
                black: d.BlackToner,
                cyan: d.CyanToner,
                magenta: d.MagentaToner,
                yellow: d.YellowToner,
                lowCount: [d.BlackToner, d.CyanToner, d.MagentaToner, d.YellowToner]
                    .filter(t => t !== null && t < 20).length
            }));

            // Summary stats
            const lowToner = tonerData.filter(d => d.lowCount > 0).length;
            const critical = tonerData.filter(d =>
                [d.black, d.cyan, d.magenta, d.yellow].some(t => t !== null && t < 10)
            ).length;

            // Snapshot HTML
            const snapshotHtml = `
                <div class="snapshot-grid">
                    <div class="snapshot-item">
                        <div class="snapshot-value">${tonerData.length}</div>
                        <div class="snapshot-label">Total Devices</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-value" style="color: #ffc107;">${lowToner}</div>
                        <div class="snapshot-label">Low Toner (&lt;20%)</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-value" style="color: #dc3545;">${critical}</div>
                        <div class="snapshot-label">Critical (&lt;10%)</div>
                    </div>
                </div>
            `;

            // Table columns
            const columns = [
                {key: 'identifier', label: 'Device'},
                {key: 'black', label: 'Black', render: TableUtils.renderTonerLevel},
                {key: 'cyan', label: 'Cyan', render: TableUtils.renderTonerLevel},
                {key: 'magenta', label: 'Magenta', render: TableUtils.renderTonerLevel},
                {key: 'yellow', label: 'Yellow', render: TableUtils.renderTonerLevel}
            ];

            // Pagination state
            let pageSize = 25;
            let currentPage = 1;

            function renderTable() {
                const table = TableUtils.createPaginatedTable(tonerData, columns, {
                    pageSize: pageSize,
                    currentPage: currentPage,
                    className: 'toner-table'
                });

                container.innerHTML = TableUtils.createExpandableCard(
                    'Toner Levels',
                    snapshotHtml,
                    table.html
                );

                TableUtils.setupExpandable(container);

                const detailsSection = container.querySelector('.card-details');
                if (detailsSection) {
                    table.setup(detailsSection, {
                        onRowClick: (device) => openDeviceModal(device.id),
                        onPageSizeChange: (newSize) => {
                            pageSize = parseInt(newSize);
                            currentPage = 1;
                            renderTable();
                        },
                        onPageChange: (direction) => {
                            const totalPages = Math.ceil(tonerData.length / pageSize);
                            if (direction === 'prev' && currentPage > 1) {
                                currentPage--;
                            } else if (direction === 'next' && currentPage < totalPages) {
                                currentPage++;
                            }
                            renderTable();
                        }
                    });
                }
            }

            renderTable();

        } catch (error) {
            console.error('Failed to load toner levels:', error);
            container.innerHTML = '<div class="empty-state">Failed to load toner data</div>';
        }
    }

    /**
     * Load meter reads
     */
    async function loadMeterReads() {
        const container = document.getElementById('meter-list');
        container.innerHTML = '<div class="loading">Loading meter data...</div>';

        try {
            console.log('[loadMeterReads] Starting. state.devices count:', state.devices?.length);
            const devices = state.devices;

            // Extract counter data
            const meterData = devices.map(d => ({
                id: d.Id,
                identifier: d.ExternalIdentifier || d.AssetNumber || 'Unknown',
                monoCounter: d.CounterMono || 0,
                colorCounter: d.CounterColor || 0,
                monoMonthly: d.MonthlyMonoVolume || 0,
                colorMonthly: d.MonthlyColorVolume || 0,
                totalMonthly: (d.MonthlyMonoVolume || 0) + (d.MonthlyColorVolume || 0)
            })).sort((a, b) => b.totalMonthly - a.totalMonthly);

            // Summary stats
            const totalMono = meterData.reduce((sum, d) => sum + d.monoMonthly, 0);
            const totalColor = meterData.reduce((sum, d) => sum + d.colorMonthly, 0);
            const top3 = meterData.slice(0, 3);

            const snapshotHtml = `
                <div class="snapshot-grid">
                    <div class="snapshot-item">
                        <div class="snapshot-value">${totalMono.toLocaleString()}</div>
                        <div class="snapshot-label">Mono Pages (Month)</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-value">${totalColor.toLocaleString()}</div>
                        <div class="snapshot-label">Color Pages (Month)</div>
                    </div>
                    <div class="snapshot-item">
                        <div class="snapshot-value">${(totalMono + totalColor).toLocaleString()}</div>
                        <div class="snapshot-label">Total Pages</div>
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <strong>Top 3 Devices:</strong>
                    <ul style="margin: 0.5rem 0; padding-left: 1.5rem;">
                        ${top3.map(d => `
                            <li>${d.identifier}: ${d.totalMonthly.toLocaleString()} pages/month</li>
                        `).join('')}
                    </ul>
                </div>
            `;

            const columns = [
                {key: 'identifier', label: 'Device'},
                {key: 'monoCounter', label: 'Mono Total', render: TableUtils.renderCounter},
                {key: 'colorCounter', label: 'Color Total', render: TableUtils.renderCounter},
                {key: 'monoMonthly', label: 'Mono/Month', render: TableUtils.renderCounter},
                {key: 'colorMonthly', label: 'Color/Month', render: TableUtils.renderCounter}
            ];

            // Pagination state
            let pageSize = 25;
            let currentPage = 1;

            function renderTable() {
                const table = TableUtils.createPaginatedTable(meterData, columns, {
                    pageSize: pageSize,
                    currentPage: currentPage
                });

                container.innerHTML = TableUtils.createExpandableCard(
                    'Meter Reads',
                    snapshotHtml,
                    table.html
                );

                TableUtils.setupExpandable(container);

                const detailsSection = container.querySelector('.card-details');
                if (detailsSection) {
                    table.setup(detailsSection, {
                        onRowClick: (device) => openDeviceModal(device.id),
                        onPageSizeChange: (newSize) => {
                            pageSize = parseInt(newSize);
                            currentPage = 1;
                            renderTable();
                        },
                        onPageChange: (direction) => {
                            const totalPages = Math.ceil(meterData.length / pageSize);
                            if (direction === 'prev' && currentPage > 1) {
                                currentPage--;
                            } else if (direction === 'next' && currentPage < totalPages) {
                                currentPage++;
                            }
                            renderTable();
                        }
                    });
                }
            }

            renderTable();

        } catch (error) {
            console.error('Failed to load meters:', error);
            container.innerHTML = '<div class="empty-state">Failed to load meter data</div>';
        }
    }

    /**
     * Load recent activity
     */
    async function loadRecentActivity() {
        const container = document.getElementById('activity-list');
        container.innerHTML = '<div class="loading">Loading activity...</div>';

        try {
            console.log('[loadRecentActivity] Starting. state.devices count:', state.devices?.length);
            // Get recent device updates
            const devices = state.devices;
            const recentUpdates = devices
                .filter(d => d.LastUpdate)
                .map(d => ({
                    id: d.Id,
                    identifier: d.ExternalIdentifier || d.AssetNumber || 'Unknown',
                    action: 'Counter Updated',
                    timestamp: new Date(d.LastUpdate),
                    details: `Mono: ${(d.CounterMono || 0).toLocaleString()}, Color: ${(d.CounterColor || 0).toLocaleString()}`
                }))
                .sort((a, b) => b.timestamp - a.timestamp)
                .slice(0, 50);

            const snapshotHtml = `
                <div style="max-height: 200px; overflow-y: auto;">
                    <strong>Last 5 Activities:</strong>
                    <ul style="margin: 0.5rem 0; padding-left: 1.5rem; list-style: none;">
                        ${recentUpdates.slice(0, 5).map(a => `
                            <li style="margin: 0.5rem 0; padding: 0.5rem; background: var(--bg-secondary); border-radius: 4px;">
                                <strong>${a.identifier}</strong> - ${a.action}
                                <br><small style="color: var(--text-secondary);">${a.timestamp.toLocaleString()}</small>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;

            const columns = [
                {key: 'identifier', label: 'Device'},
                {key: 'action', label: 'Action'},
                {key: 'details', label: 'Details'},
                {key: 'timestamp', label: 'Time', render: (v) => v.toLocaleString()}
            ];

            // Pagination state
            let pageSize = 25;
            let currentPage = 1;

            function renderTable() {
                const table = TableUtils.createPaginatedTable(recentUpdates, columns, {
                    pageSize: pageSize,
                    currentPage: currentPage
                });

                container.innerHTML = TableUtils.createExpandableCard(
                    'Recent Activity',
                    snapshotHtml,
                    table.html
                );

                TableUtils.setupExpandable(container);

                const detailsSection = container.querySelector('.card-details');
                if (detailsSection) {
                    table.setup(detailsSection, {
                        onRowClick: (activity) => openDeviceModal(activity.id),
                        onPageSizeChange: (newSize) => {
                            pageSize = parseInt(newSize);
                            currentPage = 1;
                            renderTable();
                        },
                        onPageChange: (direction) => {
                            const totalPages = Math.ceil(recentUpdates.length / pageSize);
                            if (direction === 'prev' && currentPage > 1) {
                                currentPage--;
                            } else if (direction === 'next' && currentPage < totalPages) {
                                currentPage++;
                            }
                            renderTable();
                        }
                    });
                }
            }

            renderTable();

        } catch (error) {
            console.error('Failed to load activity:', error);
            container.innerHTML = '<div class="empty-state">Failed to load activity</div>';
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

        // Load cache statistics
        loadCacheStats();

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
     * Load cache statistics
     */
    function loadCacheStats() {
        const metadata = MPSApi.getCacheMetadata();

        // Update summary stats
        document.getElementById('cache-hit-rate').textContent = `${metadata.stats.hitRate}%`;
        document.getElementById('cache-total-entries').textContent = metadata.totalEntries;
        document.getElementById('cache-total-size').textContent = `${metadata.totalSizeMB} MB`;
        document.getElementById('cache-hit-miss').textContent = `${metadata.stats.hits} / ${metadata.stats.misses}`;

        // Build cache entries table
        const container = document.getElementById('cache-entries-list');

        if (metadata.entries.length === 0) {
            container.innerHTML = '<p style="text-align:center;color:#999;">No cache entries found</p>';
            return;
        }

        const html = `
            <table class="cache-entry-table">
                <thead>
                    <tr>
                        <th>Cache Key</th>
                        <th>Size</th>
                        <th>Age</th>
                        <th>TTL Remaining</th>
                        <th>Status</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    ${metadata.entries.map(entry => {
                        const ageMinutes = Math.floor(entry.age / 60000);
                        const remainingMinutes = Math.floor(entry.remaining / 60000);
                        const ttlMinutes = Math.floor(entry.ttl / 60000);
                        const percentUsed = parseFloat(entry.percentUsed);

                        let status = 'cache-status-fresh';
                        let statusText = 'Fresh';

                        if (entry.expired) {
                            status = 'cache-status-expired';
                            statusText = 'Expired';
                        } else if (percentUsed > 80) {
                            status = 'cache-status-aging';
                            statusText = 'Aging';
                        }

                        return `
                            <tr>
                                <td><code>${entry.key}</code></td>
                                <td>${entry.sizeKB} KB</td>
                                <td>${ageMinutes}m ago</td>
                                <td>${remainingMinutes}m / ${ttlMinutes}m</td>
                                <td class="${status}">${statusText}</td>
                                <td>
                                    <div class="cache-ttl-bar">
                                        <div class="cache-ttl-fill" style="width: ${percentUsed}%"></div>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;

        container.innerHTML = html;
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
