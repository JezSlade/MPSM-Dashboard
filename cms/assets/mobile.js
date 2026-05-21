const MobileApp = (() => {
    const config = window.MPSM_MOBILE_CONFIG || {};
    const state = {
        alerts: [],
        searchResults: [],
        activeSection: 'alerts',
        lastQuery: '',
        customers: [],
        customerSearchTimeout: null,
        customerSearchTerm: '',
        lifecycle: {
            page: 1,
            pageRows: 50,
            total: 0,
            totalPages: 1,
            search: '',
            customerCode: (config.customerCode || '').trim(),
            devices: [],
            loading: false
        }
    };

    let alertInterval = null;
    let searchTimeout = null;
    let lifecycleSnapshot = null;

    const fetchJson = async (url, options = {}, retries = 2) => {
        let attempt = 0;
        let lastError = null;

        while (attempt <= retries) {
            try {
                const response = await fetch(url, Object.assign({
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                }, options));

                const contentType = response.headers.get('content-type') || '';
                const isJson = contentType.includes('application/json');

                if (response.status === 429) {
                    let retryAfter = 60;
                    try {
                        const data = await response.json();
                        retryAfter = (data.retry_after || retryAfter);
                    } catch (_) {
                        // ignore parse errors, keep default
                    }
                    await new Promise((resolve) => setTimeout(resolve, retryAfter * 1000));
                    attempt += 1;
                    continue;
                }

                if (!isJson) {
                    const snippet = await response.text();
                    throw new Error(`Unexpected response (non-JSON). ${response.status} ${response.statusText}. Preview: ${snippet.slice(0, 120)}`);
                }

                const data = await response.json();
                if (data && data.success === false) {
                    throw new Error(data.error || 'Request failed');
                }
                return data;
            } catch (error) {
                lastError = error;
                attempt += 1;
            }
        }

        throw lastError || new Error('Request failed');
    };

    const setActiveSection = (target) => {
        const sections = document.querySelectorAll('.mobile-section');
        sections.forEach(section => {
            section.classList.toggle('active', section.dataset.section === target);
        });

        const navButtons = document.querySelectorAll('.nav-btn');
        navButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.target === target);
        });

        state.activeSection = target;

        if (target === 'lifecycle') {
            loadLifecycle().catch(() => {});
        }
    };

    const renderAlerts = (list) => {
        const container = document.getElementById('mobile-alert-list');
        const countEl = document.getElementById('mobile-alert-count');
        countEl.textContent = list.length;

        if (!list.length) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>No active alerts</p>
                </div>
            `;
            return;
        }

        container.innerHTML = list.map(item => {
            const severity = (item.severity || 'info').toLowerCase();
            const customer = item.customer_code || config.customerCode || '—';
            const device = item.device_serial || 'Device';
            const alert = item.alert_code || item.title || 'Alert';
            const created = item.created_at_ny || item.created_at || '';

            return `
                <article class="alert-card">
                    <div class="alert-header">
                        <div class="alert-title">${escapeHtml(item.title || alert)}</div>
                        <span class="badge ${severity}">${severity}</span>
                    </div>
                    <div class="alert-meta">
                        <span><i class="fas fa-print"></i> ${escapeHtml(device)}</span>
                        <span><i class="fas fa-user"></i> ${escapeHtml(customer)}</span>
                        ${created ? `<span><i class="fas fa-clock"></i> ${escapeHtml(created)}</span>` : ''}
                    </div>
                    ${item.message ? `<div class="alert-message">${escapeHtml(item.message)}</div>` : ''}
                </article>
            `;
        }).join('');
    };

    const loadAlerts = async () => {
        const customerParam = config.customerCode ? `&customerCode=${encodeURIComponent(config.customerCode)}` : '';
        const data = await fetchJson(`api/command-center.php?action=get_notifications&status=active&limit=50${customerParam}`);
        const alerts = Array.isArray(data.notifications) ? data.notifications : [];
        state.alerts = alerts;
        renderAlerts(alerts);
    };

    const updateLifecycleMeta = () => {
        const meta = document.getElementById('lifecycle-meta');
        if (!meta) return;
        meta.textContent = `Page ${state.lifecycle.page} of ${state.lifecycle.totalPages} • ${state.lifecycle.total} devices`;
    };

    const renderLifecycleList = () => {
        const container = document.getElementById('lifecycle-list');
        if (!container) return;

        if (!state.lifecycle.devices.length) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>No devices matched the filters</p>
                </div>
            `;
            updateLifecycleMeta();
            return;
        }

        container.innerHTML = state.lifecycle.devices.map(device => {
            const serial = escapeHtml(device.SerialNumber || device.DeviceSerialNumber || '—');
            const customer = escapeHtml(device.CustomerDescription || device.CustomerName || device.CustomerCode || '—');
            const model = escapeHtml(device.ProductModel || device.Model || '—');
            const brand = escapeHtml(device.ProductBrand || device.Brand || '—');
            const asset = escapeHtml(device.AssetNumber || device.Asset || '—');
            const status = device.IsUninstalled ? 'Uninstalled' : (device.IsOffline ? 'Offline' : 'Active');
            const deviceId = device.DeviceId || device.Id || device.InstalledProductId || '';

            return `
                <article class="lifecycle-card" data-device-id="${escapeHtml(deviceId)}">
                    <div class="lifecycle-row">
                        <div>
                            <div class="lifecycle-serial">${serial}</div>
                            <div class="lifecycle-sub">${customer}</div>
                        </div>
                        <span class="badge ${status.toLowerCase()}">${status}</span>
                    </div>
                    <div class="lifecycle-row meta">
                        <span><i class="fas fa-box"></i> ${brand} / ${model}</span>
                        <span><i class="fas fa-barcode"></i> ${asset}</span>
                    </div>
                    <div class="lifecycle-actions">
                        <button class="btn-secondary small" data-action="edit"><i class="fas fa-pen"></i> Edit</button>
                        <button class="btn-danger small" data-action="delete"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </article>
            `;
        }).join('');

        container.querySelectorAll('[data-action="edit"]').forEach(btn => {
            btn.addEventListener('click', (event) => {
                const card = event.target.closest('.lifecycle-card');
                const deviceId = card?.dataset.deviceId;
                const device = state.lifecycle.devices.find(d => String(d.DeviceId || d.Id || d.InstalledProductId || '') === deviceId);
                if (device) {
                    openUpdateSheet(device);
                }
            });
        });

        container.querySelectorAll('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', (event) => {
                const card = event.target.closest('.lifecycle-card');
                const deviceId = card?.dataset.deviceId;
                if (deviceId) {
                    deleteDevice(deviceId);
                }
            });
        });

        updateLifecycleMeta();
    };

    const updateLifecycleControls = () => {
        const prevBtn = document.getElementById('lifecycle-prev');
        const nextBtn = document.getElementById('lifecycle-next');
        if (prevBtn) prevBtn.disabled = state.lifecycle.page <= 1;
        if (nextBtn) nextBtn.disabled = state.lifecycle.page >= state.lifecycle.totalPages;
    };

    const loadLifecycle = async () => {
        if (state.lifecycle.loading) return;
        const container = document.getElementById('lifecycle-list');
        if (container) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading devices...</p>
                </div>
            `;
        }
        state.lifecycle.loading = true;
        try {
            const params = new URLSearchParams();
            params.set('page', String(state.lifecycle.page));
            params.set('pageRows', String(state.lifecycle.pageRows));
            params.set('sortColumn', 'AssetNumber');
            params.set('sortOrder', 'Asc');
            if (state.lifecycle.customerCode) {
                params.set('customerCode', state.lifecycle.customerCode);
            }
            if (state.lifecycle.search) {
                params.set('search', state.lifecycle.search);
            }

            const data = await fetchJson(`api/device-list.php?${params.toString()}`);
            state.lifecycle.devices = Array.isArray(data.devices) ? data.devices : [];
            state.lifecycle.total = data.total || state.lifecycle.devices.length;
            state.lifecycle.totalPages = Math.max(1, Math.ceil(state.lifecycle.total / state.lifecycle.pageRows));
            renderLifecycleList();
            updateLifecycleControls();
        } catch (error) {
            if (container) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>${escapeHtml(error.message)}</p>
                    </div>
                `;
            }
        } finally {
            state.lifecycle.loading = false;
            updateLifecycleMeta();
            updateLifecycleControls();
        }
    };

    const openSheet = (id) => {
        const sheet = document.getElementById(id);
        const overlay = document.getElementById('lifecycle-overlay');
        if (sheet && overlay) {
            sheet.setAttribute('aria-hidden', 'false');
            sheet.classList.add('active');
            overlay.setAttribute('aria-hidden', 'false');
            overlay.classList.add('active');
            overlay.dataset.target = id;
        }
    };

    const closeSheet = (id) => {
        const sheet = document.getElementById(id);
        const overlay = document.getElementById('lifecycle-overlay');
        if (sheet) {
            sheet.setAttribute('aria-hidden', 'true');
            sheet.classList.remove('active');
        }
        if (overlay && (!id || overlay.dataset.target === id)) {
            overlay.setAttribute('aria-hidden', 'true');
            overlay.classList.remove('active');
            overlay.dataset.target = '';
        }
    };

    const resetCreateSheet = () => {
        const form = document.getElementById('lifecycle-create-form');
        if (!form) return;
        form.reset();
        const defaultCustomer = document.getElementById('create-customer');
        if (defaultCustomer) {
            defaultCustomer.value = (config.customerCode || '').trim();
        }
        const managed = document.getElementById('create-managed');
        if (managed) {
            managed.checked = true;
        }
    };

    const lifecycleSnapshotFromDevice = (device) => ({
        assetNumber: device.AssetNumber || device.Asset || '',
        department: device.Department || device.Location || '',
        contact: device.Contact || device.ContactName || '',
        ipAddress: device.AddressIP || device.IPAddress || device.IpAddress || device.IP || '',
        note: device.Note || device.Notes || '',
        productDescription: device.ProductDescription || device.Description || '',
        isManaged: Boolean(device.IsManaged),
        isGenerateAlert: Boolean(device.IsGenerateAlert),
        manageRepeatedAlerts: Boolean(device.ManageRepeatedAlerts),
        brand: device.ProductBrand || device.Brand || '',
        model: device.ProductModel || device.Model || ''
    });

    const openUpdateSheet = (device) => {
        const form = document.getElementById('lifecycle-update-form');
        const meta = document.getElementById('update-device-meta');
        const title = document.getElementById('update-sheet-title');
        if (!form) return;

        lifecycleSnapshot = lifecycleSnapshotFromDevice(device);

        form.reset();
        form.querySelector('#update-device-id').value = device.DeviceId || device.Id || device.InstalledProductId || '';
        form.querySelector('#update-brand').value = '';
        form.querySelector('#update-model').value = '';
        form.querySelector('#update-asset').value = lifecycleSnapshot.assetNumber;
        form.querySelector('#update-department').value = lifecycleSnapshot.department;
        form.querySelector('#update-contact').value = lifecycleSnapshot.contact;
        form.querySelector('#update-ip').value = lifecycleSnapshot.ipAddress;
        form.querySelector('#update-product-description').value = lifecycleSnapshot.productDescription;
        form.querySelector('#update-note').value = lifecycleSnapshot.note;

        const managed = form.querySelector('#update-managed');
        const alerts = form.querySelector('#update-alerts');
        const repeats = form.querySelector('#update-repeat-alerts');
        if (managed) managed.checked = lifecycleSnapshot.isManaged;
        if (alerts) alerts.checked = lifecycleSnapshot.isGenerateAlert;
        if (repeats) repeats.checked = lifecycleSnapshot.manageRepeatedAlerts;

        if (meta) {
            const serial = escapeHtml(device.SerialNumber || device.DeviceSerialNumber || '—');
            const customer = escapeHtml(device.CustomerDescription || device.CustomerCode || '—');
            meta.innerHTML = `
                <span class="pill">${serial}</span>
                <span class="pill">${customer}</span>
            `;
        }
        if (title) {
            title.textContent = `Edit ${device.SerialNumber || device.DeviceSerialNumber || 'device'}`;
        }

        openSheet('lifecycle-update-sheet');
    };

    const collectUpdatePayload = (form) => {
        const payload = { deviceId: form.querySelector('#update-device-id').value };
        const snapshot = lifecycleSnapshot || {};

        const diffField = (name, value, originalValue) => {
            const current = (value ?? '').trim();
            const original = (originalValue ?? '').toString().trim();
            if (current !== original) {
                payload[name] = current;
            }
        };

        diffField('newProductBrand', form.querySelector('#update-brand').value, '');
        diffField('newProductModel', form.querySelector('#update-model').value, '');
        diffField('assetNumber', form.querySelector('#update-asset').value, snapshot.assetNumber);
        diffField('department', form.querySelector('#update-department').value, snapshot.department);
        diffField('contact', form.querySelector('#update-contact').value, snapshot.contact);
        diffField('ipAddress', form.querySelector('#update-ip').value, snapshot.ipAddress);
        diffField('productDescription', form.querySelector('#update-product-description').value, snapshot.productDescription);
        diffField('note', form.querySelector('#update-note').value, snapshot.note);

        const managed = form.querySelector('#update-managed')?.checked ?? snapshot.isManaged;
        const alerts = form.querySelector('#update-alerts')?.checked ?? snapshot.isGenerateAlert;
        const repeats = form.querySelector('#update-repeat-alerts')?.checked ?? snapshot.manageRepeatedAlerts;
        if (managed !== snapshot.isManaged) payload.isManaged = managed;
        if (alerts !== snapshot.isGenerateAlert) payload.isGenerateAlert = alerts;
        if (repeats !== snapshot.manageRepeatedAlerts) payload.manageRepeatedAlerts = repeats;

        return payload;
    };

    const deleteDevice = async (deviceId) => {
        if (!deviceId) return;
        const confirmed = window.confirm('Delete this device? This cannot be undone.');
        if (!confirmed) return;
        await fetchJson('api/device-delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ deviceId })
        });
        await loadLifecycle();
    };

    const renderLookupResults = (results, query) => {
        const container = document.getElementById('mobile-lookup-results');

        if (!results.length) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>${query ? 'No devices found' : 'Start typing to search devices'}</p>
                </div>
            `;
            return;
        }

        container.innerHTML = results.map(device => {
            const equipmentId = escapeHtml(getEquipmentId(device));
            const model = escapeHtml(device.ProductModel || device.Product?.Model || 'Unknown model');
            const customer = escapeHtml(device.CustomerDescription || 'Unknown customer');
            const serial = escapeHtml(device.SerialNumber || device.DeviceSerialNumber || '');
            const status = device.IsUninstalled ? 'Uninstalled' : (device.IsOffline ? 'Offline' : 'Active');
            const deviceId = device.Id || device.IdInstalledProduct || device.DeviceId || '';
            const customerCode = device.CustomerCode || config.customerCode || '';

            return `
                <article class="lookup-card" data-serial="${serial}" data-device-id="${deviceId}" data-customer-code="${escapeHtml(customerCode)}">
                    <div class="lookup-title">${equipmentId}</div>
                    <div class="lookup-sub">${customer}${serial ? ' • SN ' + serial : ''}</div>
                    <div class="lookup-tags">
                        <span class="tag">${model}</span>
                        <span class="tag">${status}</span>
                    </div>
                </article>
            `;
        }).join('');

        container.querySelectorAll('.lookup-card').forEach(card => {
            card.addEventListener('click', () => {
                const deviceId = card.dataset.deviceId;
                const serial = card.dataset.serial;
                const customerCode = card.dataset.customerCode;
                openDeviceModal({ deviceId, serialNumber: serial, customerCode });
            });
        });
    };

    const searchDevices = async (query) => {
        state.lastQuery = query;
        const resultsContainer = document.getElementById('mobile-search-results');
        resultsContainer.innerHTML = `
            <div class="search-result">
                <div class="search-title"><i class="fas fa-spinner fa-spin"></i> Searching...</div>
            </div>
        `;

        const params = new URLSearchParams({ query });
        if (config.customerCode) {
            params.set('customerCode', config.customerCode);
        }

        const data = await fetchJson(`api/search-devices.php?${params.toString()}`);
        const devices = Array.isArray(data.devices) ? data.devices : [];

        state.searchResults = devices;

        if (!devices.length) {
            resultsContainer.innerHTML = `
                <div class="search-result">
                    <div class="search-title">No results</div>
                    <div class="search-meta">Try another term</div>
                </div>
            `;
            renderLookupResults([], query);
            return;
        }

        resultsContainer.innerHTML = devices.slice(0, 6).map(device => {
            const equipmentId = escapeHtml(getEquipmentId(device));
            const model = escapeHtml(device.ProductModel || device.Product?.Model || 'Unknown model');
            const customer = escapeHtml(device.CustomerDescription || 'Unknown customer');
            const serial = escapeHtml(device.SerialNumber || device.DeviceSerialNumber || '');
            return `
                <div class="search-result" data-serial="${serial}" data-device-id="${device.Id || device.IdInstalledProduct || ''}" data-customer-code="${escapeHtml(device.CustomerCode || '')}">
                    <div class="search-title">${equipmentId}</div>
                    <div class="search-meta">${customer}${serial ? ' • SN ' + serial : ''}</div>
                    <div class="search-tags">
                        <span class="tag">${model}</span>
                    </div>
                </div>
            `;
        }).join('');

        resultsContainer.querySelectorAll('.search-result').forEach(item => {
            item.addEventListener('click', () => {
                const deviceId = item.dataset.deviceId;
                const serial = item.dataset.serial;
                const customerCode = item.dataset.customerCode || config.customerCode;
                openDeviceModal({ deviceId, serialNumber: serial, customerCode });
            });
        });

        renderLookupResults(devices, query);
    };

    const handleSearchInput = (value) => {
        const resultsContainer = document.getElementById('mobile-search-results');
        if (!value || value.length < 2) {
            resultsContainer.innerHTML = '';
            renderLookupResults([], value);
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchDevices(value).catch(error => {
                resultsContainer.innerHTML = `
                    <div class="search-result">
                        <div class="search-title">Search error</div>
                        <div class="search-meta">${escapeHtml(error.message)}</div>
                    </div>
                `;
            });
        }, 300);
    };

    const openDeviceModal = async ({ deviceId, serialNumber, customerCode }) => {
        const modal = document.getElementById('mobile-device-modal');
        const body = document.getElementById('mobile-modal-body');
        const title = document.getElementById('mobile-modal-title');
        const lifecycleLink = document.getElementById('mobile-modal-lifecycle');

        modal.classList.add('active');
        body.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading device...</p>
            </div>
        `;

        const params = new URLSearchParams();
        if (deviceId) params.append('deviceId', deviceId);
        if (serialNumber) params.append('serialNumber', serialNumber);
        if (customerCode) params.append('customerCode', customerCode);

        try {
            const data = await fetchJson(`api/get-device-deep-dive.php?${params.toString()}`);
            const device = data.device || data.Device || {};
            const name = getEquipmentId(device) || serialNumber || 'Device';
            const ipAddress = String(device.IpAddress || '').trim();
            const ipHref = ipAddress && /^https?:\/\//i.test(ipAddress) ? ipAddress : `http://${ipAddress}`;

            title.textContent = name;
            lifecycleLink.href = 'device-lifecycle.php';

            const rows = [
                { label: 'Serial', value: device.SerialNumber || serialNumber || 'N/A' },
                { label: 'Customer', value: device.CustomerDescription || customerCode || 'N/A' },
                { label: 'Status', value: device.StatusDescription || (device.IsUninstalled ? 'Uninstalled' : 'Active') },
                {
                    label: 'IP Address',
                    value: ipAddress
                        ? `<a href="${escapeHtml(ipHref)}" target="_blank" rel="noopener noreferrer">${escapeHtml(ipAddress)}</a>`
                        : 'N/A',
                    html: Boolean(ipAddress)
                },
                { label: 'Model', value: device.ProductModel || device.Product?.Model || 'Unknown' },
                { label: 'Brand', value: device.ProductBrand || device.Product?.Brand || 'Unknown' },
                { label: 'Last Contact', value: device.LastContact || device.LastContactDate || 'N/A' }
            ];

            const asRows = (value) => {
                if (!value) return [];
                if (Array.isArray(value)) return value;
                if (typeof value === 'object') {
                    for (const key of ['Result', 'Items', 'data', 'CounterDetails', 'Counters', 'Actions', 'SupplyAlerts', 'Alerts', 'MaintenanceKitLevels', 'MaintenanceKitCounters']) {
                        if (Array.isArray(value[key])) return value[key];
                    }
                    return [value];
                }
                return [];
            };

            const valueFor = (item) => {
                if (!item || typeof item !== 'object') return '';
                return item.LevelValue ?? item.Level ?? item.value ?? item.CounterValue ?? item.Value ?? item.Description ?? item.Message ?? item.display_name ?? item.summary ?? '';
            };

            const levelValueFor = (item) => {
                if (!item || typeof item !== 'object') return '';
                return item.value ?? item.Value ?? item.LevelValue ?? item.Level ?? item.CounterValue ?? item.Percent ?? item.Percentage ?? '';
            };

            const labelFor = (item, fallback) => {
                if (!item || typeof item !== 'object') return fallback;
                return item.Key ?? item.key ?? item.label ?? item.Description ?? item.SupplyName ?? item.CounterName ?? item.Name ?? item.Type ?? item.display_name ?? item.ActionType ?? fallback;
            };

            const parseLevelPercent = (value) => {
                if (value === null || value === undefined || value === '') return null;
                const match = String(value).match(/-?\d+(\.\d+)?/);
                if (!match) return null;
                const numeric = Number(match[0]);
                if (!Number.isFinite(numeric)) return null;
                return Math.max(0, Math.min(100, numeric));
            };

            const renderCompactLevels = (title, items, emptyText) => {
                const list = asRows(items).slice(0, 20);
                return `
                    <div class="mobile-detail-section">
                        <h3>${escapeHtml(title)}</h3>
                        ${list.length ? `
                            <div class="mobile-level-grid">
                                ${list.map((item, index) => {
                                    const rawValue = levelValueFor(item);
                                    const percent = parseLevelPercent(rawValue);
                                    return `
                                        <div class="detail-tile mobile-level-tile">
                                            <div class="detail-label">${escapeHtml(labelFor(item, 'Consumable ' + (index + 1)))}</div>
                                            <div class="detail-value">${escapeHtml(String(rawValue || 'N/A'))}</div>
                                            ${percent !== null ? `
                                                <div class="mobile-level-track">
                                                    <div class="mobile-level-fill" style="width:${percent}%"></div>
                                                </div>
                                            ` : ''}
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        ` : `<div class="empty-state"><p>${escapeHtml(emptyText)}</p></div>`}
                    </div>
                `;
            };

            const renderCompactList = (title, items, emptyText) => {
                const list = asRows(items).slice(0, 12);
                return `
                    <div class="mobile-detail-section">
                        <h3>${escapeHtml(title)}</h3>
                        ${list.length ? `
                            <div class="mobile-detail-grid">
                                ${list.map((item, index) => `
                                    <div class="detail-tile">
                                        <div class="detail-label">${escapeHtml(labelFor(item, 'Item ' + (index + 1)))}</div>
                                        <div class="detail-value">${escapeHtml(String(valueFor(item) || 'See details'))}</div>
                                    </div>
                                `).join('')}
                            </div>
                        ` : `<div class="empty-state"><p>${escapeHtml(emptyText)}</p></div>`}
                    </div>
                `;
            };

            const removeIdentifierFields = (item) => {
                if (!item || typeof item !== 'object') return item;
                const hidden = new Set([
                    'id',
                    'identifier',
                    'guid',
                    'deviceid',
                    'iddevice',
                    'idinstalledproduct',
                    'installedproductid',
                    'customerid',
                    'dealerid'
                ]);
                return Object.fromEntries(Object.entries(item).filter(([key]) => !hidden.has(String(key).toLowerCase())));
            };

            const alertTimestamp = (item) => {
                if (!item || typeof item !== 'object') return 0;
                for (const key of ['DateUTC', 'ActionDateUtc', 'InitialDate', 'GeneratedOn', 'CreatedOn', 'LastUpdateUTC', 'CreatedAt', 'created_at', 'received_at', 'OpenedAt']) {
                    if (item[key]) {
                        const parsed = new Date(item[key]).getTime();
                        if (Number.isFinite(parsed)) return parsed;
                    }
                }
                return 0;
            };

            const maintenance = data.maintenance || {};
            const supplies = data.supplies || {};
            const alerts = data.alerts || {};
            const panelMessages = Array.isArray(data.panelHistory?.messages) ? data.panelHistory.messages : [];
            const maintenanceLevels = [
                ...asRows(supplies.levels),
                ...asRows(maintenance.levels),
                ...asRows(maintenance.counters)
            ];
            const maintenanceAlerts = asRows(maintenance.alerts)
                .slice()
                .sort((a, b) => alertTimestamp(b) - alertTimestamp(a))
                .map(removeIdentifierFields);

            body.innerHTML = `
                <div class="mobile-detail-grid">
                    ${rows.map(row => `
                        <div class="detail-tile">
                            <div class="detail-label">${escapeHtml(row.label)}</div>
                            <div class="detail-value">${row.html ? row.value : escapeHtml(String(row.value || 'N/A'))}</div>
                        </div>
                    `).join('')}
                </div>
                ${renderCompactLevels('Consumable Levels', maintenanceLevels, 'No consumable level data available.')}
                ${renderCompactList('Maintenance Alerts', maintenanceAlerts, 'No active maintenance alerts.')}
                ${renderCompactList('Supply Details', supplies.details || supplies.levels, 'No supply detail data available.')}
                ${renderCompactList('Alert History', panelMessages, 'No recent panel messages.')}
            `;
        } catch (error) {
            body.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${escapeHtml(error.message)}</p>
                </div>
            `;
        }
    };

    const getEquipmentId = (device) => {
        if (!device) {
            return '';
        }
        return device.EquipmentId || device.EquipmentCode || device.AssetNumber || device.SerialNumber || device.DeviceSerialNumber || 'Device';
    };

    const savePreference = async (key, value) => {
        try {
            const payload = {};
            payload[key] = value;
            await fetchJson('api/save-preferences.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
        } catch (error) {
            // Silent fail on mobile
        }
    };

    const setCustomerStatus = (message, tone = 'muted') => {
        const el = document.getElementById('mobile-customer-status');
        if (!el) return;
        el.textContent = message || '';
        el.dataset.tone = tone;
    };

    const loadCustomers = async (searchTerm = '') => {
        const select = document.getElementById('mobile-customer-select');
        if (!select) {
            return;
        }

        state.customerSearchTerm = (searchTerm || '').trim();
        select.innerHTML = '<option>Loading...</option>';

        const params = new URLSearchParams();
        if (config.dealerCode) {
            params.append('dealerCode', config.dealerCode);
        }
        if (state.customerSearchTerm && state.customerSearchTerm.length >= 2) {
            params.append('search', state.customerSearchTerm);
        }

        try {
            const data = await fetchJson(`api/get-customers.php?${params.toString()}`);
            state.customers = Array.isArray(data.customers) ? data.customers : [];
            populateCustomerSelect();
            setCustomerStatus(state.customers.length ? '' : 'No customers returned', state.customers.length ? 'muted' : 'warn');
        } catch (error) {
            select.innerHTML = `<option>Error loading customers</option>`;
            setCustomerStatus(error.message || 'Unable to load customers', 'warn');
        }
    };

    const populateCustomerSelect = () => {
        const select = document.getElementById('mobile-customer-select');
        const nameEl = document.getElementById('mobile-customer-name');
        const codeEl = document.getElementById('mobile-customer-code');
        if (!select) {
            return;
        }

        const currentCode = (config.customerCode || '').trim();
        const currentName = (config.customerName || '').trim();
        const filter = (state.customerSearchTerm || '').toLowerCase();
        select.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = state.customers.length ? 'Select customer' : 'No customers';
        placeholder.disabled = true;
        placeholder.selected = !currentCode;
        select.appendChild(placeholder);

        const filtered = state.customers.filter(customer => {
            if (!filter || filter.length < 2) return true;
            return (
                (customer.Description || '').toLowerCase().includes(filter) ||
                (customer.Code || '').toLowerCase().includes(filter)
            );
        });

        filtered.forEach(customer => {
            const option = document.createElement('option');
            option.value = customer.Code;
            option.textContent = customer.Description ? `${customer.Description} (${customer.Code})` : customer.Code;
            if (customer.Code === currentCode) {
                option.selected = true;
            }
            select.appendChild(option);
        });

        if (currentCode && !state.customers.find(c => c.Code === currentCode)) {
            const manual = document.createElement('option');
            manual.value = currentCode;
            manual.textContent = currentName ? `${currentName} (${currentCode})` : currentCode;
            manual.selected = true;
            select.appendChild(manual);
        }

        if (nameEl && currentName) {
            nameEl.textContent = currentName;
        }
        if (codeEl && currentCode) {
            codeEl.textContent = currentCode;
        }
    };

    const setCustomer = async (code, name = '') => {
        if (!code) {
            return;
        }
        config.customerCode = code;
        config.customerName = name || config.customerName || code;

        const nameEl = document.getElementById('mobile-customer-name');
        const codeEl = document.getElementById('mobile-customer-code');
        if (nameEl) {
            nameEl.textContent = config.customerName || code;
        }
        if (codeEl) {
            codeEl.textContent = code;
        }

        await savePreference('customerCode', code);
        if (config.customerName) {
            await savePreference('customerName', config.customerName);
        }

        await loadAlerts().catch(() => {});
        if (state.lastQuery && state.lastQuery.length >= 2) {
            searchDevices(state.lastQuery).catch(() => {});
        }
        state.lifecycle.customerCode = code;
        if (state.activeSection === 'lifecycle') {
            state.lifecycle.page = 1;
            loadLifecycle().catch(() => {});
        }
    };

    const escapeHtml = (value) => {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const bindNav = () => {
        document.querySelectorAll('.nav-btn').forEach(btn => {
            const target = btn.dataset.target;
            if (!target) {
                return;
            }
            btn.addEventListener('click', () => setActiveSection(target));
        });

        document.querySelectorAll('.quick-card[data-target]').forEach(card => {
            const target = card.dataset.target;
            card.addEventListener('click', () => setActiveSection(target));
        });
    };

    const bindSearch = () => {
        const searchInput = document.getElementById('mobile-search');
        const clearBtn = document.getElementById('mobile-search-clear');
        const refreshBtn = document.getElementById('mobile-lookup-refresh');

        if (searchInput) {
            searchInput.addEventListener('input', (e) => handleSearchInput(e.target.value.trim()));
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                handleSearchInput('');
            });
        }

        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                if (state.lastQuery && state.lastQuery.length >= 2) {
                    searchDevices(state.lastQuery).catch(() => {});
                } else {
                    handleSearchInput('');
                }
            });
        }
    };

    const bindCustomerSwitch = () => {
        const select = document.getElementById('mobile-customer-select');

        if (select) {
            select.addEventListener('change', (e) => {
                const code = e.target.value;
                if (!code) {
                    return;
                }
                const match = (state.customers || []).find(c => c.Code === code);
                const name = match ? (match.Description || '') : '';
                setCustomer(code, name);
            });
        }

        loadCustomers().catch(() => {});
    };

    const bindAlerts = () => {
        const refreshBtn = document.getElementById('mobile-alert-refresh');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                loadAlerts().catch(() => {});
            });
        }

        if (alertInterval) {
            clearInterval(alertInterval);
        }
        alertInterval = setInterval(() => {
            if (state.activeSection === 'alerts') {
                loadAlerts().catch(() => {});
            }
        }, 30000);
    };

    const bindLifecycle = () => {
        const form = document.getElementById('lifecycle-filters');
        const resetBtn = document.getElementById('lifecycle-reset');
        const refreshBtn = document.getElementById('lifecycle-refresh');
        const createBtn = document.getElementById('lifecycle-create-open');
        const prevBtn = document.getElementById('lifecycle-prev');
        const nextBtn = document.getElementById('lifecycle-next');
        const overlay = document.getElementById('lifecycle-overlay');
        const createForm = document.getElementById('lifecycle-create-form');
        const updateForm = document.getElementById('lifecycle-update-form');

        form?.addEventListener('submit', (e) => {
            e.preventDefault();
            const customer = document.getElementById('lifecycle-customer')?.value.trim() || '';
            const search = document.getElementById('lifecycle-search')?.value.trim() || '';
            const rows = parseInt(document.getElementById('lifecycle-rows')?.value || '50', 10);
            state.lifecycle.customerCode = customer;
            state.lifecycle.search = search;
            state.lifecycle.pageRows = Number.isNaN(rows) ? 50 : rows;
            state.lifecycle.page = 1;
            loadLifecycle().catch(() => {});
        });

        resetBtn?.addEventListener('click', () => {
            const customerInput = document.getElementById('lifecycle-customer');
            const searchInput = document.getElementById('lifecycle-search');
            const rowsSelect = document.getElementById('lifecycle-rows');
            if (customerInput) customerInput.value = (config.customerCode || '').trim();
            if (searchInput) searchInput.value = '';
            if (rowsSelect) rowsSelect.value = '50';
            state.lifecycle.customerCode = (config.customerCode || '').trim();
            state.lifecycle.search = '';
            state.lifecycle.pageRows = 50;
            state.lifecycle.page = 1;
            loadLifecycle().catch(() => {});
        });

        refreshBtn?.addEventListener('click', () => {
            loadLifecycle().catch(() => {});
        });

        createBtn?.addEventListener('click', () => {
            resetCreateSheet();
            openSheet('lifecycle-create-sheet');
        });

        prevBtn?.addEventListener('click', () => {
            if (state.lifecycle.page > 1) {
                state.lifecycle.page -= 1;
                loadLifecycle().catch(() => {});
            }
        });

        nextBtn?.addEventListener('click', () => {
            if (state.lifecycle.page < state.lifecycle.totalPages) {
                state.lifecycle.page += 1;
                loadLifecycle().catch(() => {});
            }
        });

        overlay?.addEventListener('click', () => {
            closeSheet(overlay.dataset.target || '');
        });

        document.querySelectorAll('[data-close-sheet]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const target = e.currentTarget.getAttribute('data-close-sheet');
                closeSheet(target);
            });
        });

        createForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(createForm);
            const payload = Object.fromEntries(formData.entries());
            payload.isManaged = document.getElementById('create-managed')?.checked ?? false;
            try {
                await fetchJson('api/device-create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                closeSheet('lifecycle-create-sheet');
                await loadLifecycle();
            } catch (error) {
                window.alert(error.message || 'Create failed');
            }
        });

        updateForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!updateForm) return;
            const payload = collectUpdatePayload(updateForm);
            if (Object.keys(payload).length <= 1) {
                window.alert('No changes to save.');
                return;
            }
            try {
                await fetchJson('api/device-update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                closeSheet('lifecycle-update-sheet');
                await loadLifecycle();
            } catch (error) {
                window.alert(error.message || 'Update failed');
            }
        });
    };

    const bindModal = () => {
        const modal = document.getElementById('mobile-device-modal');
        const closeBtn = document.getElementById('mobile-modal-close');
        const refreshBtn = document.getElementById('mobile-modal-refresh');

        closeBtn?.addEventListener('click', () => {
            modal.classList.remove('active');
        });

        modal?.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });

        refreshBtn?.addEventListener('click', () => {
            const currentCard = state.searchResults.find(d => {
                const serial = d.SerialNumber || d.DeviceSerialNumber;
                return serial === state.lastQuery;
            });
            if (currentCard) {
                openDeviceModal({
                    deviceId: currentCard.Id || currentCard.IdInstalledProduct || currentCard.DeviceId,
                    serialNumber: currentCard.SerialNumber || currentCard.DeviceSerialNumber,
                    customerCode: currentCard.CustomerCode || config.customerCode
                });
            }
        });
    };

    const bindHeaderActions = () => {
        const refreshBtn = document.getElementById('mobile-refresh');
        const logoutBtn = document.getElementById('mobile-logout');

        refreshBtn?.addEventListener('click', () => {
            Promise.all([
                loadAlerts(),
                state.lastQuery && state.lastQuery.length >= 2 ? searchDevices(state.lastQuery) : Promise.resolve()
            ]).catch(() => {});
        });

        logoutBtn?.addEventListener('click', async () => {
            try {
                await fetch('api/logout.php', { method: 'POST' });
            } catch (e) {
                // ignore logout errors
            }
            window.location.href = 'login.html';
        });
    };

    const init = () => {
        bindNav();
        bindSearch();
        bindCustomerSwitch();
        bindAlerts();
        bindLifecycle();
        bindModal();
        bindHeaderActions();
        loadAlerts().catch(() => {});
    };

    return { init };
})();

document.addEventListener('DOMContentLoaded', () => {
    MobileApp.init();
});

/*
CHANGELOG
2025-11-24 Codex
- Implemented mobile landing logic with alert polling, device search, section navigation, and device modal using existing APIs.
2025-11-24 Codex
- Added mobile customer switcher reusing get-customers preferences flow; persists selection and reloads alerts/search context.
2025-11-24 Codex
- Improved customer switcher robustness (dealer-aware fetch, client-side filtering, status messaging) and refined styling hooks.
2025-11-24 Codex
- Simplified customer selection to a single header dropdown; removed search input and tightened preference updates.
2025-11-24 Codex
- Added 429-aware fetch retry helper to keep mobile alerts and search resilient under rate limits.
2025-11-25 Codex
- Aligned mobile fetches with dashboard by sending credentials and guarding against HTML responses to prevent “Unexpected token '<'” search errors.
2025-11-29 Codex
- Added mobile lifecycle section with filters, pagination, create/update sheets, and delete actions mirroring desktop CRUD fields (IP, location, asset, contact, note, description).
*/
