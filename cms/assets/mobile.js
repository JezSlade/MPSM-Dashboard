const MobileApp = (() => {
    const config = window.MPSM_MOBILE_CONFIG || {};
    const state = {
        alerts: [],
        searchResults: [],
        activeSection: 'alerts',
        lastQuery: '',
        customers: [],
        customerSearchTimeout: null,
        customerSearchTerm: ''
    };

    let alertInterval = null;
    let searchTimeout = null;

    const fetchJson = async (url, options = {}) => {
        const response = await fetch(url, options);
        const data = await response.json();
        if (data && data.success === false) {
            throw new Error(data.error || 'Request failed');
        }
        return data;
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

        const data = await fetchJson(`api/search-devices.php?query=${encodeURIComponent(query)}`);
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

            title.textContent = name;
            lifecycleLink.href = 'device-lifecycle.php';

            const rows = [
                { label: 'Serial', value: device.SerialNumber || serialNumber || 'N/A' },
                { label: 'Customer', value: device.CustomerDescription || customerCode || 'N/A' },
                { label: 'Status', value: device.StatusDescription || (device.IsUninstalled ? 'Uninstalled' : 'Active') },
                { label: 'Model', value: device.ProductModel || device.Product?.Model || 'Unknown' },
                { label: 'Brand', value: device.ProductBrand || device.Product?.Brand || 'Unknown' },
                { label: 'Last Contact', value: device.LastContact || device.LastContactDate || 'N/A' }
            ];

            body.innerHTML = `
                <div class="mobile-detail-grid">
                    ${rows.map(row => `
                        <div class="detail-tile">
                            <div class="detail-label">${escapeHtml(row.label)}</div>
                            <div class="detail-value">${escapeHtml(String(row.value || 'N/A'))}</div>
                        </div>
                    `).join('')}
                </div>
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
*/
