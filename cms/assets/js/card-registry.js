/**
 * CardRegistry - authoritative list of dashboard cards.
 * Each card definition describes how to fetch snapshot data and render modal drill-downs.
 */

const CardRegistry = (function () {
    'use strict';

    const formatDate = (value, options = { dateStyle: 'short', timeStyle: 'short' }) => {
        if (window.MPSM && typeof window.MPSM.formatDateTime === 'function') {
            return window.MPSM.formatDateTime(value, options);
        }
        if (!value) {
            return 'N/A';
        }
        const date = value instanceof Date ? value : new Date(value);
        if (Number.isNaN(date.getTime())) {
            return 'N/A';
        }
        return date.toLocaleString('en-US', options);
    };

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
                let devices = [];
                let total = 0;

                if (window.MPSM && typeof window.MPSM.fetchAllDevices === 'function') {
                    const result = await window.MPSM.fetchAllDevices({
                        customerCode: context.customerCode,
                        dealerCode: context.dealerCode,
                        dealerId: context.dealerId,
                        sortColumn: 'AssetNumber',
                        sortOrder: 'Asc'
                    });
                    devices = Array.isArray(result.devices) ? result.devices : [];
                    total = Number(result.total ?? devices.length ?? 0);

                    if (typeof window.MPSM.hydrateDeviceLookup === 'function') {
                        window.MPSM.hydrateDeviceLookup(devices);
                    }
                } else {
                    const data = await helpers.fetchJson('api/get-devices.php', {
                        customerCode: context.customerCode,
                        dealerCode: context.dealerCode,
                        dealerId: context.dealerId,
                        pageRows: 200,
                        sortColumn: 'AssetNumber',
                        sortOrder: 'Asc'
                    });

                    devices = Array.isArray(data.devices) ? data.devices : [];
                    total = Number(data.total ?? devices.length ?? 0);
                }

                if (!Number.isFinite(total)) {
                    total = devices.length;
                }

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
                        { id: 'EquipmentId', label: 'Equipment ID', accessor: row => resolveEquipmentId(row), sortable: true },
                        { id: 'AssetNumber', label: 'Asset #', accessor: row => row.AssetNumber || '', hidden: true },
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
                    defaultSort: { column: 'EquipmentId', direction: 'asc' },
                    onRowClick: row => {
                        if (row && row.Id && window.MPSM && typeof window.MPSM.openDeviceModal === 'function') {
                            window.MPSM.openDeviceModal(row.Id);
                        }
                    }
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
                let alerts = [];
                let totalAlerts = 0;
                if (window.MPSM && typeof window.MPSM.fetchAllSupplyAlerts === 'function') {
                    const result = await window.MPSM.fetchAllSupplyAlerts({
                        customerCode: context.customerCode,
                        dealerCode: context.dealerCode,
                        pageRows: 500,
                        sortColumn: 'InitialDate',
                        sortOrder: 'Desc'
                    });
                    alerts = Array.isArray(result.alerts) ? result.alerts : [];
                    totalAlerts = Number(result.total ?? alerts.length ?? 0);
                } else {
                    const data = await helpers.fetchJson('api/get-supply-alerts.php', {
                        customerCode: context.customerCode,
                        dealerCode: context.dealerCode,
                        pageRows: 500,
                        sortColumn: 'InitialDate',
                        sortOrder: 'Desc'
                    });
                    const meta = data.meta || {};
                    const payload = data.alerts ?? [];
                    alerts = Array.isArray(payload)
                        ? payload
                        : Array.isArray(payload.Items) ? payload.Items : [];
                    totalAlerts = Number(
                        meta.total_rows
                        ?? meta.total_count
                        ?? meta.total
                        ?? data.total
                        ?? alerts.length
                        ?? 0
                    );
                }

                const summarize = (alertList) => {
                    if (window.computeAlertDeviceSummary) {
                        return window.computeAlertDeviceSummary(alertList);
                    }
                    const deviceMap = new Map();
                    alertList.forEach(alert => {
                        const key = window.resolveAlertDeviceKey ? window.resolveAlertDeviceKey(alert) : (alert.DeviceId || alert.IdInstalledProduct || alert.SerialNumber || alert.AssetNumber || null);
                        if (!key) return;
                        const level = Number(alert.Percentage ?? alert.ActualResidualPercentage ?? alert.InitialResidualPercentage ?? 0);
                        const normalized = Number.isFinite(level) ? level : 100;
                        const current = deviceMap.get(key);
                        if (!current || normalized < current) {
                            deviceMap.set(key, normalized);
                        }
                    });
                    let critical = 0;
                    let low = 0;
                    let ok = 0;
                    deviceMap.forEach(level => {
                        if (level <= 10) critical += 1;
                        else if (level <= 25) low += 1;
                        else ok += 1;
                    });
                    return {
                        totalDevices: deviceMap.size,
                        critical,
                        low,
                        ok,
                        deviceMap
                    };
                };

                const summary = summarize(alerts);

                return {
                    headline: {
                        value: summary.totalDevices,
                        label: 'Devices with Alerts'
                    },
                    metrics: [
                        { label: 'Critical Devices', value: summary.critical, tone: summary.critical > 0 ? 'danger' : 'neutral' },
                        { label: 'Low Devices', value: summary.low, tone: summary.low > 0 ? 'warning' : 'neutral' },
                        { label: 'Total Alerts', value: totalAlerts }
                    ],
                    context: {
                        alerts,
                        total: totalAlerts,
                        summary
                    }
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
                        { id: 'EquipmentId', label: 'Equipment ID', accessor: row => resolveEquipmentId(row), sortable: true },
                        { id: 'SerialNumber', label: 'Serial', accessor: row => row.SerialNumber || row.DeviceSerialNumber || 'N/A', sortable: true },
                        { id: 'ProductModel', label: 'Model', accessor: row => row.ProductModel || row.Product?.Model || 'Unknown', sortable: true },
                        { id: 'SupplyTypeDescription', label: 'Supply', accessor: row => row.SupplyTypeDescription || row.SupplyType || 'N/A', sortable: true },
                        {
                            id: 'Level',
                            label: 'Level',
                            accessor: row => Number(row.Percentage ?? row.ActualResidualPercentage ?? row.InitialResidualPercentage ?? 0),
                            format: (value, row) => {
                                const level = Number(value);
                                if (!Number.isFinite(level)) {
                                    return '--';
                                }
                                const colorResolver = (typeof resolveSupplyColor === 'function')
                                    ? resolveSupplyColor
                                    : value => {
                                        const text = (value ?? '').toString().toLowerCase();
                                        if (text.includes('cyan')) return 'cyan';
                                        if (text.includes('magenta')) return 'magenta';
                                        if (text.includes('yellow')) return 'yellow';
                                        if (text.includes('black') || text.includes('mono')) return 'black';
                                        return 'neutral';
                                    };
                                const supplyColor = colorResolver(row.SupplyTypeDescription || row.SupplyType || '');
                                return renderTonerBadge(supplyColor, level);
                            },
                            sortable: true
                        },
                        {
                            id: 'InitialDate',
                            label: 'Opened',
                            accessor: row => row.InitialDate ? new Date(row.InitialDate).getTime() : 0,
                            sortable: true,
                            format: (value, row) => formatDate(row.InitialDate)
                        },
                        {
                            id: 'ManageOption',
                            label: 'Action',
                            accessor: row => row.ManageOption || row.InstallationOption || 'Monitor'
                        }
                    ],
                    rows: alerts,
                    pageSize: 50,
                    defaultSort: { column: 'EquipmentId', direction: 'asc' },
                    onRowClick: row => {
                        if (!window.MPSM || typeof window.MPSM.openDeviceModal !== 'function') {
                            return;
                        }
                        const resolver = typeof window.findDeviceIdForAlert === 'function'
                            ? window.findDeviceIdForAlert
                            : (alert) => alert?.DeviceId ?? alert?.IdInstalledProduct ?? alert?.IdDevice ?? null;
                        const deviceId = resolver(row);
                        if (deviceId) {
                            window.MPSM.openDeviceModal(deviceId);
                        } else if (typeof window.MPSM.showToast === 'function') {
                            window.MPSM.showToast('Device details are not available for this alert yet.', 'info');
                        }
                    }
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
            id: 'top-devices',
            title: 'Top Devices',
            icon: 'fas fa-trophy',
            description: 'Top 5 devices by monthly page volume',
            group: 'Analytics',
            defaultVisible: true,
            load: async (helpers, context) => {
                let devices = [];

                if (window.MPSM && typeof window.MPSM.fetchAllDevices === 'function') {
                    const result = await window.MPSM.fetchAllDevices({
                        customerCode: context.customerCode,
                        dealerCode: context.dealerCode,
                        dealerId: context.dealerId,
                        sortColumn: 'AssetNumber',
                        sortOrder: 'Asc'
                    });
                    devices = Array.isArray(result.devices) ? result.devices : [];
                } else {
                    const data = await helpers.fetchJson('api/get-devices.php', {
                        customerCode: context.customerCode,
                        dealerCode: context.dealerCode,
                        dealerId: context.dealerId,
                        pageRows: 200,
                        sortColumn: 'AssetNumber',
                        sortOrder: 'Asc'
                    });
                    devices = Array.isArray(data.devices) ? data.devices : [];
                }

                const enriched = devices.map(device => {
                    const monoCandidates = [
                        device.MonthlyMonoVolume,
                        device.MonthlyMonoPages,
                        device.MonthlyMono,
                        device.CounterMonoMonthly,
                        device.CounterMonoDelta
                    ];
                    const colorCandidates = [
                        device.MonthlyColorVolume,
                        device.MonthlyColorPages,
                        device.MonthlyColor,
                        device.CounterColorMonthly,
                        device.CounterColorDelta
                    ];

                    const resolvedMono = monoCandidates
                        .map(value => Number(value) || 0)
                        .find(value => value > 0) ?? 0;
                    const resolvedColor = colorCandidates
                        .map(value => Number(value) || 0)
                        .find(value => value > 0) ?? 0;

                    const fallbackMono = Number(device.CounterMono ?? 0);
                    const fallbackColor = Number(device.CounterColor ?? 0);

                    const mono = resolvedMono || fallbackMono;
                    const color = resolvedColor || fallbackColor;
                    const total = mono + color;

                    return Object.assign({}, device, {
                        __topMono: mono,
                        __topColor: color,
                        __topTotal: total
                    });
                });

                const sorted = enriched
                    .slice()
                    .sort((a, b) => (Number(b.__topTotal) || 0) - (Number(a.__topTotal) || 0));

                const topDevices = sorted.slice(0, 5);
                const totalVolume = topDevices.reduce((sum, device) => sum + (Number(device.__topTotal) || 0), 0);
                const averageVolume = topDevices.length ? totalVolume / topDevices.length : 0;
                const offlineCount = topDevices.filter(device => device.IsOffline).length;
                const colorCapable = topDevices.filter(device => {
                    const flag = device.IsColor ?? device.ColorCapable ?? device.Product?.IsColor ?? device.Product?.ColorCapable;
                    if (typeof flag === 'string') {
                        const normalized = flag.toLowerCase();
                        return normalized === 'yes' || normalized === 'true' || normalized === 'color';
                    }
                    return Boolean(flag);
                }).length;

                return {
                    headline: {
                        value: Math.round(totalVolume),
                        label: 'Monthly Pages (Top 5)'
                    },
                    metrics: [
                        { label: 'Avg / Device', value: Math.round(averageVolume) },
                        { label: 'Offline', value: offlineCount, tone: offlineCount ? 'danger' : 'success' },
                        { label: 'Color Devices', value: colorCapable }
                    ],
                    context: { devices: topDevices }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Top 5 Devices by Volume');
                const devices = snapshot.context.devices || [];

                if (!devices.length) {
                    modal.innerHTML = '<div class="empty-state"><i class="fas fa-info-circle"></i><p>No device volume data is available.</p></div>';
                    return;
                }

                const formatNumber = (value) => helpers.formatNumber(Number(value) || 0);

                helpers.renderTable(modal, {
                    columns: [
                        { id: 'EquipmentId', label: 'Equipment ID', accessor: row => resolveEquipmentId(row), sortable: true },
                        { id: 'ProductModel', label: 'Model', accessor: row => row.Product?.Model || 'Unknown', sortable: true },
                        { id: 'OfficeDescription', label: 'Location', accessor: row => row.OfficeDescription || row.Note || '-' },
                        {
                            id: 'MonoVolume',
                            label: 'Monthly Mono',
                            accessor: row => Number(row.__topMono) || 0,
                            sortable: true,
                            format: value => formatNumber(value)
                        },
                        {
                            id: 'ColorVolume',
                            label: 'Monthly Color',
                            accessor: row => Number(row.__topColor) || 0,
                            sortable: true,
                            format: value => formatNumber(value)
                        },
                        {
                            id: 'TotalVolume',
                            label: 'Monthly Total',
                            accessor: row => Number(row.__topTotal) || 0,
                            sortable: true,
                            format: value => formatNumber(value)
                        }
                    ],
                    rows: devices,
                    pageSize: 5,
                    defaultSort: { column: 'TotalVolume', direction: 'desc' },
                    onRowClick: row => {
                        if (!window.MPSM || typeof window.MPSM.openDeviceModal !== 'function') {
                            return;
                        }
                        const rowId = row?.Id ?? row?.IdInstalledProduct ?? row?.DeviceId ?? null;
                        if (rowId) {
                            window.MPSM.openDeviceModal(rowId);
                        } else if (typeof window.MPSM.showToast === 'function') {
                            window.MPSM.showToast('Device details are not available for this record yet.', 'info');
                        }
                    }
                });
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
            id: 'export-library',
            title: 'Export Library',
            icon: 'fas fa-file-export',
            description: 'Downloadable reports and meter exports',
            group: 'Reports',
            defaultVisible: false,
            load: async (helpers, context) => {
                const data = await helpers.fetchJson('api/get-export-endpoints.php', {});
                const exports = Array.isArray(data.exports) ? data.exports : [];
                const working = exports.filter(entry => entry.success === true).length;
                const excelCount = exports.filter(entry => (entry.format || '').toLowerCase() === 'excel').length;
                const csvCount = exports.filter(entry => (entry.format || '').toLowerCase() === 'csv').length;

                return {
                    headline: {
                        value: exports.length,
                        label: 'Exportable Reports'
                    },
                    metrics: [
                        {
                            label: 'Working',
                            value: working,
                            tone: working === exports.length ? 'success' : (working === 0 ? 'danger' : 'warning')
                        },
                        { label: 'Excel', value: excelCount || 0 },
                        { label: 'CSV', value: csvCount || 0 }
                    ],
                    context: { exports }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Export Library');
                const exports = snapshot.context.exports || [];

                if (!exports.length) {
                    modal.innerHTML = '<div class="empty-state"><i class="fas fa-info-circle"></i><p>No exportable endpoints are currently available.</p></div>';
                    return;
                }

                const tableContainer = document.createElement('div');
                modal.appendChild(tableContainer);

                const escapeHtml = helpers.escape;
                const formatNumber = helpers.formatNumber;

                const columns = [
                    {
                        id: 'action',
                        label: 'Endpoint',
                        sortable: true,
                        format: value => {
                            const safeText = escapeHtml(value || 'Unknown');
                            return (value || '') === 'Counter/Device/Export'
                                ? `<strong>${safeText}</strong>`
                                : safeText;
                        }
                    },
                    { id: 'category', label: 'Category', accessor: row => row.category || 'N/A', sortable: true },
                    {
                        id: 'status',
                        label: 'Status',
                        accessor: row => {
                            if (row.runtimeStatus) {
                                return row.runtimeStatus;
                            }
                            if (row.success === null || row.success === undefined) {
                                return 'Untested';
                            }
                            return row.success ? 'Pass' : 'Fail';
                        },
                        sortable: true,
                        format: (value, row) => {
                            const status = (row.runtimeStatus ?? value ?? 'Untested').toString();
                            const normalized = status.toLowerCase();
                            let tone = 'muted';
                            if (normalized === 'pass' || normalized === 'success') {
                                tone = 'success';
                            } else if (normalized === 'fail' || normalized === 'error') {
                                tone = 'danger';
                            }
                            const title = row.runtimeError ? ` title="${helpers.escape(row.runtimeError)}"` : '';
                            return `<span class="status-badge status-${tone}"${title}>${helpers.escape(status)}</span>`;
                        }
                    },
                    {
                        id: 'runtimeAttempts',
                        label: 'Attempts',
                        accessor: row => row.runtimeAttempts ?? '',
                        sortable: true,
                        format: value => value === '' ? '--' : formatNumber(Number(value) || 0)
                    },
                    {
                        id: 'download',
                        label: 'Download',
                        accessor: row => row.action,
                        format: (value, row) => {
                            const safeAction = helpers.escape(row.action);
                            return `<button type="button" class="btn btn-primary btn-sm export-download" data-export-action="${safeAction}"><i class="fas fa-download"></i> Download</button>`;
                        }
                    }
                ];

                const exportTable = helpers.renderTable(tableContainer, {
                    columns,
                    rows: exports,
                    pageSize: 25,
                    defaultSort: { column: 'action', direction: 'asc' }
                });

                const baseParams = {};
                if (context.dealerCode) baseParams.dealerCode = context.dealerCode;
                if (context.dealerId) baseParams.dealerId = context.dealerId;
                if (context.customerCode) baseParams.customerCode = context.customerCode;

                const base64ToBlob = (base64, contentType) => {
                    const binary = atob(base64);
                    const len = binary.length;
                    const bytes = new Uint8Array(len);
                    for (let i = 0; i < len; i++) {
                        bytes[i] = binary.charCodeAt(i);
                    }
                    return new Blob([bytes], { type: contentType || 'application/octet-stream' });
                };

                tableContainer.addEventListener('click', async (event) => {
                    const button = event.target.closest('button.export-download');
                    if (!button) {
                        return;
                    }

                    const actionName = button.dataset.exportAction;
                    const exportRow = exports.find(entry => entry.action === actionName);
                    if (!exportRow) {
                        showToast('Export definition not found in snapshot.', 'error');
                        return;
                    }

                    const prerequisites = Array.isArray(exportRow.prerequisites) ? exportRow.prerequisites : [];
                    const missing = [];
                    if (prerequisites.includes('customerCode') && !baseParams.customerCode) {
                        missing.push('customerCode');
                    }
                    if (prerequisites.includes('dealerCode') && !baseParams.dealerCode) {
                        missing.push('dealerCode');
                    }

                    if (missing.length) {
                        showToast(`Missing required context: ${missing.join(', ')}`, 'warning');
                        return;
                    }

                    const params = Object.assign({}, baseParams);

                    if (actionName === 'Counter/Device/Export') {
                        const now = new Date();
                        const from = new Date(now.getTime());
                        from.setDate(from.getDate() - 30);

                        params.fromDate = params.fromDate || from.toISOString();
                        params.toDate = params.toDate || now.toISOString();
                        if (params.exportToCsv === undefined) {
                            params.exportToCsv = false;
                        }

                        if (!params.id) {
                            const resolver = window.MPSM && typeof window.MPSM.resolveDeviceIdForExports === 'function'
                                ? window.MPSM.resolveDeviceIdForExports
                                : null;
                            const deviceId = resolver ? await resolver() : null;
                            if (!deviceId) {
                                throw new Error('Unable to resolve a device for Counter/Device/Export.');
                            }
                            params.id = deviceId;
                        }
                    }

                    const payload = {
                        action: actionName,
                        params
                    };

                    const originalHtml = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    try {
                        const response = await fetch('api/run-export.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();
                        if (!result.success) {
                            throw new Error(result.error || 'Export failed');
                        }

                        if (result.file && result.file.data) {
                            const blob = base64ToBlob(result.file.data, result.file.content_type);
                            const objectUrl = URL.createObjectURL(blob);
                            const safeBase = actionName.replace(/[^a-z0-9_\-]+/gi, '_').replace(/_+/g, '_').replace(/^_|_$/g, '').toLowerCase() || 'export';
                            const contentType = (result.file.content_type || '').toLowerCase();
                            let filename = result.file.name;

                            if (!filename || typeof filename !== 'string') {
                                if (contentType.includes('csv')) {
                                    filename = `${safeBase}.csv`;
                                } else if (contentType.includes('excel') || contentType.includes('spreadsheet') || contentType.includes('sheet')) {
                                    filename = `${safeBase}.xlsx`;
                                } else if (contentType.includes('pdf')) {
                                    filename = `${safeBase}.pdf`;
                                } else if (contentType.includes('json')) {
                                    filename = `${safeBase}.json`;
                                } else if (contentType.includes('xml')) {
                                    filename = `${safeBase}.xml`;
                                } else if (contentType.includes('zip')) {
                                    filename = `${safeBase}.zip`;
                                } else {
                                    filename = `${safeBase}.bin`;
                                }
                            }

                            const link = document.createElement('a');
                            link.href = objectUrl;
                            link.download = filename;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            setTimeout(() => URL.revokeObjectURL(objectUrl), 2000);
                            showToast(`Export downloaded: ${filename}. Check your browser downloads folder.`, 'success');
                            exportRow.runtimeStatus = 'Pass';
                            exportRow.runtimeError = null;
                            exportRow.runtimeAttempts = (exportRow.runtimeAttempts ?? 0) + 1;
                            exportRow.runtimeDuration = result.duration_ms ?? null;
                            exportRow.runtimeParams = result.params_used ?? params;
                            exportRow.runtimeTestedAt = new Date().toISOString();
                            exportRow.success = true;
                        } else if (result.file && result.file.url) {
                            window.open(result.file.url, '_blank');
                            showToast('Export opened in a new tab.', 'info');
                            exportRow.runtimeStatus = 'Pass';
                            exportRow.runtimeError = null;
                            exportRow.runtimeAttempts = (exportRow.runtimeAttempts ?? 0) + 1;
                            exportRow.runtimeDuration = result.duration_ms ?? null;
                            exportRow.runtimeParams = result.params_used ?? params;
                            exportRow.runtimeTestedAt = new Date().toISOString();
                            exportRow.success = true;
                        } else {
                            console.log('Export response payload', result);
                            showToast('Export returned structured data. See console for details.', 'info');
                            exportRow.runtimeStatus = 'Pass';
                            exportRow.runtimeError = null;
                            exportRow.runtimeAttempts = (exportRow.runtimeAttempts ?? 0) + 1;
                            exportRow.runtimeDuration = result.duration_ms ?? null;
                            exportRow.runtimeParams = result.params_used ?? null;
                            exportRow.runtimeTestedAt = new Date().toISOString();
                            exportRow.success = true;
                        }
                        exportTable.updateRows(exports);
                    } catch (error) {
                        showToast('Export failed: ' + error.message, 'error');
                        exportRow.runtimeStatus = 'Fail';
                        exportRow.runtimeError = error.message;
                        exportRow.runtimeAttempts = (exportRow.runtimeAttempts ?? 0) + 1;
                        exportRow.runtimeParams = params;
                        exportRow.runtimeTestedAt = new Date().toISOString();
                        exportRow.success = false;
                        exportTable.updateRows(exports);
                    } finally {
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                    }
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

function resolveEquipmentIdFromParts(asset, external, fallback) {
    const a = (asset ?? '').toString().trim();
    const e = (external ?? '').toString().trim();
    const f = (fallback ?? '').toString().trim();

    if (!a && !e) {
        return f || 'N/A';
    }

    if (!a) {
        return e;
    }

    if (!e) {
        return a;
    }

    if (a.toLowerCase() === e.toLowerCase()) {
        return a;
    }

    return `${a} / ${e}`;
}

function resolveEquipmentId(entity) {
    if (!entity || typeof entity !== 'object') {
        return 'N/A';
    }

    const direct =
        entity.EquipmentId
        ?? entity.EquipmentID
        ?? entity.DeviceEquipmentId
        ?? entity.DeviceEquipmentID
        ?? entity.InstalledProductEquipmentId
        ?? entity.DeviceKey
        ?? entity.IdDevice
        ?? '';

    const asset = entity.AssetNumber ?? entity.Asset ?? direct ?? '';
    const external = entity.ExternalIdentifier ?? entity.ExternalId ?? '';
    const fallback = direct
        || entity.SerialNumber
        || entity.DeviceSerialNumber
        || entity.SystemName
        || '';

    return resolveEquipmentIdFromParts(asset, external, fallback);
}

if (typeof window !== 'undefined') {
    window.resolveEquipmentId = window.resolveEquipmentId || resolveEquipmentId;
    window.resolveEquipmentIdFromParts = window.resolveEquipmentIdFromParts || resolveEquipmentIdFromParts;
}

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
    const normalizedColor = ['black', 'cyan', 'magenta', 'yellow'].includes(color)
        ? color
        : (color === 'neutral' ? 'neutral' : 'unknown');
    const colorClass = `toner-chip--${normalizedColor}`;
    const severityClass = percentage <= 10 ? 'toner-chip--critical'
        : percentage <= 25 ? 'toner-chip--low'
        : 'toner-chip--ok';

    return `
        <span class="toner-chip ${colorClass} ${severityClass}">
            <span class="toner-chip__fill" style="width:${percentage}%"></span>
            <span class="toner-chip__label">${percentage}%</span>
        </span>
    `;
}



