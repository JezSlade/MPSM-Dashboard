/**
 * CardRegistry - authoritative list of dashboard cards.
 * Each card definition describes how to fetch snapshot data and render modal drill-downs.
 */

const CardRegistry = (function () {
    'use strict';

    const definitions = [
        {
            id: 'customer-overview',
            title: 'Customer Snapshot',
            icon: 'fas fa-building',
            description: 'Key metrics for the selected customer',
            group: 'Summary',
            defaultVisible: true,
            load: async (helpers, context) => {
                const data = await helpers.fetchJson('api/get-customer-dashboard.php', {
                    customerCode: context.customerCode
                });

                const dashboard = data.dashboard || {};
                const totalsSource = dashboard.MpsDashboardCustomer || dashboard;
                const contacted = Array.isArray(dashboard.ContactedDevices) ? dashboard.ContactedDevices : [];
                const books = Array.isArray(dashboard.Books) ? dashboard.Books : [];
                const supplyAlerts = Array.isArray(totalsSource.SupplyAlerts) ? totalsSource.SupplyAlerts : [];

                const toManage = supplyAlerts.find(item => (item.Key || '').toLowerCase() === 'tomanage');

                return {
                    headline: {
                        value: totalsSource.TotalManagedDevices ?? 0,
                        label: 'Devices'
                    },
                    metrics: [
                        { label: 'Connectors', value: totalsSource.TotalConnectors ?? 0 },
                        { label: 'Alerts', value: Number(toManage?.Value ?? 0) },
                        { label: 'Enabled', value: totalsSource.EnabledDevicesByContract ?? 0 }
                    ],
                    context: {
                        contacted,
                        books,
                        supplyAlerts
                    }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Customer Overview');
                const { contacted = [], books = [], supplyAlerts = [] } = snapshot.context || {};

                const contactedHtml = contacted.length
                    ? contacted.map(item => `<li><strong>${helpers.escape(item.Key)}:</strong> ${helpers.escape(item.Value)}</li>`).join('')
                    : '<li>No recent device contact data.</li>';

                const booksHtml = books.length
                    ? books.map(item => `<li>${helpers.escape(item.Key)}: ${helpers.escape(item.Value)}</li>`).join('')
                    : '<li>No book statistics.</li>';

                const alertsHtml = supplyAlerts.length
                    ? supplyAlerts.map(item => `<li>${helpers.escape(item.Key)}: ${helpers.escape(item.Value)}</li>`).join('')
                    : '<li>No supply alerts statistics.</li>';

                modal.innerHTML = `
                    <section class="modal-section">
                        <h3><i class="fas fa-signal"></i> Device Contact (Last 3 Days)</h3>
                        <ul class="metric-list">${contactedHtml}</ul>
                    </section>
                    <section class="modal-section">
                        <h3><i class="fas fa-book"></i> Books & Catalogs</h3>
                        <ul class="metric-list">${booksHtml}</ul>
                    </section>
                    <section class="modal-section">
                        <h3><i class="fas fa-exclamation-circle"></i> Supply Alert Breakdown</h3>
                        <ul class="metric-list">${alertsHtml}</ul>
                    </section>
                `;
            }
        },
        {
            id: 'device-inventory',
            title: 'Devices',
            icon: 'fas fa-print',
            description: 'Complete device inventory for the customer',
            group: 'Devices',
            defaultVisible: true,
            load: async (helpers, context) => {
                const data = await helpers.fetchJson('api/get-devices.php', {
                    customerCode: context.customerCode,
                    dealerCode: context.dealerCode,
                    dealerId: context.dealerId,
                    pageRows: 200,
                    sortColumn: 'AssetNumber',
                    sortOrder: 'Asc'
                });

                const devices = Array.isArray(data.devices) ? data.devices : [];
                const total = Number(data.total ?? devices.length ?? 0);
                const offline = devices.filter(device => device.IsOffline).length;

                return {
                    headline: {
                        value: total,
                        label: 'Devices'
                    },
                    metrics: [
                        {
                            label: 'Offline',
                            value: offline,
                            tone: offline > 0 ? 'danger' : 'success'
                        },
                        {
                            label: 'Models',
                            value: new Set(devices.map(device => device.Product?.Model || 'Unknown')).size
                        }
                    ],
                    context: { devices, total }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Customer Devices');
                helpers.renderTable(modal, {
                    columns: [
                        { id: 'AssetNumber', label: 'Asset #', sortable: true },
                        { id: 'ProductModel', label: 'Model', accessor: row => row.Product?.Model || 'Unknown', sortable: true },
                        { id: 'SerialNumber', label: 'Serial', sortable: true },
                        { id: 'IpAddress', label: 'IP Address' },
                        { id: 'OfficeDescription', label: 'Location', accessor: row => row.OfficeDescription || row.Note || '-' },
                        {
                            id: 'IsOffline',
                            label: 'Status',
                            sortable: true,
                            accessor: row => row.IsOffline ? 'Offline' : 'Online',
                            format: value => value === 'Offline'
                                ? '<span class="status-badge status-danger">Offline</span>'
                                : '<span class="status-badge status-success">Online</span>'
                        }
                    ],
                    rows: snapshot.context.devices,
                    pageSize: 50,
                    defaultSort: { column: 'AssetNumber', direction: 'asc' }
                });
            }
        },
        {
            id: 'supply-alerts',
            title: 'Supply Alerts',
            icon: 'fas fa-exclamation-triangle',
            description: 'Actionable supply alerts by priority',
            group: 'Alerts',
            defaultVisible: true,
            load: async (helpers, context) => {
                const data = await helpers.fetchJson('api/get-supply-alerts.php', {
                    customerCode: context.customerCode,
                    dealerCode: context.dealerCode,
                    pageRows: 100,
                    sortColumn: 'InitialDate',
                    sortOrder: 'Desc'
                });

                const payload = data.alerts ?? [];
                const alerts = Array.isArray(payload)
                    ? payload
                    : Array.isArray(payload.Items) ? payload.Items : [];

                const counts = alerts.reduce((acc, alert) => {
                    const level = Number(alert.Percentage ?? alert.ActualResidualPercentage ?? alert.InitialResidualPercentage ?? 0);
                    if (level <= 10) acc.critical += 1;
                    else if (level <= 25) acc.low += 1;
                    else acc.ok += 1;
                    return acc;
                }, { critical: 0, low: 0, ok: 0 });

                return {
                    headline: {
                        value: alerts.length,
                        label: 'Alerts'
                    },
                    metrics: [
                        { label: 'Critical', value: counts.critical, tone: counts.critical > 0 ? 'danger' : 'neutral' },
                        { label: 'Low', value: counts.low, tone: counts.low > 0 ? 'warning' : 'neutral' }
                    ],
                    context: { alerts }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Supply Alerts');
                helpers.renderTable(modal, {
                    columns: [
                        { id: 'SerialNumber', label: 'Device', sortable: true },
                        { id: 'ProductModel', label: 'Model', accessor: row => row.ProductModel || row.Product?.Model || 'Unknown', sortable: true },
                        { id: 'SupplyTypeDescription', label: 'Supply', accessor: row => row.SupplyTypeDescription || row.SupplyType || 'N/A' },
                        {
                            id: 'Level',
                            label: 'Level',
                            accessor: row => Number(row.Percentage ?? row.ActualResidualPercentage ?? row.InitialResidualPercentage ?? 0),
                            format: value => `<span class="badge ${value <= 10 ? 'badge-danger' : value <= 25 ? 'badge-warning' : 'badge-success'}">${value}%</span>`,
                            sortable: true
                        },
                        {
                            id: 'InitialDate',
                            label: 'Opened',
                            accessor: row => row.InitialDate ? new Date(row.InitialDate).toLocaleString() : 'N/A',
                            sortable: true
                        },
                        {
                            id: 'ManageOption',
                            label: 'Action',
                            accessor: row => row.ManageOption || row.InstallationOption || 'Monitor'
                        }
                    ],
                    rows: snapshot.context.alerts,
                    pageSize: 50,
                    defaultSort: { column: 'Level', direction: 'asc' }
                });
            }
        },
        {
            id: 'page-volume',
            title: 'Print Volume',
            icon: 'fas fa-chart-area',
            description: 'Monthly mono and color volumes',
            group: 'Analytics',
            defaultVisible: true,
            load: async (helpers, context) => {
                const data = await helpers.fetchJson('api/get-customer-pages.php', {
                    customerCode: context.customerCode
                });

                const pages = data.pages || {};

                return {
                    headline: {
                        value: pages.MonthlyMonoManaged ?? 0,
                        label: 'Mono Pages'
                    },
                    metrics: [
                        { label: 'Color Managed', value: pages.MonthlyColorManaged ?? 0 },
                        { label: 'Mono Unmanaged', value: pages.MonthlyMonoUnManaged ?? 0 },
                        { label: 'Color Unmanaged', value: pages.MonthlyColorUnManaged ?? 0 }
                    ],
                    context: { pages }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Print Volume Summary');
                const pages = snapshot.context.pages || {};
                modal.innerHTML = `
                    <div class="metric-grid">
                        <div class="metric-card">
                            <div class="metric-value">${helpers.formatNumber(pages.MonthlyMonoManaged ?? 0)}</div>
                            <div class="metric-label">Monthly Mono (Managed)</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">${helpers.formatNumber(pages.MonthlyColorManaged ?? 0)}</div>
                            <div class="metric-label">Monthly Color (Managed)</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">${helpers.formatNumber(pages.MonthlyMonoUnManaged ?? 0)}</div>
                            <div class="metric-label">Monthly Mono (Unmanaged)</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">${helpers.formatNumber(pages.MonthlyColorUnManaged ?? 0)}</div>
                            <div class="metric-label">Monthly Color (Unmanaged)</div>
                        </div>
                    </div>
                `;
            }
        },
        {
            id: 'integrations',
            title: 'Integrations',
            icon: 'fas fa-plug',
            description: 'Connected integrations for this customer',
            group: 'Integrations',
            defaultVisible: false,
            load: async (helpers, context) => {
                const data = await helpers.fetchJson('api/get-integrations.php', {
                    dealerCode: context.dealerCode,
                    customerCode: context.customerCode
                });

                const integrations = Array.isArray(data.integrations?.Items)
                    ? data.integrations.Items
                    : Array.isArray(data.integrations) ? data.integrations : [];

                return {
                    headline: {
                        value: integrations.length,
                        label: 'Integrations'
                    },
                    metrics: [
                        {
                            label: 'Active',
                            value: integrations.length,
                            tone: integrations.length ? 'success' : 'neutral'
                        }
                    ],
                    context: { integrations }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Integration Summary');
                helpers.renderTable(modal, {
                    columns: [
                        { id: 'Code', label: 'Code', sortable: true },
                        { id: 'Description', label: 'Description', sortable: true }
                    ],
                    rows: snapshot.context.integrations,
                    pageSize: 20,
                    defaultSort: { column: 'Description', direction: 'asc' }
                });
            }
        },
        {
            id: 'api-clients',
            title: 'API Clients',
            icon: 'fas fa-key',
            description: 'OAuth clients configured for the dealer',
            group: 'Administration',
            defaultVisible: false,
            load: async (helpers, context) => {
                const data = await helpers.fetchJson('api/get-api-clients.php', {
                    dealerCode: context.dealerCode,
                    pageRows: 100,
                    sortColumn: 'Description',
                    sortOrder: 'Asc'
                });

                const clients = Array.isArray(data.clients?.Items)
                    ? data.clients.Items
                    : Array.isArray(data.clients) ? data.clients : [];

                const active = clients.filter(client => client.Enabled !== false).length;

                return {
                    headline: {
                        value: clients.length,
                        label: 'API Clients'
                    },
                    metrics: [
                        { label: 'Active', value: active, tone: active ? 'success' : 'neutral' }
                    ],
                    context: { clients }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('API Clients');
                helpers.renderTable(modal, {
                    columns: [
                        { id: 'Description', label: 'Name', sortable: true },
                        { id: 'ClientId', label: 'Client ID', sortable: true },
                        {
                            id: 'Enabled',
                            label: 'Status',
                            accessor: row => row.Enabled !== false ? 'Active' : 'Disabled',
                            format: value => value === 'Active'
                                ? '<span class="status-badge status-success">Active</span>'
                                : '<span class="status-badge status-muted">Disabled</span>'
                        },
                        { id: 'LastAccessDate', label: 'Last Access', accessor: row => row.LastAccessDate ? new Date(row.LastAccessDate).toLocaleString() : 'N/A' }
                    ],
                    rows: snapshot.context.clients,
                    pageSize: 25,
                    defaultSort: { column: 'Description', direction: 'asc' }
                });
            }
        },
        {
            id: 'dealer-supplies',
            title: 'Dealer Supplies',
            icon: 'fas fa-box',
            description: 'Catalog supplies available for fulfillment',
            group: 'Supplies',
            defaultVisible: false,
            load: async (helpers, context) => {
                const data = await helpers.fetchJson('api/get-dealer-supplies.php', {
                    dealerCode: context.dealerCode,
                    pageRows: 100,
                    sortColumn: 'Description',
                    sortOrder: 'Asc'
                });

                const supplies = Array.isArray(data.supplies?.Items)
                    ? data.supplies.Items
                    : Array.isArray(data.supplies) ? data.supplies : [];

                const toners = supplies.filter(item => item.SupplyTypeDescription === 'Toner').length;

                return {
                    headline: {
                        value: supplies.length,
                        label: 'SKUs'
                    },
                    metrics: [
                        { label: 'Toners', value: toners },
                        { label: 'Maintenance', value: supplies.length - toners }
                    ],
                    context: { supplies }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Dealer Supplies');
                helpers.renderTable(modal, {
                    columns: [
                        { id: 'Code', label: 'Code', sortable: true },
                        { id: 'Description', label: 'Description', sortable: true },
                        { id: 'SupplyTypeDescription', label: 'Type', sortable: true },
                        { id: 'Brand', label: 'Brand', sortable: true },
                        { id: 'ColorTypeDescription', label: 'Color' }
                    ],
                    rows: snapshot.context.supplies,
                    pageSize: 25,
                    defaultSort: { column: 'Description', direction: 'asc' }
                });
            }
        }
    ];

    function getAll() {
        return definitions.slice();
    }

    return {
        getAll
    };
})();

