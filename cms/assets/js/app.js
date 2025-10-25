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
        // FORCE modal to be closed on init (fix for stuck modal issue)
        // This runs multiple times to catch any timing issues
        const forceCloseModal = () => {
            const modal = document.getElementById('device-modal');
            if (modal && modal.classList.contains('active')) {
                modal.classList.remove('active');
                console.log('[init] FORCED modal closed');
            }
        };

        // Close immediately
        forceCloseModal();

        // Close again after 100ms to catch late triggers
        setTimeout(forceCloseModal, 100);

        // Close again after 500ms for slower loads
        setTimeout(forceCloseModal, 500);

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
        document.getElementById('clear-all-cache').addEventListener('click', async () => {
            if (confirm('Clear all cached data? This will reload the dashboard.')) {
                try {
                    const response = await fetch('api/cache-manager.php', {
                        method: 'DELETE'
                    });
                    const data = await response.json();

                    if (data.success) {
                        showToast('All cache cleared successfully', 'success');
                        loadCacheStats();
                        setTimeout(() => refreshDashboard(), 500);
                    } else {
                        showToast('Failed to clear cache: ' + data.error, 'error');
                    }
                } catch (error) {
                    showToast('Failed to clear cache: ' + error.message, 'error');
                }
            }
        });

        // Admin - Export settings
        document.getElementById('export-settings').addEventListener('click', exportSettings);

        // Admin - Import settings
        document.getElementById('import-settings').addEventListener('click', () => {
            document.getElementById('import-file').click();
        });

        document.getElementById('import-file').addEventListener('change', importSettings);

        // Modal close - multiple methods for reliability
        const modalCloseBtn = document.querySelector('.modal-close');
        const modalOverlay = document.querySelector('.modal-overlay');

        if (modalCloseBtn) {
            modalCloseBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                closeModal();
            });
        }

        if (modalOverlay) {
            modalOverlay.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                closeModal();
            });
        }

        // Keyboard shortcuts - ESC key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
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
        } else if (subTabName === 'users') {
            loadUsers();
        } else if (subTabName === 'engine') {
            loadEngineStatus();
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
     * Handle micro-card clicks to drill down into specific metrics
     */
    function handleMicroCardClick(metric, dashboardData) {
        console.log('[handleMicroCardClick] Metric:', metric, 'Data:', dashboardData);

        // For now, show a toast with the metric details
        // TODO: Implement drill-down modals for each metric type
        switch (metric) {
            case 'total-devices':
                showToast('Loading all devices...', 'info');
                // Could open a filtered view of the printer card
                break;
            case 'errors':
                showToast(`Showing ${dashboardData.DevicesWithErrors} devices with errors`, 'info');
                // Could filter devices by error status
                break;
            case 'warnings':
                showToast(`Showing ${dashboardData.DevicesWithWarnings} devices with warnings`, 'info');
                // Could filter devices by warning status
                break;
            case 'offline':
                showToast(`Showing ${dashboardData.NonCommunicatingDevices} offline devices`, 'info');
                // Could filter devices by communication status
                break;
            case 'actions':
                showToast(`Showing ${dashboardData.CommonActionsToComplete} pending actions`, 'info');
                // Could show action list
                break;
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

                // Update CardManager params with all required parameters
                const dealerId = 'SZ13qRwU5GtFLj0i_CbEgQ2'; // Hardcoded dealer ID

                const params = {
                    dealerId: dealerId,
                    FilterDealerId: dealerId, // For printer list card
                    dealerCode: state.dealerCode,
                    customerCode: state.customerCode,
                    customerId: state.customerId
                };

                if (state.devices.length > 0) {
                    const firstDevice = state.devices[0];
                    params.deviceId = firstDevice.Id;
                    params.idInstalledProduct = firstDevice.IdInstalledProduct;
                    console.log('[loadDashboard] Set deviceId:', firstDevice.Id);
                }

                // Set date range for meter readings (last 30 days)
                const toDate = new Date();
                const fromDate = new Date();
                fromDate.setDate(fromDate.getDate() - 30);

                params.fromDate = fromDate.toISOString().split('T')[0];
                params.toDate = toDate.toISOString().split('T')[0];

                CardManager.setParams(params);
                console.log('[loadDashboard] Set all params:', params);

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

            // Render customer dashboard with prominent customer name banner
            container.innerHTML = `
                <div class="customer-banner">
                    <div class="customer-banner-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="customer-banner-content">
                        <div class="customer-banner-label">Customer</div>
                        <h2 class="customer-banner-name">${state.customerName || 'Unknown Customer'}</h2>
                    </div>
                </div>

                <div class="micro-cards-grid">
                    <div class="micro-card clickable" data-metric="total-devices" title="Click to view all devices">
                        <div class="micro-card-icon">
                            <i class="fas fa-print"></i>
                        </div>
                        <div class="micro-card-content">
                            <div class="micro-card-label">Total Devices</div>
                            <div class="micro-card-value">${sdsData.TotalDevices || 0}</div>
                        </div>
                        <div class="micro-card-action">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>

                    <div class="micro-card clickable ${sdsData.DevicesWithErrors > 0 ? 'status-danger' : 'status-success'}"
                         data-metric="errors"
                         title="Click to view devices with errors">
                        <div class="micro-card-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="micro-card-content">
                            <div class="micro-card-label">Errors</div>
                            <div class="micro-card-value">${sdsData.DevicesWithErrors || 0}</div>
                        </div>
                        <div class="micro-card-action">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>

                    <div class="micro-card clickable ${sdsData.DevicesWithWarnings > 0 ? 'status-warning' : 'status-success'}"
                         data-metric="warnings"
                         title="Click to view devices with warnings">
                        <div class="micro-card-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="micro-card-content">
                            <div class="micro-card-label">Warnings</div>
                            <div class="micro-card-value">${sdsData.DevicesWithWarnings || 0}</div>
                        </div>
                        <div class="micro-card-action">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>

                    <div class="micro-card clickable ${sdsData.NonCommunicatingDevices > 0 ? 'status-warning' : 'status-success'}"
                         data-metric="offline"
                         title="Click to view non-communicating devices">
                        <div class="micro-card-icon">
                            <i class="fas fa-wifi-slash"></i>
                        </div>
                        <div class="micro-card-content">
                            <div class="micro-card-label">Offline</div>
                            <div class="micro-card-value">${sdsData.NonCommunicatingDevices || 0}</div>
                        </div>
                        <div class="micro-card-action">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>

                    <div class="micro-card clickable" data-metric="actions" title="Click to view pending actions">
                        <div class="micro-card-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="micro-card-content">
                            <div class="micro-card-label">Actions</div>
                            <div class="micro-card-value">${sdsData.CommonActionsToComplete || 0}</div>
                        </div>
                        <div class="micro-card-action">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                </div>
            `;

            // Add click handlers for interactive cards
            container.querySelectorAll('.micro-card.clickable').forEach(card => {
                card.addEventListener('click', () => {
                    const metric = card.dataset.metric;
                    handleMicroCardClick(metric, sdsData);
                });
            });

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
        console.log('[openDeviceModal] Opening modal for deviceId:', deviceId);

        const modal = document.getElementById('device-modal');
        const title = document.getElementById('modal-title');
        const body = document.getElementById('modal-body');

        // Validate inputs
        if (!modal || !title || !body) {
            console.error('[openDeviceModal] Modal elements not found');
            return;
        }

        // Find device in state
        const device = state.devices.find(d => d.Id === deviceId);

        if (!device) {
            console.error('[openDeviceModal] Device not found:', deviceId);
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
        console.log('[closeModal] Closing modal');
        const modal = document.getElementById('device-modal');
        if (modal) {
            modal.classList.remove('active');
        }
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
     * Load traffic metrics from database
     */
    async function loadTrafficMetrics() {
        try {
            // Fetch stats from database
            const response = await fetch('api/visitor-tracking.php?action=stats');
            const data = await response.json();

            if (data.success) {
                const stats = data.stats;
                document.getElementById('total-visitors').textContent = stats.total_visits || 0;
                document.getElementById('unique-visitors').textContent = stats.unique_visitors || 0;
                document.getElementById('active-sessions').textContent = stats.active_sessions || 0;
            }

            // Fetch recent access log
            const logResponse = await fetch('api/visitor-tracking.php?action=recent&limit=20');
            const logData = await logResponse.json();
            const logContainer = document.getElementById('access-log-list');

            if (logData.success && logData.log && logData.log.length > 0) {
                logContainer.innerHTML = logData.log.map(entry => `
                    <div class="log-entry">
                        <span class="log-ip">${entry.ip_address || 'Unknown'}</span>
                        <span class="log-user">${entry.username || 'anonymous'}</span>
                        <span class="log-page">${entry.page_url || '/'}</span>
                        <span class="log-time">${formatDate(entry.visited_at)}</span>
                    </div>
                `).join('');
            } else {
                logContainer.innerHTML = '<div class="empty-state"><p>No access log entries</p></div>';
            }

        } catch (error) {
            console.error('Failed to load traffic metrics:', error);
            document.getElementById('total-visitors').textContent = '0';
            document.getElementById('unique-visitors').textContent = '0';
            document.getElementById('active-sessions').textContent = '0';
        }
    }

    /**
     * Track visitor - log to database
     */
    async function trackVisitor() {
        try {
            const response = await fetch('api/visitor-tracking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    page_url: window.location.pathname
                })
            });

            const data = await response.json();
            if (data.success) {
                console.log('[trackVisitor] Visit logged to database:', data.id);
            }
        } catch (error) {
            console.warn('[trackVisitor] Failed to log visit:', error);
        }
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
     * Load and display MPS API engine status
     */
    async function loadEngineStatus() {
        const container = document.getElementById('engine-status-container');
        container.innerHTML = '<div class="loading">Loading engine status...</div>';

        try {
            // Fetch comprehensive system health from our new endpoint
            const healthResponse = await fetch('api/system-health.php');
            const health = await healthResponse.json();
            console.log('[loadEngineStatus] System health:', health);

            // Also get MPS API engine status
            const status = await MPSApi.getEngineStatus();
            console.log('[loadEngineStatus] MPS API status:', status);

            // Extract engine health data (from system-health.php)
            const uptime = health.uptime || 0;
            const version = health.version || '1.0.0';
            const environment = health.environment || 'production';
            const database = health.database || {};
            const cache = health.cache || {};
            const auth = health.auth || {};

            // MPS API status
            const mpsStatus = status.status || 'unknown';
            const mpsVersion = status.version || 'Unknown';
            const actionCount = status.action_count || 0;

            // Calculate uptime display
            const uptimeHours = Math.floor(uptime / 3600);
            const uptimeDays = Math.floor(uptimeHours / 24);
            const remainingHours = uptimeHours % 24;
            const uptimeDisplay = uptimeDays > 0
                ? `${uptimeDays}d ${remainingHours}h`
                : `${uptimeHours}h`;

            // Render engine status dashboard
            container.innerHTML = `
                <div class="engine-dashboard">
                    <!-- System Overview -->
                    <div class="engine-section">
                        <h3><i class="fas fa-heartbeat"></i> System Health</h3>
                        <div class="engine-stats-grid">
                            <div class="engine-stat-card ${database.connected ? 'status-success' : 'status-danger'}">
                                <div class="stat-icon"><i class="fas fa-${database.connected ? 'check-circle' : 'times-circle'}"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Database</div>
                                    <div class="stat-value">${database.connected ? 'Connected' : 'Disconnected'}</div>
                                </div>
                            </div>
                            <div class="engine-stat-card ${cache.enabled ? 'status-success' : 'status-warning'}">
                                <div class="stat-icon"><i class="fas fa-${cache.enabled ? 'check-circle' : 'exclamation-triangle'}"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Cache</div>
                                    <div class="stat-value">${cache.enabled ? 'Enabled' : 'Disabled'}</div>
                                </div>
                            </div>
                            <div class="engine-stat-card ${mpsStatus === 'online' ? 'status-success' : 'status-warning'}">
                                <div class="stat-icon"><i class="fas fa-${mpsStatus === 'online' ? 'check-circle' : 'exclamation-circle'}"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">MPS API</div>
                                    <div class="stat-value">${mpsStatus}</div>
                                </div>
                            </div>
                            <div class="engine-stat-card ${auth.sessionActive ? 'status-success' : 'status-danger'}">
                                <div class="stat-icon"><i class="fas fa-${auth.sessionActive ? 'check-circle' : 'times-circle'}"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">Authentication</div>
                                    <div class="stat-value">${auth.sessionActive ? 'Active' : 'Inactive'}</div>
                                </div>
                            </div>
                        </div>
                        <div class="test-buttons" style="margin-top: 1rem;">
                            <button class="btn btn-secondary" onclick="window.testDatabaseConnection()">
                                <i class="fas fa-database"></i> Test Database
                            </button>
                            <button class="btn btn-secondary" onclick="window.testCacheConnection()">
                                <i class="fas fa-memory"></i> Test Cache
                            </button>
                            <button class="btn btn-secondary" onclick="window.testMPSAPI()">
                                <i class="fas fa-plug"></i> Test MPS API
                            </button>
                            <button class="btn btn-primary" onclick="loadEngineStatus()">
                                <i class="fas fa-sync"></i> Refresh Status
                            </button>
                        </div>
                    </div>

                    <!-- Database Status -->
                    <div class="engine-section">
                        <h3><i class="fas fa-database"></i> Database Details</h3>
                        <div class="engine-detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Connection Status:</span>
                                <span class="detail-value ${database.connected ? 'text-success' : 'text-danger'}">
                                    <i class="fas fa-${database.connected ? 'check' : 'times'}"></i>
                                    ${database.connected ? 'Connected' : 'Disconnected'}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Database Type:</span>
                                <span class="detail-value">${database.type || 'N/A'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Host:</span>
                                <span class="detail-value">${database.host || 'N/A'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Database Name:</span>
                                <span class="detail-value">${database.name || 'N/A'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Tables:</span>
                                <span class="detail-value">${database.table_count || 'N/A'}</span>
                            </div>
                            ${database.error ? `
                            <div class="detail-item" style="grid-column: 1/-1;">
                                <span class="detail-label">Error:</span>
                                <span class="detail-value text-danger">${database.error}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Cache Status -->
                    <div class="engine-section">
                        <h3><i class="fas fa-memory"></i> Cache Details</h3>
                        <div class="engine-detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Cache Status:</span>
                                <span class="detail-value ${cache.enabled ? 'text-success' : 'text-warning'}">
                                    <i class="fas fa-${cache.enabled ? 'check' : 'exclamation-triangle'}"></i>
                                    ${cache.enabled ? 'Enabled' : 'Disabled'}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Cache Type:</span>
                                <span class="detail-value">${cache.type || 'Memory'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total Entries:</span>
                                <span class="detail-value">${cache.entries || 0}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Hit Rate:</span>
                                <span class="detail-value">${cache.hitRate || 0}%</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Cache Size:</span>
                                <span class="detail-value">${cache.size_mb || 0} MB</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total Hits:</span>
                                <span class="detail-value">${cache.total_hits || 0}</span>
                            </div>
                            ${cache.error ? `
                            <div class="detail-item" style="grid-column: 1/-1;">
                                <span class="detail-label">Error:</span>
                                <span class="detail-value text-danger">${cache.error}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- MPS API Engine Status -->
                    <div class="engine-section">
                        <h3><i class="fas fa-cogs"></i> MPS API Engine</h3>
                        <div class="engine-detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">API Status:</span>
                                <span class="detail-value ${mpsStatus === 'online' ? 'text-success' : 'text-warning'}">
                                    <i class="fas fa-${mpsStatus === 'online' ? 'check' : 'exclamation-circle'}"></i>
                                    ${mpsStatus}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Engine Version:</span>
                                <span class="detail-value">${mpsVersion}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Operations Loaded:</span>
                                <span class="detail-value">${actionCount}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Session User:</span>
                                <span class="detail-value">${auth.username || 'N/A'}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Authentication Status -->
                    <div class="engine-section">
                        <h3><i class="fas fa-shield-alt"></i> Authentication</h3>
                        <div class="engine-detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Session Status:</span>
                                <span class="detail-value ${auth.sessionActive ? 'text-success' : 'text-danger'}">
                                    <i class="fas fa-${auth.sessionActive ? 'check' : 'times'}"></i>
                                    ${auth.sessionActive ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Logged in as:</span>
                                <span class="detail-value">${auth.username || 'N/A'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Auth Type:</span>
                                <span class="detail-value">Session-based</span>
                            </div>
                        </div>
                    </div>

                    <!-- Raw JSON (for debugging) -->
                    <div class="engine-section">
                        <h3><i class="fas fa-code"></i> Raw Status Data</h3>
                        <div class="engine-raw-data">
                            <pre><code>${JSON.stringify(status, null, 2)}</code></pre>
                        </div>
                    </div>
                </div>
            `;

        } catch (error) {
            console.error('Failed to load engine status:', error);
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <p>Failed to load engine status</p>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem; color: var(--text-secondary);">
                        ${error.message}
                    </p>
                </div>
            `;
        }
    }

    /**
     * Load cache statistics from MySQL cache
     */
    async function loadCacheStats() {
        const container = document.getElementById('cache-entries-list');
        container.innerHTML = '<div class="loading">Loading cache data...</div>';

        try {
            // Fetch cache stats from MySQL backend
            const statsResponse = await fetch('api/cache-manager.php');
            const statsData = await statsResponse.json();

            if (!statsData.success) {
                throw new Error(statsData.error || 'Failed to load cache stats');
            }

            const stats = statsData.stats;

            // Update summary stats
            document.getElementById('cache-hit-rate').textContent = `${stats.hit_rate || 0}%`;
            document.getElementById('cache-total-entries').textContent = stats.active_entries || 0;
            document.getElementById('cache-total-size').textContent = `${stats.size_mb || 0} MB`;
            document.getElementById('cache-hit-miss').textContent = `${stats.total_hits || 0} hits`;

            // Fetch cache entries
            const entriesResponse = await fetch('api/cache-manager.php?action=entries');
            const entriesData = await entriesResponse.json();

            const entries = entriesData.entries || [];

            // Build cache entries table
            if (entries.length === 0) {
                container.innerHTML = '<p style="text-align:center;color:var(--text-secondary);">No cache entries found</p>';
                return;
            }

            const html = `
                <table class="cache-entry-table">
                    <thead>
                        <tr>
                            <th>Cache Key</th>
                            <th>Size</th>
                            <th>Created</th>
                            <th>Expires</th>
                            <th>Hits</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${entries.map(entry => {
                            const sizeKB = Math.round(entry.size_bytes / 1024);
                            const status = entry.status === 'active' ? 'cache-status-fresh' : 'cache-status-expired';
                            const statusText = entry.status === 'active' ? 'Active' : 'Expired';

                            return `
                                <tr>
                                    <td><code>${entry.cache_key}</code></td>
                                    <td>${sizeKB} KB</td>
                                    <td>${new Date(entry.created_at).toLocaleString()}</td>
                                    <td>${new Date(entry.expires_at).toLocaleString()}</td>
                                    <td>${entry.hit_count}</td>
                                    <td class="${status}">${statusText}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            `;

            container.innerHTML = html;

        } catch (error) {
            console.error('Failed to load cache stats:', error);
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <p>Failed to load cache data</p>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem; color: var(--text-secondary);">
                        ${error.message}
                    </p>
                </div>
            `;
        }
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

    /**
     * Load users list
     */
    async function loadUsers() {
        try {
            const response = await fetch('api/auth.php');
            const data = await response.json();

            const container = document.getElementById('users-list');

            if (data.success && data.users) {
                container.innerHTML = `
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.users.map(user => `
                                <tr>
                                    <td>${user.id}</td>
                                    <td>${user.username}</td>
                                    <td>${user.created ? new Date(user.created).toLocaleDateString() : '-'}</td>
                                    <td>
                                        ${user.id === 1 ? '<span class="badge">Admin</span>' :
                                        `<button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">Delete</button>`}
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }
        } catch (error) {
            console.error('Failed to load users:', error);
        }
    }

    /**
     * Delete user
     */
    window.deleteUser = async function(userId) {
        if (!confirm('Delete this user?')) return;

        try {
            const response = await fetch('api/auth.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId })
            });

            const data = await response.json();

            if (data.success) {
                loadUsers();
                showToast('User deleted', 'success');
            } else {
                showToast(data.error || 'Failed to delete user', 'error');
            }
        } catch (error) {
            showToast('Failed to delete user', 'error');
        }
    };

    // User management event listeners
    document.getElementById('add-user-btn')?.addEventListener('click', () => {
        document.getElementById('add-user-modal').style.display = 'flex';
        document.getElementById('new-username').value = '';
        document.getElementById('new-password').value = '';
    });

    document.getElementById('cancel-user-btn')?.addEventListener('click', () => {
        document.getElementById('add-user-modal').style.display = 'none';
    });

    document.getElementById('save-user-btn')?.addEventListener('click', async () => {
        const username = document.getElementById('new-username').value;
        const password = document.getElementById('new-password').value;

        if (!username || !password) {
            showToast('Username and password required', 'error');
            return;
        }

        try {
            const response = await fetch('api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'create', username, password })
            });

            const data = await response.json();

            if (data.success) {
                document.getElementById('add-user-modal').style.display = 'none';
                loadUsers();
                showToast('User created', 'success');
            } else {
                showToast(data.error || 'Failed to create user', 'error');
            }
        } catch (error) {
            showToast('Failed to create user', 'error');
        }
    });

    // Test functions for connectivity verification
    window.testDatabaseConnection = async function() {
        showToast('Testing database connection...', 'info');
        try {
            const response = await fetch('api/system-health.php');
            const health = await response.json();

            if (health.database.connected) {
                showToast(`✓ Database connected: ${health.database.table_count} tables found`, 'success');
            } else {
                showToast(`✗ Database disconnected: ${health.database.error}`, 'error');
            }
        } catch (error) {
            showToast(`✗ Test failed: ${error.message}`, 'error');
        }
    };

    window.testCacheConnection = async function() {
        showToast('Testing cache connection...', 'info');
        try {
            const response = await fetch('api/system-health.php');
            const health = await response.json();

            if (health.cache.enabled) {
                showToast(`✓ Cache enabled: ${health.cache.entries} entries, ${health.cache.hitRate}% hit rate`, 'success');
            } else {
                showToast(`✗ Cache disabled: ${health.cache.error}`, 'warning');
            }
        } catch (error) {
            showToast(`✗ Test failed: ${error.message}`, 'error');
        }
    };

    window.testMPSAPI = async function() {
        showToast('Testing MPS API connection...', 'info');
        try {
            const status = await MPSApi.getEngineStatus();

            if (status.status === 'online') {
                showToast(`✓ MPS API online: ${status.action_count} operations available`, 'success');
            } else {
                showToast(`✗ MPS API status: ${status.status}`, 'warning');
            }
        } catch (error) {
            showToast(`✗ Test failed: ${error.message}`, 'error');
        }
    };

    // Initialize on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
