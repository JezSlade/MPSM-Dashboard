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
     *   render: function(data, container),
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
        render: function(data, container) {
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
                    render: (value) => {
                        const statusClass = value === 'Online' ? 'status-online' : 'status-offline';
                        return `<span class="status-badge ${statusClass}">${value || 'Unknown'}</span>`;
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
                pageSize: 10,
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
        render: function(data, container) {
            if (!data) {
                container.innerHTML = '<div class="empty">No supply data available</div>';
                return;
            }

            let html = '<div class="supply-grid">';

            if (Array.isArray(data)) {
                data.forEach(supply => {
                    const level = supply.Level || 0;
                    const color = supply.Color || 'Black';
                    const status = level < 20 ? 'critical' : level < 50 ? 'warning' : 'good';

                    html += `
                        <div class="supply-item supply-${status}">
                            <div class="supply-name">${color}</div>
                            <div class="supply-bar">
                                <div class="supply-fill" style="width: ${level}%"></div>
                            </div>
                            <div class="supply-level">${level}%</div>
                        </div>
                    `;
                });
            }

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
        render: function(data, container) {
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
                pageSize: 10,
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
        render: function(data, container) {
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<div class="empty">✓ No active alerts</div>';
                return;
            }

            let html = '<div class="alert-list">';
            data.forEach(alert => {
                const severity = alert.Severity || 'info';
                html += `
                    <div class="alert-item alert-${severity}">
                        <div class="alert-icon">${severity === 'critical' ? '🔴' : '⚠️'}</div>
                        <div class="alert-content">
                            <div class="alert-title">${alert.AlertType || 'Alert'}</div>
                            <div class="alert-message">${alert.Message || 'No details'}</div>
                            <div class="alert-time">${TableUtils.renderDate(alert.CreatedAt)}</div>
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
        render: function(data, container) {
            if (!data) {
                container.innerHTML = '<div class="error">No customer data</div>';
                return;
            }

            container.innerHTML = `
                <div class="customer-overview">
                    <div class="customer-stat">
                        <div class="stat-label">Customer</div>
                        <div class="stat-value">${data.CustomerName || 'N/A'}</div>
                    </div>
                    <div class="customer-stat">
                        <div class="stat-label">Devices</div>
                        <div class="stat-value">${data.TotalDevices || 0}</div>
                    </div>
                    <div class="customer-stat">
                        <div class="stat-label">Total Prints</div>
                        <div class="stat-value">${TableUtils.renderCounter(data.TotalPrints || 0)}</div>
                    </div>
                    <div class="customer-stat">
                        <div class="stat-label">Status</div>
                        <div class="stat-value status-${(data.Status || 'inactive').toLowerCase()}">${data.Status || 'Inactive'}</div>
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
        render: function(data, container) {
            if (!Array.isArray(data)) {
                container.innerHTML = '<div class="empty">No supplies available</div>';
                return;
            }

            const columns = [
                { key: 'PartNumber', label: 'Part #', width: '20%' },
                { key: 'Description', label: 'Description', width: '40%' },
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
                { key: 'Value', label: 'Price', width: '10%', render: (val) => `$${val || 0}` }
            ];

            const table = TableUtils.createPaginatedTable(data, columns, {
                pageSize: 15,
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
        render: function(data, container) {
            if (!data || !data.ReportUrl) {
                container.innerHTML = '<div class="empty">No reports available</div>';
                return;
            }

            container.innerHTML = `
                <div class="report-item">
                    <div class="report-icon">📄</div>
                    <div class="report-info">
                        <div class="report-name">Generated Report</div>
                        <div class="report-meta">Format: ${data.ReportFormat || 'Excel'} | Generated: ${TableUtils.renderDate(data.GeneratedAt)}</div>
                    </div>
                    <a href="${data.ReportUrl}" class="btn btn-primary" download>Download</a>
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
        render: function(data, container) {
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
                pageSize: 10
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
        render: function(data, container) {
            if (!Array.isArray(data)) {
                container.innerHTML = '<div class="empty">No API clients configured</div>';
                return;
            }

            const columns = [
                { key: 'Name', label: 'Name', width: '25%' },
                { key: 'AppId', label: 'App ID', width: '30%' },
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
                    render: (val) => val ? '<span class="status-online">Active</span>' : '<span class="status-offline">Inactive</span>'
                },
                { key: 'DeveloperEmail', label: 'Developer', width: '15%' }
            ];

            const table = TableUtils.createPaginatedTable(data, columns, {
                pageSize: 10
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
