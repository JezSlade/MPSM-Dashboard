/**
 * CardRegistry - authoritative list of dashboard cards.
 * Each card definition describes how to fetch snapshot data and render modal drill-downs.
 */

const CardRegistry = (function () {
    'use strict';

    // Offline threshold: devices not reporting for 7+ days are considered offline
    const OFFLINE_THRESHOLD_DAYS = 7;

    /**
     * Determine if a device is offline based on IsOffline flag OR LastUpdate date
     * MPS API may not always populate IsOffline correctly, so we compute from LastUpdate as fallback
     */
    const isDeviceOffline = (device) => {
        // Guard against null/undefined devices
        if (!device) {
            return false;
        }

        // If API explicitly marks as offline, trust it
        if (device.IsOffline === true) {
            return true;
        }

        // Compute from LastUpdate if IsOffline is not set or false
        const lastUpdate = device.LastUpdate || device.LastCounterDate || device.LastMeterDate;
        if (!lastUpdate) {
            // No last update info - can't determine, assume online
            return false;
        }

        try {
            const lastDate = new Date(lastUpdate);
            const now = new Date();
            const daysSinceUpdate = (now - lastDate) / (1000 * 60 * 60 * 24);

            return daysSinceUpdate >= OFFLINE_THRESHOLD_DAYS;
        } catch (e) {
            return false;
        }
    };

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

    function resolveIp(device) {
        const raw = device?.IpAddress
            ?? device?.IPAddress
            ?? device?.ip_address
            ?? device?.IP
            ?? device?.Ip
            ?? '';
        const value = String(raw || '').trim();
        if (!value || value === '0.0.0.0') {
            return '';
        }
        return value;
    }

    function resolveSerial(device) {
        return (device?.SerialNumber
            ?? device?.DeviceSerialNumber
            ?? device?.Serial
            ?? device?.serial_number
            ?? '').toString().trim();
    }

    function resolveModel(device) {
        return (device?.Product?.Model
            ?? device?.ProductModel
            ?? device?.Model
            ?? device?.model_name
            ?? 'Unknown').toString().trim() || 'Unknown';
    }

    function resolveCustomer(device) {
        return (device?.CustomerDescription
            ?? device?.CustomerName
            ?? device?.Customer?.Description
            ?? device?.CustomerCode
            ?? device?.customer_code
            ?? 'Unknown').toString().trim() || 'Unknown';
    }

    function resolveLocation(device) {
        return (device?.OfficeDescription
            ?? device?.Location
            ?? device?.Note
            ?? device?.location
            ?? '—').toString().trim() || '—';
    }

    function resolveLastContact(device) {
        const candidates = [
            device?.LastContactDate,
            device?.LastContact,
            device?.LastUpdateDateTime,
            device?.last_seen,
            device?.LastUpdate
        ];
        const value = candidates.find(item => item);
        if (!value) return '';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? '' : date.toLocaleString();
    }

    function resolveDeviceIdForModal(device) {
        return device?.Id
            ?? device?.IdInstalledProduct
            ?? device?.DeviceId
            ?? device?.device_id
            ?? null;
    }

    function buildDuplicateIpGroups(devices) {
        if (!Array.isArray(devices)) {
            console.warn('[DupIP] devices is not an array:', typeof devices);
            return [];
        }

        console.log('[DupIP] Processing', devices.length, 'devices');

        // Debug: check what IP fields exist on first few devices
        if (devices.length > 0) {
            const sample = devices.slice(0, 3);
            sample.forEach((d, i) => {
                const ipFields = ['IpAddress', 'IPAddress', 'ip_address', 'IP', 'Ip'];
                const found = ipFields.filter(f => d && d[f]);
                console.log(`[DupIP] Device ${i} IP fields:`, found, 'values:', found.map(f => d[f]));
            });
        }

        const groups = new Map();
        let devicesWithIp = 0;
        devices.forEach(device => {
            const ip = resolveIp(device);
            if (!ip) return;
            devicesWithIp++;
            const key = ip.toLowerCase();
            if (!groups.has(key)) {
                groups.set(key, { ip, devices: [] });
            }
            groups.get(key).devices.push(device);
        });

        console.log('[DupIP] Devices with valid IP:', devicesWithIp, 'of', devices.length);
        console.log('[DupIP] Unique IPs:', groups.size);

        const duplicateGroups = Array.from(groups.values())
            .filter(group => group.devices.length > 1);

        console.log('[DupIP] Groups with 2+ devices (duplicates):', duplicateGroups.length);
        if (duplicateGroups.length > 0) {
            console.log('[DupIP] First duplicate group:', duplicateGroups[0].ip, 'has', duplicateGroups[0].devices.length, 'devices');
        }

        return duplicateGroups
            .map(group => {
                const sortedDevices = group.devices.slice().sort((a, b) => {
                    return resolveSerial(a).localeCompare(resolveSerial(b));
                });
                return {
                    ip: group.ip,
                    devices: sortedDevices,
                    hasOffline: sortedDevices.some(device => device?.IsOffline),
                    modelCount: new Set(sortedDevices.map(resolveModel).filter(Boolean)).size
                };
            })
            .sort((a, b) => {
                if (b.devices.length !== a.devices.length) {
                    return b.devices.length - a.devices.length;
                }
                return a.ip.localeCompare(b.ip);
            });
    }

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
                const sdsDashboard = dashboard.SdsDashboard || {};
                const contacted = Array.isArray(totalsSource.ContactedDevices) ? totalsSource.ContactedDevices : [];
                const books = Array.isArray(totalsSource.Books) ? totalsSource.Books : [];
                const supplyAlerts = Array.isArray(totalsSource.SupplyAlerts) ? totalsSource.SupplyAlerts : [];
                const toBeUpdated = Array.isArray(totalsSource.ToBeUpdated) ? totalsSource.ToBeUpdated : [];
                const warnings = Array.isArray(totalsSource.Warnings) ? totalsSource.Warnings : [];
                const suppliesDelivery = Array.isArray(totalsSource.SuppliesDelivery) ? totalsSource.SuppliesDelivery : [];

                const toManage = supplyAlerts.find(item => (item.Key || '').toLowerCase() === 'tomanage');

                // Extract duplicate IPs from warnings (more reliable than device fetch)
                const duplicateIps = warnings
                    .filter(w => w.Key === 'IP_Duplicated')
                    .map(w => w.Value)
                    .filter(Boolean);

                console.log('[DupIP] Found', duplicateIps.length, 'duplicate IPs from warnings');

                // Build simple groups from duplicate IPs
                const duplicateIpGroups = duplicateIps.map(ip => ({
                    ip,
                    devices: [], // Device details not available from warnings
                    deviceCount: 2, // Minimum for a duplicate
                    hasOffline: false,
                    modelCount: 0
                }));

                return {
                    headline: {
                        value: totalsSource.TotalManagedDevices ?? 0,
                        label: 'Devices'
                    },
                    metrics: [
                        { label: 'Connectors', value: totalsSource.TotalConnectors ?? 0 },
                        { label: 'Maintenance Alerts', value: Number(toManage?.Value ?? 0) },
                        { label: 'Enabled', value: totalsSource.EnabledDevicesByContract ?? 0 },
                        {
                            label: 'Duplicate IPs',
                            value: duplicateIpGroups.length,
                            tone: duplicateIpGroups.length ? 'warning' : 'success'
                        }
                    ],
                    context: {
                        totalsSource,
                        sdsDashboard,
                        contacted,
                        books,
                        supplyAlerts,
                        suppliesDelivery,
                        toBeUpdated,
                        warnings,
                        duplicateIpGroups
                    }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Customer Overview');
                const {
                    totalsSource = {},
                    sdsDashboard = {},
                    contacted = [],
                    books = [],
                    supplyAlerts = [],
                    suppliesDelivery = [],
                    toBeUpdated = [],
                    warnings = [],
                    duplicateIpGroups = []
                } = snapshot.context || {};

                const getByKey = (collection, key) => {
                    const match = collection.find(item => (item.Key || '').toLowerCase() === key);
                    return match ? helpers.formatNumber(match.Value ?? 0) : '0';
                };

                const metrics = [
                    { label: 'Managed Devices', icon: 'fa-print', value: helpers.formatNumber(totalsSource.TotalManagedDevices ?? 0) },
                    { label: 'Connectors', icon: 'fa-link', value: helpers.formatNumber(totalsSource.TotalConnectors ?? 0) },
                    { label: 'Enabled Devices', icon: 'fa-badge-check', value: helpers.formatNumber(totalsSource.EnabledDevicesByContract ?? 0) },
                    { label: 'Maintenance Alerts', icon: 'fa-exclamation-circle', value: getByKey(supplyAlerts, 'tomanage') },
                    { label: 'Non-communicating', icon: 'fa-plug-circle-xmark', value: helpers.formatNumber(sdsDashboard.NonCommunicatingDevices ?? 0) }
                ];

                const contactedHtml = contacted.length
                    ? contacted.map(item => `
                        <div class="pill">
                            <span class="pill-label">${helpers.escape(item.Key)}</span>
                            <span class="pill-value">${helpers.escape(item.Value)}</span>
                        </div>
                    `).join('')
                    : '<div class="empty-state-inline">No recent device contact data.</div>';

                const healthPills = [
                    { label: 'Devices w/ Errors', value: sdsDashboard.DevicesWithErrors },
                    { label: 'Devices w/ Warnings', value: sdsDashboard.DevicesWithWarnings },
                    { label: 'Connectors w/ Errors', value: sdsDashboard.ConnectorsWithErrors },
                    { label: 'Connectors w/ Warnings', value: sdsDashboard.ConnectorsWithWarnings },
                    { label: 'Non-communicating Connectors', value: sdsDashboard.NonCommunicatingConnectors }
                ].filter(item => item.value !== undefined && item.value !== null);

                const supplyPipelineKeys = ['tomanage', 'shipped', 'delivered', 'installed', 'canceled'];
                const supplyPipeline = supplyPipelineKeys.map(key => ({
                    label: key.charAt(0).toUpperCase() + key.slice(1),
                    value: getByKey(supplyAlerts, key)
                })).filter(item => item.value !== '0' || supplyAlerts.length);

                const suppliesDeliveryHtml = suppliesDelivery.length
                    ? suppliesDelivery.map(item => `
                        <div class="pill">
                            <span class="pill-label">${helpers.escape(item.Key)}</span>
                            <span class="pill-value">${helpers.escape(item.Value)}</span>
                        </div>
                    `).join('')
                    : '';

                const toBeUpdatedHtml = toBeUpdated.length
                    ? toBeUpdated.map(item => `
                        <li>
                            <strong>${helpers.escape(item.Type ?? 'Unknown')}</strong>
                            <span class="muted">Count:</span> ${helpers.formatNumber(item.Count ?? 0)}
                        </li>
                    `).join('')
                    : '<li>No pending updates.</li>';

                const booksHtml = books.length
                    ? books.map(item => `<div class="pill"><span class="pill-label">${helpers.escape(item.Key)}</span><span class="pill-value">${helpers.escape(item.Value)}</span></div>`).join('')
                    : '<div class="empty-state-inline">No catalog data.</div>';

                const warningsHtml = warnings.length
                    ? warnings.map(item => `<li><strong>${helpers.escape(item.Key)}:</strong> ${helpers.escape(item.Value)}</li>`).join('')
                    : '<li>No warnings reported.</li>';

                const explorerStatus = totalsSource.HasSentExplorerEmail ? 'Sent' : 'Not Sent';
                const explorerDate = totalsSource.EmailExplorerInstallationSentAt
                    ? helpers.escape(totalsSource.EmailExplorerInstallationSentAt)
                    : 'N/A';

                const duplicateGridId = 'customer-duplicate-ip-grid';

                function openLifecycle(searchTerm = '') {
                    const params = new URLSearchParams();
                    if (context.customerCode) {
                        params.set('customerCode', context.customerCode);
                    }
                    if (searchTerm) {
                        params.set('search', searchTerm);
                    }
                    const url = `device-lifecycle.php?${params.toString()}`;
                    window.open(url, '_blank');
                }

                function renderDuplicateComparison(group) {
                    const modalBody = helpers.createModal(`Duplicate IP: ${helpers.escape(group.ip)}`);
                    const devices = Array.isArray(group.devices) ? group.devices : [];

                    if (!devices.length) {
                        modalBody.innerHTML = '<div class="empty-state-inline">No devices available for this IP.</div>';
                        return;
                    }

                    const fields = [
                        {
                            label: 'Equipment ID',
                            render: (device) => helpers.escape(
                                (window.getEquipmentIdFromDevice && window.getEquipmentIdFromDevice(device))
                                || resolveSerial(device)
                                || 'N/A'
                            )
                        },
                        { label: 'Serial', render: (device) => helpers.escape(resolveSerial(device) || '—') },
                        { label: 'Model', render: (device) => helpers.escape(resolveModel(device)) },
                        {
                            label: 'Status',
                            render: (device) => {
                                const offline = isDeviceOffline(device);
                                return `<span class="status-badge ${offline ? 'status-danger' : 'status-success'}">${offline ? 'Offline' : 'Online'}</span>`;
                            }
                        },
                        { label: 'Customer', render: (device) => helpers.escape(resolveCustomer(device)) },
                        {
                            label: 'Asset',
                            render: (device) => helpers.escape((device?.AssetNumber ?? device?.Asset ?? '—').toString().trim() || '—')
                        },
                        { label: 'Location', render: (device) => helpers.escape(resolveLocation(device)) },
                        { label: 'Last Contact', render: (device) => helpers.escape(resolveLastContact(device) || '—') }
                    ];

                    const gridColumns = `repeat(${devices.length + 1}, minmax(0, 1fr))`;

                    modalBody.innerHTML = `
                        <div class="duplicate-comparison-actions">
                            <button class="btn btn-secondary" data-action="back-duplicates">
                                <i class="fas fa-arrow-left"></i> Back to duplicates
                            </button>
                            <div class="comparison-actions-inline">
                                <button class="btn btn-primary" data-action="open-lifecycle-ip" data-ip="${helpers.escape(group.ip)}">
                                    <i class="fas fa-up-right-from-square"></i> Open Device Lifecycle
                                </button>
                            </div>
                        </div>
                        <div class="duplicate-comparison-grid" style="grid-template-columns: ${gridColumns};">
                            <div class="comparison-label">&nbsp;</div>
                            ${devices.map(device => `
                                <div class="comparison-header">
                                    <div class="comparison-title">${helpers.escape(resolveModel(device))}</div>
                                    <div class="comparison-sub">${helpers.escape(resolveSerial(device) || resolveCustomer(device))}</div>
                                    <div class="comparison-subtle">${helpers.escape(resolveCustomer(device))}</div>
                                </div>
                            `).join('')}
                            ${fields.map(field => `
                                <div class="comparison-label">${helpers.escape(field.label)}</div>
                                ${devices.map(device => `<div class="comparison-cell">${field.render(device)}</div>`).join('')}
                            `).join('')}
                            <div class="comparison-label">Actions</div>
                            ${devices.map(device => `
                                <div class="comparison-actions">
                                    <button class="btn btn-secondary" data-action="view-device" data-device-id="${helpers.escape(resolveDeviceIdForModal(device) || '')}" data-serial="${helpers.escape(resolveSerial(device) || '')}">
                                        <i class="fas fa-eye"></i> View device
                                    </button>
                                    <button class="btn btn-primary" data-action="edit-device-lifecycle" data-ip="${helpers.escape(group.ip)}" data-serial="${helpers.escape(resolveSerial(device) || '')}">
                                        <i class="fas fa-pen"></i> Edit in Lifecycle
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                    `;

                    modalBody.querySelector('[data-action="back-duplicates"]')?.addEventListener('click', () => {
                        renderBaseView();
                    });

                    modalBody.querySelector('[data-action="open-lifecycle-ip"]')?.addEventListener('click', (event) => {
                        event.stopPropagation();
                        const ip = event.currentTarget.getAttribute('data-ip') || '';
                        openLifecycle(ip);
                    });

                    modalBody.querySelectorAll('[data-action="view-device"]').forEach(button => {
                        button.addEventListener('click', (event) => {
                            event.stopPropagation();
                            const deviceId = button.getAttribute('data-device-id') || '';
                            const serial = button.getAttribute('data-serial') || '';
                            if (window.MPSM && typeof window.MPSM.openDeviceModal === 'function') {
                                window.MPSM.openDeviceModal(deviceId || null, serial || null, context.customerCode);
                            }
                        });
                    });

                    modalBody.querySelectorAll('[data-action="edit-device-lifecycle"]').forEach(button => {
                        button.addEventListener('click', (event) => {
                            event.stopPropagation();
                            const serial = button.getAttribute('data-serial') || '';
                            const ip = button.getAttribute('data-ip') || group.ip;
                            openLifecycle(serial || ip);
                        });
                    });
                }

                function wireDuplicateSection() {
                    const duplicateGrid = modal.querySelector(`#${duplicateGridId}`);
                    if (!duplicateGrid) {
                        return;
                    }

                    duplicateGrid.querySelectorAll('.dup-ip-card').forEach(card => {
                        card.addEventListener('click', () => {
                            const ip = card.getAttribute('data-ip') || '';
                            if (ip) {
                                openLifecycle(ip);
                            }
                        });
                    });

                    modal.querySelectorAll('[data-action="open-lifecycle-all"]').forEach(button => {
                        button.addEventListener('click', (event) => {
                            event.stopPropagation();
                            openLifecycle('');
                        });
                    });
                }

                function renderBaseView() {
                    modal.innerHTML = `
                        <section class="customer-overview-section">
                            <div class="modal-metrics">
                                ${metrics.map(metric => `
                                    <div class="modal-metric">
                                        <div class="modal-metric-icon"><i class="fas ${metric.icon}"></i></div>
                                        <div>
                                            <div class="modal-metric-value">${metric.value}</div>
                                            <div class="modal-metric-label">${metric.label}</div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </section>

                        <section class="customer-overview-section">
                            <h3><i class="fas fa-signal"></i> Communication & Health</h3>
                            <div class="pill-grid">${contactedHtml}</div>
                            <div class="pill-grid">
                                ${healthPills.length
                                    ? healthPills.map(item => `
                                        <div class="pill">
                                            <span class="pill-label">${helpers.escape(item.label)}</span>
                                            <span class="pill-value">${helpers.formatNumber(item.value ?? 0)}</span>
                                        </div>
                                    `).join('')
                                    : '<div class="empty-state-inline">No device or connector health warnings.</div>'
                                }
                            </div>
                        </section>

                        <section class="customer-overview-section">
                            <h3><i class="fas fa-boxes-stacked"></i> Supply Pipeline</h3>
                            <div class="pill-grid">
                                ${supplyPipeline.length
                                    ? supplyPipeline.map(item => `
                                        <div class="pill">
                                            <span class="pill-label">${helpers.escape(item.label)}</span>
                                            <span class="pill-value">${helpers.escape(item.value)}</span>
                                        </div>
                                    `).join('')
                                    : '<div class="empty-state-inline">No supply movement recorded.</div>'
                                }
                            </div>
                                ${suppliesDeliveryHtml ? `<div class="pill-grid">${suppliesDeliveryHtml}</div>` : ''}
                        </section>

                        <section class="customer-overview-section">
                            <h3><i class="fas fa-rotate"></i> Update Backlog</h3>
                            <ul class="metric-list">${toBeUpdatedHtml}</ul>
                        </section>

                        <section class="customer-overview-section">
                            <div class="section-heading with-actions">
                                <h3><i class="fas fa-network-wired"></i> Duplicate IPs</h3>
                                <div class="section-actions">
                                    <button class="btn btn-secondary" data-action="open-lifecycle-all">
                                        <i class="fas fa-up-right-from-square"></i> Open Device Lifecycle
                                    </button>
                                </div>
                            </div>
                            <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 12px; margin-bottom: 12px; font-size: 0.9em;">
                                <strong>Debug Info:</strong><br>
                                duplicateIpGroups type: ${typeof duplicateIpGroups}<br>
                                duplicateIpGroups length: ${duplicateIpGroups?.length ?? 'undefined'}<br>
                                isArray: ${Array.isArray(duplicateIpGroups)}<br>
                                ${duplicateIpGroups?.length > 0 ? `First group IP: ${duplicateIpGroups[0]?.ip}, devices: ${duplicateIpGroups[0]?.devices?.length}` : ''}
                            </div>
                            <div class="dup-ip-grid" id="${duplicateGridId}">
                                ${duplicateIpGroups.length
                                    ? duplicateIpGroups.map(group => `
                                        <div class="dup-ip-card" data-ip="${helpers.escape(group.ip)}">
                                            <header>
                                                <div>
                                                    <p class="dup-ip-label">Duplicate IP Address</p>
                                                    <h4>${helpers.escape(group.ip)}</h4>
                                                </div>
                                                <span class="dup-ip-badge dup-ip-badge-warning"><i class="fas fa-exclamation-triangle"></i> Duplicate</span>
                                            </header>
                                            <div class="dup-ip-hint">Click IP to search in Device Lifecycle</div>
                                        </div>
                                    `).join('')
                                    : '<div class="empty-state-inline">No duplicate IPs detected for this customer.</div>'
                                }
                            </div>
                            <p class="section-help">Click a card to search for devices with that IP in Device Lifecycle.</p>
                        </section>

                        <section class="customer-overview-section">
                            <h3><i class="fas fa-book"></i> Catalog & Explorer</h3>
                            <div class="pill-grid">${booksHtml}</div>
                            <div class="explorer-status">
                                <span class="pill pill-muted">
                                    <span class="pill-label">Explorer Email</span>
                                    <span class="pill-value">${explorerStatus}</span>
                                </span>
                                <span class="pill pill-muted">
                                    <span class="pill-label">Sent At</span>
                                    <span class="pill-value">${explorerDate}</span>
                                </span>
                            </div>
                        </section>

                        <section class="customer-overview-section">
                            <h3><i class="fas fa-triangle-exclamation"></i> Warnings</h3>
                            <ul class="metric-list">${warningsHtml}</ul>
                        </section>
                    `;

                    wireDuplicateSection();
                }

                renderBaseView();
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

                const offline = devices.filter(device => isDeviceOffline(device)).length;

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
                            accessor: row => isDeviceOffline(row) ? 'Offline' : 'Online',
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
                            if (typeof window.MPSM?.showToast === 'function') {
                                window.MPSM.showToast('At least one supply type must remain visible', 'warning');
                            }
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
                        { id: 'EquipmentId', label: 'Equipment ID', accessor: row => {
                            // Use the global equipment ID resolver from app.js
                            if (typeof window.getEquipmentIdFromAlert === 'function') {
                                return window.getEquipmentIdFromAlert(row);
                            }
                            return row.EquipmentId || row.AssetNumber || row.SerialNumber || 'N/A';
                        }, sortable: true },
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

                try {
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
                } catch (error) {
                    console.error('Top Devices card failed to load devices:', error);
                    devices = [];
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
                const offlineCount = topDevices.filter(device => isDeviceOffline(device)).length;
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
                    pageSize: 50,
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
                    pageSize: 50,
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
                const data = await helpers.fetchJson('api/export-library-cache.php', {
                    action: 'list',
                    customerCode: context.customerCode
                });
                const exports = Array.isArray(data.exports) ? data.exports : [];
                const ready = exports.filter(entry => entry.status === 'ready').length;
                const stale = exports.filter(entry => entry.status === 'stale').length;

                return {
                    headline: {
                        value: exports.length,
                        label: 'Cached Exports'
                    },
                    metrics: [
                        {
                            label: 'Ready',
                            value: ready,
                            tone: ready ? 'success' : 'warning'
                        },
                        {
                            label: 'Stale',
                            value: stale || 0,
                            tone: stale ? 'warning' : 'muted'
                        },
                        { label: 'Pending', value: Math.max(exports.length - ready - stale, 0) }
                    ],
                    context: {
                        exports,
                        ttl: data.ttl_seconds,
                        last_refreshed: data.last_refreshed
                    }
                };
            },
            renderModal: (helpers, context, snapshot) => {
                const modal = helpers.createModal('Export Library');
                const exports = snapshot.context.exports || [];

                if (!exports.length) {
                    modal.innerHTML = '<div class="empty-state"><i class="fas fa-info-circle"></i><p>No cached exports are currently available.</p></div>';
                    return;
                }

                const formatBytes = (bytes) => {
                    if (!Number.isFinite(bytes) || bytes <= 0) {
                        return '--';
                    }
                    const mb = bytes / (1024 * 1024);
                    return `${mb >= 0.1 ? mb.toFixed(1) : mb.toFixed(2)} MB`;
                };

                const tableContainer = document.createElement('div');
                modal.appendChild(tableContainer);

                const escapeHtml = helpers.escape;
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
                        accessor: row => row.status || 'pending',
                        sortable: true,
                        format: (value, row) => {
                            const status = (value || 'pending').toString().toLowerCase();
                            let tone = 'muted';
                            if (status === 'ready') tone = 'success';
                            else if (status === 'stale') tone = 'warning';
                            else if (status === 'error') tone = 'danger';
                            const title = row.last_error ? ` title="${helpers.escape(row.last_error)}"` : '';
                            return `<span class="status-badge status-${tone}"${title}>${helpers.escape(status)}</span>`;
                        }
                    },
                    {
                        id: 'download',
                        label: 'Download',
                        accessor: row => row.download_url,
                        format: (value, row) => {
                            if (row.status !== 'ready' || !row.download_url) {
                                return `<button type="button" class="btn btn-secondary btn-sm" disabled>Not ready</button>`;
                            }
                            const url = helpers.escape(row.download_url);
                            return `<a class="btn btn-primary btn-sm" href="${url}" target="_blank" rel="noopener"><i class="fas fa-download"></i> Download</a>`;
                        }
                    },
                    {
                        id: 'updated_at',
                        label: 'Updated',
                        accessor: row => row.updated_at || '',
                        sortable: true,
                        format: value => value ? new Date(value).toLocaleString() : '—'
                    },
                    {
                        id: 'size_bytes',
                        label: 'Size',
                        accessor: row => row.size_bytes ?? null,
                        sortable: true,
                        format: value => formatBytes(Number(value) || 0)
                    }
                ];

                const exportTable = helpers.renderTable(tableContainer, {
                    columns,
                    rows: exports,
                    pageSize: 50,
                    defaultSort: { column: 'action', direction: 'asc' }
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
                    pageSize: 50,
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
                    pageSize: 50,
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

/*
CHANGELOG
2025-11-23 Codex
- Renamed Alerts metric to Maintenance Alerts and hardened Top Devices card loading to tolerate non-JSON responses without crashing.
2025-11-24 Codex
- Expanded Customer Snapshot modal with structured metrics, supply pipeline, health, warnings, and explorer status using existing dashboard payload data.
- Added duplicate IP insights (online/offline counts) sourced from cached devices plus quick link to lifecycle workspace.
2025-11-25 Codex
- Built duplicate IP grouping with interactive comparison grid, lifecycle handoff, and per-device actions directly inside the Customer Snapshot modal.
- Added lifecycle deep links and lifecycle CTA buttons that honor the active customer context.
- Export Library now previews structured responses, downloads JSON when files are not returned, and surfaces richer runtime status details.
2025-11-25 Codex
- Export Library consumes cached exports (daily prefetch), disables live runs, and provides ready/stale/pending status with direct cached download links.
2025-11-28 Codex
- Export Library now prioritizes direct spreadsheet downloads when Base64Content/MimeType/FileName payloads are returned and skips JSON preview for file responses.
- Search bar now prefers cached devices and device drill-down modal shows panel maintenance/system alerts alongside other device data.
*/
