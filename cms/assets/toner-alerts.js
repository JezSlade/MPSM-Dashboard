/**
 * Toner Alerts page logic.
 * Lets the user pick a dealership + customer and lists their toner alerts,
 * using the shared dealer/customer preference so the choice carries over
 * to the rest of the dashboard.
 */
(function () {
    const body = document.body;
    const state = {
        dealerCode: body.dataset.dealerCode || '',
        dealerId: '',
        customerCode: body.dataset.customerCode || '',
        page: 1,
        pageRows: 100,
        total: 0,
        currentAlerts: [],
    };

    const dealerSelect = document.getElementById('ta-dealer');
    const customerSelect = document.getElementById('ta-customer');
    const rowsEl = document.getElementById('ta-rows');
    const summaryEl = document.getElementById('ta-summary');
    const pageLabelEl = document.getElementById('ta-page-label');
    const prevBtn = document.getElementById('ta-prev');
    const nextBtn = document.getElementById('ta-next');

    // Same precedence the rest of the dashboard uses (app.js getEquipmentIdFromAlert):
    // AssetNumber/Asset/EquipmentId, then ExternalIdentifier/ExternalId, then Serial.
    function getEquipmentId(a) {
        const asset = (a.AssetNumber ?? a.Asset ?? a.EquipmentId ?? '').toString().trim();
        const external = (a.ExternalIdentifier ?? a.ExternalId ?? '').toString().trim();
        if (asset) return asset;
        if (external) return external;
        return '-';
    }

    function levelClass(pct) {
        if (pct === null || pct === undefined) return '';
        if (pct <= 10) return 'critical';
        if (pct <= 25) return 'low';
        return '';
    }

    async function loadDealers() {
        try {
            const data = await fetchJson('api/get-dealers.php');
            const dealers = data.dealers || [];
            dealerSelect.innerHTML = '';
            dealers.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.Code;
                opt.dataset.dealerId = d.Id || '';
                opt.textContent = d.Description || d.Code;
                if (d.Code === state.dealerCode) opt.selected = true;
                dealerSelect.appendChild(opt);
            });
            if (!dealers.some(d => d.Code === state.dealerCode) && dealers.length) {
                state.dealerCode = dealers[0].Code;
            }
            state.dealerId = dealerSelect.selectedOptions[0]?.dataset.dealerId || '';
        } catch (error) {
            dealerSelect.innerHTML = '<option value="">Failed to load dealers</option>';
            showToast('Failed to load dealers: ' + error.message, 'error');
        }
    }

    async function loadCustomers() {
        customerSelect.innerHTML = '<option value="">All Customers</option>';
        if (!state.dealerCode) return;
        try {
            const data = await fetchJson('api/get-customers.php?dealerCode=' + encodeURIComponent(state.dealerCode));
            (data.customers || []).forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.Code;
                opt.textContent = c.Description ? `${c.Description} (${c.Code})` : c.Code;
                if (c.Code === state.customerCode) opt.selected = true;
                customerSelect.appendChild(opt);
            });
        } catch (error) {
            showToast('Failed to load customers: ' + error.message, 'error');
        }
    }

    function severityFor(pct) {
        const cls = levelClass(pct);
        if (cls === 'critical') return { cls, label: 'Critical' };
        if (cls === 'low') return { cls, label: 'Low' };
        return { cls: 'ok', label: 'OK' };
    }

    function daysOpen(initialDate) {
        if (!initialDate) return '-';
        const started = new Date(initialDate).getTime();
        if (Number.isNaN(started)) return '-';
        const days = Math.max(1, Math.floor((Date.now() - started) / 86400000) + 1);
        return days;
    }

    function renderRows(alerts) {
        state.currentAlerts = alerts;

        if (!alerts.length) {
            rowsEl.innerHTML = '<tr><td colspan="6" class="ta-empty">No toner alerts found.</td></tr>';
            return;
        }

        rowsEl.innerHTML = alerts.map((a, i) => {
            const pct = a.ActualResidualPercentage ?? a.InitialResidualPercentage ?? null;
            const sev = severityFor(pct);
            const device = [a.ProductBrand, a.ProductModel].filter(Boolean).join(' ') || '-';
            const location = [a.Office && a.Office.Description, a.Department].filter(Boolean).join(' / ') || '-';

            return `
                <tr data-row-index="${i}">
                    <td>
                        <div class="ta-severity">
                            <span class="ta-badge ${sev.cls}">${sev.label}</span>
                            <div class="ta-mini-bar"><div class="ta-mini-fill ${sev.cls}" style="width:${pct ?? 0}%"></div></div>
                        </div>
                    </td>
                    <td>
                        <div class="ta-device-cell"><i class="fas fa-print"></i> ${escapeHtml(device)}</div>
                    </td>
                    <td>${escapeHtml(getEquipmentId(a))}</td>
                    <td>${escapeHtml(a.SerialNumber || '-')}</td>
                    <td>${escapeHtml(location)}</td>
                    <td>${daysOpen(a.InitialDate)}</td>
                </tr>
            `;
        }).join('');
    }

    // --- Device drill-down modal ---
    const modalBackdrop = document.getElementById('ta-modal-backdrop');
    const modalBody = document.getElementById('ta-modal-body');

    function closeModal() {
        modalBackdrop.style.display = 'none';
    }

    function toneBar(label, pct) {
        if (pct === null || pct === undefined) return '';
        const cls = levelClass(pct);
        return `
            <div class="ta-toner-item">
                <div class="ta-toner-label">${escapeHtml(label)}</div>
                <div class="ta-level">
                    <div class="ta-level-bar"><div class="ta-level-fill ${cls}" style="width:${pct}%"></div></div>
                    <span class="ta-level-pct">${pct}%</span>
                </div>
            </div>
        `;
    }

    function renderMaintenanceAlerts(alerts) {
        const open = (alerts || []).filter(m => !m.Closed);
        if (!open.length) return '';
        return `
            <div class="ta-maint-section">
                <div class="ta-detail-label" style="margin-bottom:0.5rem;">Active Maintenance Alerts</div>
                ${open.map(m => `
                    <div class="ta-maint-row">
                        <span class="ta-badge critical">${escapeHtml(m.Code || '-')}</span>
                        <span>${escapeHtml(m.Description || '')}</span>
                        <span class="ta-maint-date">${escapeHtml((m.Opened || '').replace('T', ' ').slice(0, 16))}</span>
                    </div>
                `).join('')}
            </div>
        `;
    }

    async function openDeviceDrilldown(alert) {
        modalBody.innerHTML = '<div class="ta-loading">Loading device...</div>';
        modalBackdrop.style.display = 'flex';

        const device = [alert.ProductBrand, alert.ProductModel].filter(Boolean).join(' ') || 'Device';
        modalBody.innerHTML = `
            <h2><i class="fas fa-print"></i> ${escapeHtml(device)}</h2>
            <div class="ta-modal-sub">${escapeHtml(alert.SerialNumber || '')} &middot; ${escapeHtml(alert.CustomerDescription || alert.CustomerCode || '')}</div>
            <div id="ta-modal-detail"><div class="ta-loading">Loading live device data...</div></div>
        `;

        if (!alert.DeviceId) {
            document.getElementById('ta-modal-detail').innerHTML = '<div class="ta-empty">No device ID on this alert to drill into.</div>';
            return;
        }

        try {
            const params = new URLSearchParams({ deviceId: alert.DeviceId, dealerCode: state.dealerCode });
            const data = await fetchJson('api/get-device-detail.php?' + params.toString());
            const d = data.device;
            if (!d) {
                throw new Error('Device not found');
            }

            const online = !!d.IsOnline;
            const detail = document.getElementById('ta-modal-detail');
            detail.innerHTML = `
                <div class="ta-detail-grid">
                    <div class="ta-detail-item">
                        <div class="ta-detail-label">Status</div>
                        <div class="ta-detail-value"><span class="ta-online-dot ${online ? 'online' : 'offline'}"></span>${online ? 'Online' : 'Offline'}</div>
                    </div>
                    <div class="ta-detail-item"><div class="ta-detail-label">Office</div><div class="ta-detail-value">${escapeHtml(d.OfficeDescription || '-')}</div></div>
                    <div class="ta-detail-item"><div class="ta-detail-label">Department</div><div class="ta-detail-value">${escapeHtml(d.Department || '-')}</div></div>
                    <div class="ta-detail-item"><div class="ta-detail-label">Contact</div><div class="ta-detail-value">${escapeHtml(d.Contact || '-')}</div></div>
                    <div class="ta-detail-item"><div class="ta-detail-label">Firmware</div><div class="ta-detail-value">${escapeHtml(d.Firmware || '-')}</div></div>
                    <div class="ta-detail-item"><div class="ta-detail-label">Last Update</div><div class="ta-detail-value">${escapeHtml((d.LastUpdate || '').replace('T', ' ').slice(0, 16) || '-')}</div></div>
                    <div class="ta-detail-item"><div class="ta-detail-label">Installed</div><div class="ta-detail-value">${escapeHtml((d.Install || '').slice(0, 10) || '-')}</div></div>
                    <div class="ta-detail-item"><div class="ta-detail-label">Note</div><div class="ta-detail-value">${escapeHtml(d.Note || '-')}</div></div>
                </div>
                <div class="ta-toner-grid">
                    ${toneBar('Black Toner', d.BlackToner)}
                    ${toneBar('Cyan Toner', d.CyanToner)}
                    ${toneBar('Magenta Toner', d.MagentaToner)}
                    ${toneBar('Yellow Toner', d.YellowToner)}
                </div>
                ${renderMaintenanceAlerts(d.MaintenanceAlerts)}
            `;
        } catch (error) {
            document.getElementById('ta-modal-detail').innerHTML = `<div class="ta-empty">Failed to load device: ${escapeHtml(error.message)}</div>`;
        }
    }

    document.getElementById('ta-modal-close').addEventListener('click', closeModal);
    modalBackdrop.addEventListener('click', (e) => { if (e.target === modalBackdrop) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

    rowsEl.addEventListener('click', (e) => {
        const tr = e.target.closest('tr[data-row-index]');
        if (!tr) return;
        const alert = state.currentAlerts[Number(tr.dataset.rowIndex)];
        if (alert) openDeviceDrilldown(alert);
    });

    async function loadAlerts() {
        rowsEl.innerHTML = '<tr><td colspan="6" class="ta-loading">Loading toner alerts...</td></tr>';
        if (!state.dealerCode) return;

        const params = new URLSearchParams({
            dealerCode: state.dealerCode,
            supplyType: 'Toner',
            pageNumber: state.page,
            pageRows: state.pageRows,
        });
        if (state.customerCode) params.set('customerCode', state.customerCode);

        try {
            const data = await fetchJson('api/get-supply-alerts.php?' + params.toString());
            const alerts = data.alerts || [];
            state.total = data.total || alerts.length;
            renderRows(alerts);

            const pages = Math.max(1, Math.ceil(state.total / state.pageRows));
            summaryEl.textContent = `${state.total} toner alert${state.total === 1 ? '' : 's'}`;
            pageLabelEl.textContent = `Page ${state.page} of ${pages}`;
            prevBtn.disabled = state.page <= 1;
            nextBtn.disabled = state.page >= pages;
        } catch (error) {
            rowsEl.innerHTML = `<tr><td colspan="6" class="ta-empty">Failed to load toner alerts: ${escapeHtml(error.message)}</td></tr>`;
        }
    }

    function saveDealerCustomerPreference() {
        const opt = dealerSelect.selectedOptions[0];
        fetchJson('api/save-preferences.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                dealerCode: state.dealerCode,
                dealerId: state.dealerId,
                customerCode: state.customerCode,
                customerName: opt ? (customerSelect.selectedOptions[0]?.textContent || '') : '',
            }),
        }).catch(() => {});
    }

    dealerSelect.addEventListener('change', async () => {
        state.dealerCode = dealerSelect.value;
        state.dealerId = dealerSelect.selectedOptions[0]?.dataset.dealerId || '';
        state.customerCode = '';
        state.page = 1;
        saveDealerCustomerPreference();
        await loadCustomers();
        await loadAlerts();
    });

    customerSelect.addEventListener('change', async () => {
        state.customerCode = customerSelect.value;
        state.page = 1;
        saveDealerCustomerPreference();
        await loadAlerts();
    });

    document.getElementById('ta-refresh').addEventListener('click', loadAlerts);
    prevBtn.addEventListener('click', () => { if (state.page > 1) { state.page--; loadAlerts(); } });
    nextBtn.addEventListener('click', () => { state.page++; loadAlerts(); });

    (async function init() {
        await loadDealers();
        await loadCustomers();
        await loadAlerts();
    })();
})();
