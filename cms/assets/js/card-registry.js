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
            id: 'connectors',
            title: 'Connectors',
            icon: 'fas fa-network-wired',
            description: 'Windows and SDS connector activity',
            group: 'Summary',
            defaultVisible: true,
            load: async (helpers, context) => {
                const data = await helpers.fetchJson('api/get-connectors.php', {
                    customerCode: context.customerCode
                });

                const summary = data.connectors || {};
                const total = Number(summary.TotalWin ?? 0) + Number(summary.TotalEmbedded ?? 0);
                const lastDay = Number(summary.LastDay ?? 0);
                const sdsActive = Number(summary.SdsTotalWin ?? 0) > 0;
                const online = total > 0 && (lastDay > 0 || sdsActive);

                return {
                    headline: {
                        value: total,
                        label: 'Connectors'
                    },
                    metrics: [
                        {
                            label: 'Status',
                            value: online ? 'Online' : 'Offline',
                            tone: online ? 'success' : 'danger'
                        },
                        {
                            label: 'Active (24h)',
                            value: lastDay
                        }
                    ],
                    context: { summary, online }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Connector Activity');
                const summary = snapshot.context.summary || {};

                modal.innerHTML = `
                    <table class="table">
                        <tbody>
                            <tr><th>Total Connectors</th><td>${helpers.formatNumber(Number(summary.TotalWin ?? 0) + Number(summary.TotalEmbedded ?? 0))}</td></tr>
                            <tr><th>SDS Connectors</th><td>${helpers.formatNumber(summary.SdsTotalWin ?? 0)}</td></tr>
                            <tr><th>Active in last 24h</th><td>${helpers.formatNumber(summary.LastDay ?? 0)}</td></tr>
                            <tr><th>Active last period</th><td>${helpers.formatNumber(summary.LastPeriod ?? 0)}</td></tr>
                            <tr><th>Offline over period</th><td>${helpers.formatNumber(summary.OverPeriod ?? 0)}</td></tr>
                        </tbody>
                    </table>
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
                        },
                        {
                            id: 'BlackToner',
                            label: 'K',
                            accessor: row => resolveTonerValue(row, ['BlackToner', 'BlackToner1', 'BlackToner2', 'BlackToner3']),
                            format: value => renderTonerBadge('black', value)
                        },
                        {
                            id: 'CyanToner',
                            label: 'C',
                            accessor: row => resolveTonerValue(row, ['CyanToner']),
                            format: value => renderTonerBadge('cyan', value)
                        },
                        {
                            id: 'MagentaToner',
                            label: 'M',
                            accessor: row => resolveTonerValue(row, ['MagentaToner']),
                            format: value => renderTonerBadge('magenta', value)
                        },
                        {
                            id: 'YellowToner',
                            label: 'Y',
                            accessor: row => resolveTonerValue(row, ['YellowToner']),
                            format: value => renderTonerBadge('yellow', value)
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
                const alerts = snapshot.context.alerts || [];
                const supplyTypes = Array.from(new Set(alerts.map(alert => alert.SupplyTypeDescription || alert.SupplyType || 'Other')));
                const activeTypes = new Set(supplyTypes);

                const filterBar = document.createElement('div');
                filterBar.className = 'table-filters';
                filterBar.innerHTML = '<span class="filter-label"><i class="fas fa-filter"></i> Supply Types</span>';

                supplyTypes.forEach(type => {
                    const label = document.createElement('label');
                    label.className = 'filter-checkbox';
                    label.innerHTML = `
                        <input type="checkbox" value="${helpers.escape(type)}" checked>
                        <span>${helpers.escape(type)}</span>
                    `;
                    const checkbox = label.querySelector('input');
                    checkbox.addEventListener('change', () => {
                        if (checkbox.checked) {
                            activeTypes.add(type);
                        } else {
                            activeTypes.delete(type);
                        }

                        if (!activeTypes.size) {
                            activeTypes.add(type);
                            checkbox.checked = true;
                            showToast('At least one supply type must remain visible', 'warning');
                            return;
                        }

                        const filtered = alerts.filter(alert => activeTypes.has(alert.SupplyTypeDescription || alert.SupplyType || 'Other'));
                        tableHandle.updateRows(filtered);
                    });
                    filterBar.appendChild(label);
                });

                modal.appendChild(filterBar);

                const tableContainer = document.createElement('div');
                modal.appendChild(tableContainer);

                const tableHandle = helpers.renderTable(tableContainer, {
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
                    rows: alerts,
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

                const stats = integrations.reduce((acc, entry) => {
                    const code = (entry.Code || '').toLowerCase();
                    const value = Number(entry.Description ?? 0);
                    if (code === 'joined') {
                        acc.joined = value;
                    } else if (code === 'unjoined') {
                        acc.unjoined = value;
                    } else {
                        acc.other += value;
                    }
                    return acc;
                }, { joined: 0, unjoined: 0, other: 0 });

                return {
                    headline: {
                        value: stats.joined,
                        label: 'Joined Customers'
                    },
                    metrics: [
                        {
                            label: 'Unjoined',
                            value: stats.unjoined,
                            tone: stats.unjoined ? 'warning' : 'success'
                        },
                        {
                            label: 'Total',
                            value: stats.joined + stats.unjoined + stats.other
                        }
                    ],
                    context: { integrations, stats }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Integration Summary');
                helpers.renderTable(modal, {
                    columns: [
                        { id: 'Code', label: 'Code', sortable: true },
                        {
                            id: 'Description',
                            label: 'Count',
                            sortable: true,
                            format: value => helpers.formatNumber(Number(value ?? 0))
                        }
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

                const active = clients.filter(client => client.IsActive !== false).length;

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
                        { id: 'Name', label: 'Name', sortable: true },
                        { id: 'AppId', label: 'Client ID', sortable: true },
                        {
                            id: 'IsActive',
                            label: 'Status',
                            accessor: row => row.IsActive !== false ? 'Active' : 'Disabled',
                            format: value => value === 'Active'
                                ? '<span class="status-badge status-success">Active</span>'
                                : '<span class="status-badge status-muted">Disabled</span>'
                        },
                        { id: 'DeveloperEmail', label: 'Developer Email', sortable: true }
                    ],
                    rows: snapshot.context.clients,
                    pageSize: 25,
                    defaultSort: { column: 'Name', direction: 'asc' }
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

                const counts = supplies.reduce((acc, item) => {
                    const type = item.SupplyType;
                    if (type === 3) acc.toner += 1;
                    else if (type === 1) acc.maintenance += 1;
                    else acc.other += 1;
                    return acc;
                }, { toner: 0, maintenance: 0, other: 0 });

                return {
                    headline: {
                        value: supplies.length,
                        label: 'SKUs'
                    },
                    metrics: [
                        { label: 'Toner', value: counts.toner },
                        { label: 'Maintenance', value: counts.maintenance },
                        { label: 'Other', value: counts.other }
                    ],
                    context: { supplies }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Dealer Supplies');
                const supplyType = {
                    1: 'Maintenance',
                    2: 'Photo Conductor',
                    3: 'Toner'
                };
                const colorType = {
                    1: 'Color',
                    2: 'Mono',
                    3: 'Mono 2',
                    4: 'Mono 3'
                };

                helpers.renderTable(modal, {
                    columns: [
                        { id: 'PartNumber', label: 'Part Number', sortable: true },
                        { id: 'Description', label: 'Description', sortable: true },
                        {
                            id: 'SupplyType',
                            label: 'Type',
                            accessor: row => supplyType[row.SupplyType] || 'Other'
                        },
                        {
                            id: 'ColorType',
                            label: 'Color',
                            accessor: row => colorType[row.ColorType] || 'N/A'
                        },
                        { id: 'Duration', label: 'Yield', sortable: true }
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

function resolveTonerValue(row, keys) {
    for (const key of keys) {
        const value = row[key];
        if (value !== undefined && value !== null) {
            return Number(value);
        }
    }
    return null;
}

function renderTonerBadge(color, value) {
    if (value === null || value === undefined || Number.isNaN(Number(value))) {
        return `
            <span class="toner-chip toner-chip--unknown">
                <span class="toner-chip__fill" style="width:0%"></span>
                <span class="toner-chip__label">--</span>
            </span>
        `;
    }

    const percentage = Math.max(0, Math.min(100, Number(value)));
    const colorClass = `toner-chip--${color}`;

    return `
        <span class="toner-chip ${colorClass}">
            <span class="toner-chip__fill" style="width:${percentage}%"></span>
            <span class="toner-chip__label">${percentage}%</span>
        </span>
    `;
}

