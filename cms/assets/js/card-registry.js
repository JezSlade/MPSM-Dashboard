/**
 * Card Registry - Manages all available dashboard cards
 * Each card is registered with metadata about its data source, rendering, and configuration
 */

const CardRegistry = (function() {
    'use strict';

    const cards = new Map();

    /**
     * Card definition structure:
     * {
     *   id: string,
     *   name: string,
     *   description: string,
     *   category: string,
     *   endpoint: string,
     *   icon: string,
     *   defaultVisible: boolean,
     *   defaultOrder: number,
     *   fetchData: async function(),
     *   renderSnapshot: function(data, container),  // Compact view
     *   renderModal: function(data, container),     // Full detail view
     *   requiresParams: array
     * }
     */

    // Device/Printer Cards
    cards.set('printers', {
        id: 'printers',
        name: 'Printer List',
        description: 'Display all printers/devices with status, location, and counter information',
        category: 'Devices',
        endpoint: 'Device/List',
        icon: '🖨️',
        defaultVisible: true,
        defaultOrder: 1,
        requiresParams: ['FilterDealerId'],
        fetchData: async (params) => {
            return await MPSApi.query('Device/List', {
                FilterDealerId: params.dealerId,
                pageNumber: 1,
                pageRows: 100
            });
        },
        renderSnapshot: function(data, container) {
            if (!Array.isArray(data)) {
                container.innerHTML = '<div class="error">No data available</div>';
                return;
            }

            const total = data.length;
            const online = data.filter(d => d.Status === 'Online' || d.IsOnline).length;
            const offline = total - online;
            const totalPrints = data.reduce((sum, d) => sum + (d.TotalCounter || 0), 0);

            container.innerHTML = `
                <div class="card-snapshot">
                    <div class="snapshot-stat-grid">
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${total}</div>
                            <div class="snapshot-label">Total Devices</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value success">${online}</div>
                            <div class="snapshot-label">Online</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value ${offline > 0 ? 'warning' : ''}">${offline}</div>
                            <div class="snapshot-label">Offline</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${TableUtils.renderCounter(totalPrints)}</div>
                            <div class="snapshot-label">Total Prints</div>
                        </div>
                    </div>
                    <div class="card-click-hint">Click for details</div>
                </div>
            `;
        },
        renderModal: function(data, container) {
            if (!Array.isArray(data)) {
                container.innerHTML = '<div class="error">No data available</div>';
                return;
            }

            const columns = [
                { key: 'SerialNumber', label: 'Serial Number', width: '15%' },
                { key: 'Model', label: 'Model', width: '20%' },
                { key: 'Location', label: 'Location', width: '15%' },
                {
                    key: 'Status',
                    label: 'Status',
                    width: '10%',
                    render: (value, row) => {
                        const isOnline = value === 'Online' || row.IsOnline;
                        const statusClass = isOnline ? 'status-online' : 'status-offline';
                        return `<span class="status-badge ${statusClass}">${isOnline ? 'Online' : 'Offline'}</span>`;
                    }
                },
                {
                    key: 'TotalCounter',
                    label: 'Total Prints',
                    width: '15%',
                    render: TableUtils.renderCounter
                },
                { key: 'IPAddress', label: 'IP Address', width: '15%' }
            ];

            const table = TableUtils.createPaginatedTable(data, columns, {
                pageSize: 20,
                searchable: true,
                sortable: true
            });

            container.innerHTML = table.html;
            table.setup(container);
        }
    });

    cards.set('device-supplies', {
        id: 'device-supplies',
        name: 'Device Supply Levels',
        description: 'Monitor toner and supply levels across all devices',
        category: 'Devices',
        endpoint: 'Device/GetSuppliesDetailsSummary',
        icon: '💧',
        defaultVisible: true,
        defaultOrder: 2,
        requiresParams: ['deviceId'],
        fetchData: async (params) => {
            // For summary, we'd need to aggregate across devices
            // This is a placeholder - in reality would loop through devices
            if (!params.deviceId) {
                throw new Error('Device ID required');
            }
            return await MPSApi.query('Device/GetSuppliesDetailsSummary', {
                id: params.deviceId
            });
        },
        renderSnapshot: function(data, container) {
            if (!data || !Array.isArray(data)) {
                container.innerHTML = '<div class="empty">No supply data available</div>';
                return;
            }

            const critical = data.filter(s => (s.Level || 0) < 20).length;
            const warning = data.filter(s => (s.Level || 0) >= 20 && (s.Level || 0) < 50).length;
            const good = data.filter(s => (s.Level || 0) >= 50).length;
            const avgLevel = data.reduce((sum, s) => sum + (s.Level || 0), 0) / data.length;

            container.innerHTML = `
                <div class="card-snapshot">
                    <div class="snapshot-stat-grid">
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${data.length}</div>
                            <div class="snapshot-label">Supplies</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value ${critical > 0 ? 'critical' : ''}">${critical}</div>
                            <div class="snapshot-label">Critical</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value ${warning > 0 ? 'warning' : ''}">${warning}</div>
                            <div class="snapshot-label">Low</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value success">${Math.round(avgLevel)}%</div>
                            <div class="snapshot-label">Avg Level</div>
                        </div>
                    </div>
                    <div class="card-click-hint">Click for details</div>
                </div>
            `;
        },
        renderModal: function(data, container) {
            if (!data || !Array.isArray(data)) {
                container.innerHTML = '<div class="empty">No supply data available</div>';
                return;
            }

            let html = '<div class="supply-grid-expanded">';

            data.forEach(supply => {
                const level = supply.Level || 0;
                const color = supply.Color || 'Black';
                const type = supply.Type || 'Toner';
                const status = level < 20 ? 'critical' : level < 50 ? 'warning' : 'good';

                html += `
                    <div class="supply-item supply-${status}">
                        <div class="supply-header">
                            <div class="supply-name">${color} ${type}</div>
                            <div class="supply-level-text">${level}%</div>
                        </div>
                        <div class="supply-bar">
                            <div class="supply-fill" style="width: ${level}%"></div>
                        </div>
                        ${supply.PartNumber ? `<div class="supply-part">Part: ${supply.PartNumber}</div>` : ''}
                    </div>
                `;
            });

            html += '</div>';
            container.innerHTML = html;
        }
    });

    // Counter/Meter Cards
    cards.set('meter-reads', {
        id: 'meter-reads',
        name: 'Meter Readings',
        description: 'Display recent meter readings and counter history',
        category: 'Counters',
        endpoint: 'Counter/Device/List',
        icon: '📊',
        defaultVisible: true,
        defaultOrder: 3,
        requiresParams: ['deviceId', 'fromDate', 'toDate'],
        fetchData: async (params) => {
            const now = new Date();
            const thirtyDaysAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);

            return await MPSApi.query('Counter/Device/List', {
                id: params.deviceId,
                fromDate: thirtyDaysAgo.toISOString(),
                toDate: now.toISOString()
            });
        },
        renderSnapshot: function(data, container) {
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<div class="empty">No meter data available</div>';
                return;
            }

            const latest = data[0] || {};
            const oldest = data[data.length - 1] || {};
            const totalGrowth = (latest.TotalCounter || 0) - (oldest.TotalCounter || 0);
            const avgDaily = Math.round(totalGrowth / Math.max(data.length, 1));

            container.innerHTML = `
                <div class="card-snapshot">
                    <div class="snapshot-stat-grid">
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${TableUtils.renderCounter(latest.TotalCounter || 0)}</div>
                            <div class="snapshot-label">Current Total</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${TableUtils.renderCounter(latest.MonoCounter || 0)}</div>
                            <div class="snapshot-label">B&W</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${TableUtils.renderCounter(latest.ColorCounter || 0)}</div>
                            <div class="snapshot-label">Color</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${TableUtils.renderCounter(avgDaily)}</div>
                            <div class="snapshot-label">Avg Daily</div>
                        </div>
                    </div>
                    <div class="card-click-hint">Click for details</div>
                </div>
            `;
        },
        renderModal: function(data, container) {
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<div class="empty">No meter data available</div>';
                return;
            }

            const columns = [
                {
                    key: 'ReadDate',
                    label: 'Date',
                    width: '25%',
                    render: TableUtils.renderDate
                },
                {
                    key: 'MonoCounter',
                    label: 'B&W',
                    width: '25%',
                    render: TableUtils.renderCounter
                },
                {
                    key: 'ColorCounter',
                    label: 'Color',
                    width: '25%',
                    render: TableUtils.renderCounter
                },
                {
                    key: 'TotalCounter',
                    label: 'Total',
                    width: '25%',
                    render: TableUtils.renderCounter
                }
            ];

            const table = TableUtils.createPaginatedTable(data, columns, {
                pageSize: 15,
                sortable: true
            });

            container.innerHTML = table.html;
            table.setup(container);
        }
    });

    // Alert/Error Cards
    cards.set('device-alerts', {
        id: 'device-alerts',
        name: 'Device Alerts',
        description: 'Show maintenance alerts and device warnings',
        category: 'Alerts',
        endpoint: 'Device/MaintenanceAlerts/List',
        icon: '⚠️',
        defaultVisible: true,
        defaultOrder: 4,
        requiresParams: ['idInstalledProduct'],
        fetchData: async (params) => {
            return await MPSApi.query('Device/MaintenanceAlerts/List', {
                idInstalledProduct: params.idInstalledProduct
            });
        },
        renderSnapshot: function(data, container) {
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = `
                    <div class="card-snapshot">
                        <div class="snapshot-empty success">
                            <div class="snapshot-icon">✓</div>
                            <div class="snapshot-message">No Active Alerts</div>
                        </div>
                        <div class="card-click-hint">Click for details</div>
                    </div>
                `;
                return;
            }

            const critical = data.filter(a => a.Severity === 'critical').length;
            const warnings = data.filter(a => a.Severity === 'warning').length;
            const info = data.length - critical - warnings;

            container.innerHTML = `
                <div class="card-snapshot">
                    <div class="snapshot-stat-grid">
                        <div class="snapshot-stat">
                            <div class="snapshot-value ${data.length > 0 ? 'warning' : ''}">${data.length}</div>
                            <div class="snapshot-label">Total Alerts</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value ${critical > 0 ? 'critical' : ''}">${critical}</div>
                            <div class="snapshot-label">Critical</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value ${warnings > 0 ? 'warning' : ''}">${warnings}</div>
                            <div class="snapshot-label">Warnings</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${info}</div>
                            <div class="snapshot-label">Info</div>
                        </div>
                    </div>
                    <div class="card-click-hint">Click for details</div>
                </div>
            `;
        },
        renderModal: function(data, container) {
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<div class="empty success">✓ No active alerts</div>';
                return;
            }

            let html = '<div class="alert-list-expanded">';
            data.forEach(alert => {
                const severity = alert.Severity || 'info';
                html += `
                    <div class="alert-item alert-${severity}">
                        <div class="alert-icon">${severity === 'critical' ? '🔴' : severity === 'warning' ? '⚠️' : 'ℹ️'}</div>
                        <div class="alert-content">
                            <div class="alert-title">${alert.AlertType || 'Alert'}</div>
                            <div class="alert-message">${alert.Message || 'No details'}</div>
                            <div class="alert-time">${TableUtils.renderDate(alert.CreatedAt)}</div>
                            ${alert.DeviceSerial ? `<div class="alert-device">Device: ${alert.DeviceSerial}</div>` : ''}
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        }
    });

    // Customer Cards
    cards.set('customer-dashboard', {
        id: 'customer-dashboard',
        name: 'Customer Overview',
        description: 'Display customer information and statistics',
        category: 'Customer',
        endpoint: 'CustomerDashboard/Get',
        icon: '🏢',
        defaultVisible: true,
        defaultOrder: 0,
        requiresParams: ['customerCode'],
        fetchData: async (params) => {
            return await MPSApi.query('CustomerDashboard/Get', {
                customerCode: params.customerCode
            });
        },
        renderSnapshot: function(data, container) {
            if (!data) {
                container.innerHTML = '<div class="error">No customer data</div>';
                return;
            }

            container.innerHTML = `
                <div class="card-snapshot">
                    <div class="snapshot-stat-grid">
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${data.TotalDevices || 0}</div>
                            <div class="snapshot-label">Devices</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${TableUtils.renderCounter(data.TotalPrints || 0)}</div>
                            <div class="snapshot-label">Total Prints</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${data.ActiveUsers || 0}</div>
                            <div class="snapshot-label">Users</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value ${(data.Status || 'inactive').toLowerCase() === 'active' ? 'success' : 'warning'}">${data.Status || 'N/A'}</div>
                            <div class="snapshot-label">Status</div>
                        </div>
                    </div>
                    <div class="card-click-hint">Click for details</div>
                </div>
            `;
        },
        renderModal: function(data, container) {
            if (!data) {
                container.innerHTML = '<div class="error">No customer data</div>';
                return;
            }

            container.innerHTML = `
                <div class="customer-overview-expanded">
                    <div class="customer-detail-grid">
                        <div class="customer-detail-item">
                            <div class="detail-label">Customer Name</div>
                            <div class="detail-value">${data.CustomerName || 'N/A'}</div>
                        </div>
                        <div class="customer-detail-item">
                            <div class="detail-label">Customer Code</div>
                            <div class="detail-value">${data.CustomerCode || 'N/A'}</div>
                        </div>
                        <div class="customer-detail-item">
                            <div class="detail-label">Total Devices</div>
                            <div class="detail-value">${data.TotalDevices || 0}</div>
                        </div>
                        <div class="customer-detail-item">
                            <div class="detail-label">Active Devices</div>
                            <div class="detail-value">${data.ActiveDevices || 0}</div>
                        </div>
                        <div class="customer-detail-item">
                            <div class="detail-label">Total Prints</div>
                            <div class="detail-value">${TableUtils.renderCounter(data.TotalPrints || 0)}</div>
                        </div>
                        <div class="customer-detail-item">
                            <div class="detail-label">Monthly Average</div>
                            <div class="detail-value">${TableUtils.renderCounter(data.MonthlyAvgPrints || 0)}</div>
                        </div>
                        <div class="customer-detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span class="status-badge status-${(data.Status || 'inactive').toLowerCase()}">${data.Status || 'Inactive'}</span>
                            </div>
                        </div>
                        <div class="customer-detail-item">
                            <div class="detail-label">Last Activity</div>
                            <div class="detail-value">${data.LastActivity ? TableUtils.renderDate(data.LastActivity) : 'N/A'}</div>
                        </div>
                    </div>
                </div>
            `;
        }
    });

    // Dealer Cards
    cards.set('dealer-supplies', {
        id: 'dealer-supplies',
        name: 'Dealer Supply Catalog',
        description: 'View available supplies and inventory',
        category: 'Dealer',
        endpoint: 'DealerSupply/List',
        icon: '📦',
        defaultVisible: false,
        defaultOrder: 10,
        requiresParams: ['dealerCode'],
        fetchData: async (params) => {
            return await MPSApi.query('DealerSupply/List', {
                dealerCode: params.dealerCode,
                pageNumber: 1,
                pageRows: 50
            });
        },
        renderSnapshot: function(data, container) {
            if (!Array.isArray(data)) {
                container.innerHTML = '<div class="empty">No supplies available</div>';
                return;
            }

            const toner = data.filter(s => s.SupplyType === 3).length;
            const ink = data.filter(s => s.SupplyType === 2).length;
            const other = data.length - toner - ink;
            const avgPrice = data.reduce((sum, s) => sum + (s.Value || 0), 0) / data.length;

            container.innerHTML = `
                <div class="card-snapshot">
                    <div class="snapshot-stat-grid">
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${data.length}</div>
                            <div class="snapshot-label">Total Items</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${toner}</div>
                            <div class="snapshot-label">Toner</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${ink}</div>
                            <div class="snapshot-label">Ink</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">$${avgPrice.toFixed(2)}</div>
                            <div class="snapshot-label">Avg Price</div>
                        </div>
                    </div>
                    <div class="card-click-hint">Click for details</div>
                </div>
            `;
        },
        renderModal: function(data, container) {
            if (!Array.isArray(data)) {
                container.innerHTML = '<div class="empty">No supplies available</div>';
                return;
            }

            const columns = [
                { key: 'PartNumber', label: 'Part #', width: '15%' },
                { key: 'Description', label: 'Description', width: '35%' },
                {
                    key: 'SupplyType',
                    label: 'Type',
                    width: '15%',
                    render: (val) => val === 3 ? 'Toner' : val === 2 ? 'Ink' : 'Other'
                },
                {
                    key: 'Duration',
                    label: 'Yield',
                    width: '15%',
                    render: TableUtils.renderCounter
                },
                { key: 'Value', label: 'Price', width: '10%', render: (val) => `$${(val || 0).toFixed(2)}` },
                { key: 'Stock', label: 'Stock', width: '10%' }
            ];

            const table = TableUtils.createPaginatedTable(data, columns, {
                pageSize: 20,
                searchable: true,
                sortable: true
            });

            container.innerHTML = table.html;
            table.setup(container);
        }
    });

    // Analytics/Reporting Cards
    cards.set('analytics-reports', {
        id: 'analytics-reports',
        name: 'Analytics & Reports',
        description: 'Access saved analytics reports and visualizations',
        category: 'Analytics',
        endpoint: 'Analytics/GetReportFileResult',
        icon: '📈',
        defaultVisible: false,
        defaultOrder: 15,
        requiresParams: ['idReport'],
        fetchData: async (params) => {
            return await MPSApi.query('Analytics/GetReportFileResult', {
                idReport: params.idReport || 1,
                reportFormat: 'Excel'
            });
        },
        renderSnapshot: function(data, container) {
            if (!data || !data.ReportUrl) {
                container.innerHTML = '<div class="empty">No reports available</div>';
                return;
            }

            container.innerHTML = `
                <div class="card-snapshot">
                    <div class="snapshot-report">
                        <div class="report-icon-large">📄</div>
                        <div class="snapshot-value">Report Ready</div>
                        <div class="snapshot-label">${data.ReportFormat || 'Excel'} Format</div>
                    </div>
                    <div class="card-click-hint">Click to download</div>
                </div>
            `;
        },
        renderModal: function(data, container) {
            if (!data || !data.ReportUrl) {
                container.innerHTML = '<div class="empty">No reports available</div>';
                return;
            }

            container.innerHTML = `
                <div class="report-detail-expanded">
                    <div class="report-header">
                        <div class="report-icon-large">📄</div>
                        <div class="report-title">Analytics Report</div>
                    </div>
                    <div class="report-metadata">
                        <div class="metadata-item">
                            <span class="metadata-label">Format:</span>
                            <span class="metadata-value">${data.ReportFormat || 'Excel'}</span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Generated:</span>
                            <span class="metadata-value">${data.GeneratedAt ? TableUtils.renderDate(data.GeneratedAt) : 'N/A'}</span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">File Size:</span>
                            <span class="metadata-value">${data.FileSize ? (data.FileSize / 1024).toFixed(2) + ' KB' : 'N/A'}</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <a href="${data.ReportUrl}" class="btn btn-primary btn-lg" download>
                            Download Report
                        </a>
                    </div>
                </div>
            `;
        }
    });

    // Explorer/Integration Cards
    cards.set('explorer-data', {
        id: 'explorer-data',
        name: 'Explorer Data Collection',
        description: 'Monitor Explorer data collection agents and configurations',
        category: 'Integration',
        endpoint: 'Explorer/GetExplorerDatas',
        icon: '🔍',
        defaultVisible: false,
        defaultOrder: 20,
        requiresParams: ['customerCode'],
        fetchData: async (params) => {
            return await MPSApi.query('Explorer/GetExplorerDatas', {
                customerCode: params.customerCode
            });
        },
        renderSnapshot: function(data, container) {
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<div class="empty">No Explorer data collectors configured</div>';
                return;
            }

            const active = data.filter(d => d.Status && d.Status.toLowerCase() === 'active').length;
            const totalDevices = data.reduce((sum, d) => sum + (d.DeviceCount || 0), 0);
            const avgVersion = data[0]?.Version || 'N/A';

            container.innerHTML = `
                <div class="card-snapshot">
                    <div class="snapshot-stat-grid">
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${data.length}</div>
                            <div class="snapshot-label">Collectors</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value ${active === data.length ? 'success' : 'warning'}">${active}</div>
                            <div class="snapshot-label">Active</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${totalDevices}</div>
                            <div class="snapshot-label">Devices</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${avgVersion}</div>
                            <div class="snapshot-label">Version</div>
                        </div>
                    </div>
                    <div class="card-click-hint">Click for details</div>
                </div>
            `;
        },
        renderModal: function(data, container) {
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<div class="empty">No Explorer data collectors configured</div>';
                return;
            }

            const columns = [
                { key: 'Identifier', label: 'Identifier', width: '30%' },
                { key: 'Version', label: 'Version', width: '15%' },
                {
                    key: 'LastContact',
                    label: 'Last Contact',
                    width: '25%',
                    render: TableUtils.renderDate
                },
                {
                    key: 'Status',
                    label: 'Status',
                    width: '15%',
                    render: (val) => {
                        const status = val || 'unknown';
                        return `<span class="status-badge status-${status.toLowerCase()}">${status}</span>`;
                    }
                },
                { key: 'DeviceCount', label: 'Devices', width: '15%' }
            ];

            const table = TableUtils.createPaginatedTable(data, columns, {
                pageSize: 15
            });

            container.innerHTML = table.html;
            table.setup(container);
        }
    });

    // API/System Cards
    cards.set('api-clients', {
        id: 'api-clients',
        name: 'API Clients',
        description: 'Manage API client applications and credentials',
        category: 'System',
        endpoint: 'ApiClient/List',
        icon: '🔐',
        defaultVisible: false,
        defaultOrder: 25,
        requiresParams: [],
        fetchData: async (params) => {
            return await MPSApi.query('ApiClient/List', {});
        },
        renderSnapshot: function(data, container) {
            if (!Array.isArray(data)) {
                container.innerHTML = '<div class="empty">No API clients configured</div>';
                return;
            }

            const active = data.filter(c => c.IsActive).length;
            const inactive = data.length - active;
            const webApps = data.filter(c => c.ApplicationType === 1).length;
            const mobileApps = data.filter(c => c.ApplicationType === 2).length;

            container.innerHTML = `
                <div class="card-snapshot">
                    <div class="snapshot-stat-grid">
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${data.length}</div>
                            <div class="snapshot-label">Total Clients</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value success">${active}</div>
                            <div class="snapshot-label">Active</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${webApps}</div>
                            <div class="snapshot-label">Web</div>
                        </div>
                        <div class="snapshot-stat">
                            <div class="snapshot-value">${mobileApps}</div>
                            <div class="snapshot-label">Mobile</div>
                        </div>
                    </div>
                    <div class="card-click-hint">Click for details</div>
                </div>
            `;
        },
        renderModal: function(data, container) {
            if (!Array.isArray(data)) {
                container.innerHTML = '<div class="empty">No API clients configured</div>';
                return;
            }

            const columns = [
                { key: 'Name', label: 'Name', width: '20%' },
                { key: 'AppId', label: 'App ID', width: '25%' },
                {
                    key: 'ApplicationType',
                    label: 'Type',
                    width: '15%',
                    render: (val) => val === 1 ? 'Web' : val === 2 ? 'Mobile' : 'Other'
                },
                {
                    key: 'IsActive',
                    label: 'Status',
                    width: '15%',
                    render: (val) => val ? '<span class="status-badge status-online">Active</span>' : '<span class="status-badge status-offline">Inactive</span>'
                },
                { key: 'DeveloperEmail', label: 'Developer', width: '25%' }
            ];

            const table = TableUtils.createPaginatedTable(data, columns, {
                pageSize: 15,
                searchable: true
            });

            container.innerHTML = table.html;
            table.setup(container);
        }
    });

    // Public API
    return {
        /**
         * Get all registered cards
         */
        getAll: function() {
            return Array.from(cards.values());
        },

        /**
         * Get a specific card by ID
         */
        get: function(cardId) {
            return cards.get(cardId);
        },

        /**
         * Get cards by category
         */
        getByCategory: function(category) {
            return Array.from(cards.values()).filter(card => card.category === category);
        },

        /**
         * Get all unique categories
         */
        getCategories: function() {
            const categories = new Set();
            cards.forEach(card => categories.add(card.category));
            return Array.from(categories).sort();
        },

        /**
         * Register a new card or override existing
         */
        register: function(cardDefinition) {
            if (!cardDefinition.id) {
                throw new Error('Card must have an id');
            }
            cards.set(cardDefinition.id, cardDefinition);
        },

        /**
         * Check if a card exists
         */
        has: function(cardId) {
            return cards.has(cardId);
        }
    };
})();

// Make globally available
window.CardRegistry = CardRegistry;
