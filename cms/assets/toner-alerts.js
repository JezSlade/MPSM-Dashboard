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
        alertRequest: 0,
        sessionExpired: false,
    };

    const dealerSelect = document.getElementById('ta-dealer');
    const customerSelect = document.getElementById('ta-customer');
    const rowsEl = document.getElementById('ta-rows');
    const summaryEl = document.getElementById('ta-summary');
    const pageLabelEl = document.getElementById('ta-page-label');
    const prevBtn = document.getElementById('ta-prev');
    const nextBtn = document.getElementById('ta-next');
    const signInNotice = document.getElementById('ta-sign-in');
    const refreshBtn = document.getElementById('ta-refresh');
    const themeToggle = document.getElementById('ta-theme-toggle');
    const pageTheme = document.documentElement.dataset;
    const ageQueue = new Map();
    const ageCache = new Map();
    let ageObserver;
    let ageRequests = 0;
    let detailRow;

    function updateThemeToggle() {
        const isLight = pageTheme.tonerTheme === 'light';
        themeToggle.querySelector('i').className = isLight ? 'fas fa-moon' : 'fas fa-sun';
        themeToggle.querySelector('span').textContent = isLight ? 'Dark mode' : 'Light mode';
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            pageTheme.tonerTheme = pageTheme.tonerTheme === 'light' ? 'dark' : 'light';
            updateThemeToggle();
            try {
                localStorage.setItem('mpsm.tonerAlerts.theme', pageTheme.tonerTheme);
            } catch {}
        });
        updateThemeToggle();
    }

    function clearResults() {
        ageObserver?.disconnect();
        ageQueue.clear();
        state.currentAlerts = [];
        state.total = 0;
        summaryEl.textContent = '';
        pageLabelEl.textContent = '';
        prevBtn.disabled = true;
        nextBtn.disabled = true;
    }

    async function fetchTonerJson(url, options) {
        try {
            return await fetchJson(url, options);
        } catch (error) {
            if (error.status === 401 && !state.sessionExpired) {
                state.sessionExpired = true;
                state.alertRequest++;
                clearResults();
                rowsEl.textContent = '';
                dealerSelect.disabled = true;
                customerSelect.disabled = true;
                refreshBtn.disabled = true;
                rowsEl.closest('.ta-table-wrap').hidden = true;
                pageLabelEl.closest('.ta-pagination').hidden = true;
                if (deviceDialog.open) deviceDialog.close();
                signInNotice.hidden = false;
                signInNotice.querySelector('a').focus();
            }
            throw error;
        }
    }

    function levelClass(pct) {
        if (pct === null || pct === undefined) return '';
        if (pct <= 10) return 'critical';
        if (pct <= 25) return 'low';
        return '';
    }

    async function loadDealers() {
        try {
            const data = await fetchTonerJson('api/get-dealers.php');
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
            if (state.sessionExpired) return;
            dealerSelect.innerHTML = '<option value="">Failed to load dealers</option>';
            showToast('Failed to load dealers: ' + error.message, 'error');
        }
    }

    async function loadCustomers() {
        customerSelect.innerHTML = '<option value="">All Customers</option>';
        if (!state.dealerCode) return;
        try {
            const data = await fetchTonerJson('api/get-customers.php?dealerCode=' + encodeURIComponent(state.dealerCode));
            (data.customers || []).forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.Code;
                opt.textContent = c.Description ? `${c.Description} (${c.Code})` : c.Code;
                if (c.Code === state.customerCode) opt.selected = true;
                customerSelect.appendChild(opt);
            });
        } catch (error) {
            if (state.sessionExpired) return;
            showToast('Failed to load customers: ' + error.message, 'error');
        }
    }

    function severityFor(pct) {
        const cls = levelClass(pct);
        if (cls === 'critical') return { cls, label: 'Critical' };
        if (cls === 'low') return { cls, label: 'Low' };
        return { cls: 'ok', label: 'OK' };
    }

    const dayInMilliseconds = 24 * 60 * 60 * 1000;
    const readingFormat = new Intl.DateTimeFormat(undefined, {
        month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit',
        timeZoneName: 'short',
    });

    function dateTimestamp(value) {
        if (typeof value !== 'string' || value.startsWith('0001-01-01')) return NaN;
        // MySQL panel history uses a space between date and time.
        return Date.parse(value.replace(' ', 'T'));
    }

    function readingStatus(value) {
        const timestamp = dateTimestamp(value);
        if (!Number.isFinite(timestamp)) return 'Reading time unavailable';
        const date = readingFormat.format(timestamp);
        return Date.now() - timestamp >= dayInMilliseconds ? `Stale · ${date}` : date;
    }

    function renderAge(dates = []) {
        const now = Date.now();
        const timestamps = dates.map(dateTimestamp).filter(value => Number.isFinite(value) && value <= now);
        if (!timestamps.length) return '<span class="ta-toner-part-meta">Unknown</span>';
        const timestamp = Math.min(...timestamps);
        const days = Math.floor((now - timestamp) / dayInMilliseconds);
        const label = timestamps.length > 1 ? 'Oldest verified opening' : 'Verified opening';
        return `<time datetime="${new Date(timestamp).toISOString()}">${days} d</time>
                <div class="ta-toner-part-meta">${label}</div>`;
    }

    function renderHistoryAge(alert, lowRuns) {
        const now = Date.now();
        const observed = (alert.TonerAlerts ?? [alert]).flatMap(cartridge => {
            const run = lowRuns[cartridge.TonerColor];
            if (!run) return [];
            const first = dateTimestamp(run.firstLowAt);
            const last = dateTimestamp(run.lastLowAt);
            const above = dateTimestamp(run.aboveBeforeAt);
            const listedReading = dateTimestamp(cartridge.TonerReadingDate);
            if (!Number.isFinite(first) || !Number.isFinite(last) || first > last || last > now
                || listedReading - last > 3 * dayInMilliseconds) return [];
            const stale = now - last >= dayInMilliseconds;
            const asOf = stale ? last : now;
            const lower = Math.floor((asOf - first) / dayInMilliseconds);
            const upper = Number.isFinite(above) && above < first
                ? Math.floor((asOf - above) / dayInMilliseconds) : null;
            return [{ lower, upper, stale }];
        });
        observed.sort((a, b) => b.lower - a.lower);
        const age = observed[0];
        if (!age) return renderAge();
        const { lower, upper, stale } = age;
        let days = lower === 0 ? 'Low recorded' : `≥ ${lower} d`;
        if (upper !== null) {
            days = lower === upper ? `~ ${lower} d` : `${lower}–${upper} d`;
        }
        const source = stale ? 'Estimate at last reading' : 'History estimate';
        return `<span>${days}</span><div class="ta-toner-part-meta">${source}</div>`;
    }

    async function loadHistoryAge(row, alert, dealerCode) {
        const ageCell = row.querySelector('.ta-alert-age');
        if (ageCell.dataset.verified) return;
        const params = new URLSearchParams({ deviceId: alert.DeviceId, dealerCode, range: '90', summary: '1' });
        const key = params.toString();
        ageCell.textContent = 'Checking history…';
        let markup = renderAge();
        try {
            let cached = ageCache.get(key);
            if (!cached || cached.expires <= Date.now()) {
                const data = await fetchTonerJson('api/get-toner-history.php?' + key, { timeoutMs: 22000 });
                cached = { runs: data.lowRuns ?? {}, expires: Date.now() + 5 * 60 * 1000 };
                if (ageCache.size >= 200) ageCache.delete(ageCache.keys().next().value);
                ageCache.set(key, cached);
            }
            markup = renderHistoryAge(alert, cached.runs);
        } catch {
            markup = '<span class="ta-toner-part-meta">History unavailable</span>';
        }
        if (!row.isConnected || ageCell.dataset.verified) return;
        ageCell.innerHTML = markup;
        if (detailRow === row && deviceDialog.open) {
            const detailAge = modalBody.querySelector('.ta-modal-age');
            if (detailAge) detailAge.innerHTML = markup;
        }
    }

    function drainAgeQueue() {
        while (ageRequests < 2 && ageQueue.size) {
            const [row, { alert, dealerCode }] = ageQueue.entries().next().value;
            ageQueue.delete(row);
            if (!row.isConnected || state.sessionExpired) continue;
            ageObserver?.unobserve(row);
            ageRequests++;
            loadHistoryAge(row, alert, dealerCode).finally(() => {
                ageRequests--;
                drainAgeQueue();
            });
        }
    }

    function observeAlertAges(alerts) {
        if (typeof IntersectionObserver !== 'function') return;
        const dealerCode = state.dealerCode;
        ageObserver = new IntersectionObserver(entries => {
            for (const entry of entries) {
                const row = entry.target;
                if (entry.isIntersecting && row.isConnected) {
                    const alert = alerts[Number(row.dataset.rowIndex)];
                    ageQueue.set(row, { alert, dealerCode });
                } else {
                    ageQueue.delete(row);
                }
            }
            drainAgeQueue();
        });
        rowsEl.querySelectorAll('tr[data-row-index]').forEach((row, index) => {
            const alert = alerts[index];
            const hasChartColor = (alert.TonerAlerts ?? [alert]).some(cartridge =>
                ['black', 'cyan', 'magenta', 'yellow'].includes(cartridge.TonerColor));
            if (alert.DeviceId && hasChartColor) ageObserver.observe(row);
        });
    }

    function deviceLabel(brand, model, fallback = '-') {
        const product = (model || '').trim();
        const make = (brand || '').trim();
        if (make && product.toLowerCase().startsWith(make.toLowerCase() + ' ')) return product;
        return [make, product].filter(Boolean).join(' ') || fallback;
    }

    function renderCartridge(alert) {
        const level = alert.Percentage;
        const pct = level == null || level === '' ? NaN : Number(level);
        const validLevel = Number.isFinite(pct) && pct >= 0 && pct <= 100;
        const sev = validLevel ? severityFor(pct) : { cls: 'ok', label: 'Unknown' };
        const color = alert.TonerColor
            ? alert.TonerColor.charAt(0).toUpperCase() + alert.TonerColor.slice(1)
            : 'Unknown color';
        return `
            <div class="ta-cartridge">
                <div class="ta-severity">
                    <span class="ta-badge ${sev.cls}">${sev.label}</span>
                    <span>${escapeHtml(color)} ${validLevel ? `${pct}%` : '· level unavailable'}</span>
                </div>
                <div class="ta-toner-part-meta">${escapeHtml(readingStatus(alert.TonerReadingDate))}</div>
            </div>
        `;
    }

    function renderRows(alerts) {
        state.currentAlerts = alerts;

        if (!alerts.length) {
            rowsEl.innerHTML = '<tr><td colspan="6" class="ta-empty">No toner alerts found.</td></tr>';
            return;
        }

        rowsEl.innerHTML = alerts.map((a, i) => {
            const device = deviceLabel(a.ProductBrand, a.ProductModel);
            const idSource = a.TonerEquipmentIdFromDepartment ? ' title="From device department"' : '';
            const cartridges = a.TonerAlerts ?? [{
                TonerColor: a.TonerColor,
                Percentage: a.ActualResidualPercentage ?? a.InitialResidualPercentage,
                TonerReadingDate: a.TonerReadingDate,
            }];

            return `
                <tr role="row" data-row-index="${i}">
                    <td role="cell" class="ta-device-alerts">${cartridges.map(renderCartridge).join('')}</td>
                    <td role="cell" class="ta-device-model">
                        <button type="button" class="ta-device-cell" aria-describedby="ta-device-serial-${i}">
                            <i class="fas fa-print" aria-hidden="true"></i><span>${escapeHtml(device)}</span>
                        </button>
                    </td>
                    <td role="cell" class="ta-device-id"${idSource}>
                        <span class="ta-mobile-label" aria-hidden="true">Systel Equip ID</span>${escapeHtml(a.TonerEquipmentId || '-')}
                    </td>
                    <td role="cell" id="ta-device-serial-${i}">
                        <span class="ta-mobile-label" aria-hidden="true">Serial Number</span>${escapeHtml(a.SerialNumber || '-')}
                    </td>
                    <td role="cell">
                        <span class="ta-mobile-label" aria-hidden="true">Location</span>${escapeHtml(a.TonerLocation || 'Location unavailable')}
                    </td>
                    <td role="cell">
                        <span class="ta-mobile-label" aria-hidden="true">Days Open</span><div class="ta-alert-age">${renderAge()}</div>
                    </td>
                </tr>
            `;
        }).join('');
        observeAlertAges(alerts);
    }

    // --- Device drill-down modal ---
    const deviceDialog = document.getElementById('ta-device-dialog');
    const modalBody = document.getElementById('ta-modal-body');
    let disposeHistory;

    function connectTonerHistory(section, deviceParams) {
        const content = section.querySelector('.ta-history-body');
        let connecting = false;
        async function openHistory() {
            if (!section.open || connecting) return;
            connecting = true;
            content.textContent = 'Loading toner history…';
            try {
                const moduleUrl = new URL(body.dataset.tonerHistoryUrl, document.baseURI);
                const { mountTonerHistory } = await import(moduleUrl.href);
                if (!section.isConnected || !deviceDialog.open || !section.open) return;
                disposeHistory = mountTonerHistory(section, deviceParams, fetchTonerJson);
                section.removeEventListener('toggle', openHistory);
            } catch {
                content.textContent = 'Toner history is temporarily unavailable.';
            } finally {
                connecting = false;
            }
        }
        section.addEventListener('toggle', openHistory);
    }

    function tonerPartsFor(partsByColor, color) {
        if (!partsByColor || typeof partsByColor !== 'object') return [];

        const colorKey = color.toLowerCase();
        const options = partsByColor[colorKey]
            ?? partsByColor[color]
            ?? partsByColor[color.toUpperCase()]
            ?? [];

        return Array.isArray(options) ? options : [];
    }

    function renderTonerParts(options) {
        const validOptions = options.filter(option => option && option.partNumber);
        if (!validOptions.length) {
            return '<div class="ta-toner-parts ta-toner-parts--empty">Part number not found</div>';
        }

        return `
            <div class="ta-toner-parts" aria-label="Compatible OEM toner part numbers">
                ${validOptions.map(option => {
                    const details = [option.yield, option.region].filter(Boolean).join(' / ');
                    return `
                        <div class="ta-toner-part">
                            <div class="ta-toner-part-number">${escapeHtml(option.partNumber)}</div>
                            ${details ? `<div class="ta-toner-part-meta">${escapeHtml(details)}</div>` : ''}
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    function toneBar(label, pct, partOptions = []) {
        if (pct === null || pct === undefined) return '';
        const cls = levelClass(pct);
        return `
            <div class="ta-toner-item">
                <div class="ta-toner-label">${escapeHtml(label)}</div>
                <div class="ta-level">
                    <div class="ta-level-bar"><div class="ta-level-fill ${cls}" style="width:${pct}%"></div></div>
                    <span class="ta-level-pct">${pct}%</span>
                </div>
                ${renderTonerParts(partOptions)}
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

    async function openDeviceDrilldown(alert, row) {
        detailRow = row;
        const device = deviceLabel(alert.ProductBrand, alert.ProductModel, 'Device');
        modalBody.innerHTML = `
            <h2 id="ta-modal-title"><i class="fas fa-print" aria-hidden="true"></i> ${escapeHtml(device)}</h2>
            <div class="ta-modal-sub">${escapeHtml(alert.SerialNumber || '')} &middot; ${escapeHtml(alert.CustomerDescription || alert.CustomerCode || '')}</div>
            <div id="ta-modal-detail"><div class="ta-loading">Loading live device data...</div></div>
        `;
        deviceDialog.showModal();
        const detail = document.getElementById('ta-modal-detail');

        if (!alert.DeviceId) {
            detail.innerHTML = '<div class="ta-empty">No device ID on this alert to drill into.</div>';
            return;
        }

        try {
            const params = new URLSearchParams({ deviceId: alert.DeviceId, dealerCode: state.dealerCode });
            if (alert.ProductBrand) params.set('brand', alert.ProductBrand);
            if (alert.ProductModel) params.set('model', alert.ProductModel);
            const data = await fetchTonerJson('api/get-device-detail.php?' + params.toString());
            const d = data.device;
            if (!d) {
                throw new Error('Device not found');
            }

            const online = !!d.IsOnline;
            const partsByColor = data.tonerPartNumbers?.colors || data.tonerPartNumbers || {};
            const openedByColor = data.tonerAlertOpenedDates || {};
            const openingDates = (alert.TonerAlerts ?? [alert]).map(cartridge => openedByColor[cartridge.TonerColor]);
            const ageCell = row.querySelector('.ta-alert-age');
            if (openingDates.some(date => {
                const time = dateTimestamp(date);
                return Number.isFinite(time) && time <= Date.now();
            })) {
                ageCell.dataset.verified = '1';
                ageCell.innerHTML = renderAge(openingDates);
            }
            const ageMarkup = ageCell.innerHTML;
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
                    <div class="ta-detail-item"><div class="ta-detail-label">Last Update</div><div class="ta-detail-value">${escapeHtml(readingStatus(d.LastUpdate))}</div></div>
                    <div class="ta-detail-item"><div class="ta-detail-label">Days Open</div><div class="ta-detail-value ta-modal-age">${ageMarkup}</div></div>
                    <div class="ta-detail-item"><div class="ta-detail-label">Installed</div><div class="ta-detail-value">${escapeHtml((d.Install || '').slice(0, 10) || '-')}</div></div>
                    <div class="ta-detail-item"><div class="ta-detail-label">Note</div><div class="ta-detail-value">${escapeHtml(d.Note || '-')}</div></div>
                </div>
                <div class="ta-toner-grid">
                    ${toneBar('Black Toner', d.BlackToner, tonerPartsFor(partsByColor, 'Black'))}
                    ${toneBar('Cyan Toner', d.CyanToner, tonerPartsFor(partsByColor, 'Cyan'))}
                    ${toneBar('Magenta Toner', d.MagentaToner, tonerPartsFor(partsByColor, 'Magenta'))}
                    ${toneBar('Yellow Toner', d.YellowToner, tonerPartsFor(partsByColor, 'Yellow'))}
                    ${toneBar('White Toner', d.WhiteToner, tonerPartsFor(partsByColor, 'White'))}
                </div>
                <details class="ta-history">
                    <summary>Toner History</summary>
                    <div class="ta-history-body"></div>
                </details>
                ${renderMaintenanceAlerts(d.MaintenanceAlerts)}
            `;
            connectTonerHistory(detail.querySelector('.ta-history'), {
                deviceId: params.get('deviceId'),
                dealerCode: params.get('dealerCode'),
            });
        } catch (error) {
            if (state.sessionExpired) return;
            detail.innerHTML = `<div class="ta-empty">Failed to load device: ${escapeHtml(error.message)}</div>`;
        }
    }

    document.getElementById('ta-modal-close').addEventListener('click', () => deviceDialog.close());
    deviceDialog.addEventListener('close', () => {
        detailRow = undefined;
        disposeHistory?.();
        disposeHistory = undefined;
    });
    deviceDialog.addEventListener('click', (event) => {
        if (event.target !== deviceDialog) return;
        const bounds = deviceDialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right
            || event.clientY < bounds.top || event.clientY > bounds.bottom) {
            deviceDialog.close();
        }
    });

    rowsEl.addEventListener('click', (e) => {
        const tr = e.target.closest('tr[data-row-index]');
        if (!tr) return;
        const alert = state.currentAlerts[Number(tr.dataset.rowIndex)];
        if (alert) openDeviceDrilldown(alert, tr);
    });

    async function loadAlerts() {
        if (state.sessionExpired) return;
        const request = ++state.alertRequest;
        clearResults();
        if (!state.dealerCode) {
            renderRows([]);
            return;
        }
        rowsEl.innerHTML = '<tr><td colspan="6" class="ta-loading">Loading toner alerts...</td></tr>';

        const params = new URLSearchParams({
            dealerCode: state.dealerCode,
            supplyType: 'Toner',
            currentOnly: '1',
            groupByDevice: '1',
            sortColumn: 'InitialDate',
            sortOrder: 'Asc',
            pageNumber: state.page,
            pageRows: state.pageRows,
        });
        if (state.customerCode) params.set('customerCode', state.customerCode);

        try {
            const data = await fetchTonerJson('api/get-supply-alerts.php?' + params.toString());
            if (request !== state.alertRequest) return;
            const alerts = data.alerts || [];
            state.total = data.total ?? alerts.length;
            const pages = Math.max(1, Math.ceil(state.total / state.pageRows));
            if (state.page > pages) {
                state.page = pages;
                return loadAlerts();
            }
            renderRows(alerts);

            const alertCount = data.alertTotal ?? state.total;
            summaryEl.textContent = `${state.total} device${state.total === 1 ? '' : 's'} · `
                + `${alertCount} toner alert${alertCount === 1 ? '' : 's'}`;
            pageLabelEl.textContent = `Page ${state.page} of ${pages}`;
            prevBtn.disabled = state.page <= 1;
            nextBtn.disabled = state.page >= pages;
        } catch (error) {
            if (request !== state.alertRequest) return;
            rowsEl.innerHTML = `<tr><td colspan="6" class="ta-empty">Failed to load toner alerts: ${escapeHtml(error.message)}</td></tr>`;
        }
    }

    function saveDealerCustomerPreference() {
        const opt = dealerSelect.selectedOptions[0];
        return fetchTonerJson('api/save-preferences.php', {
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
        await saveDealerCustomerPreference();
        if (state.sessionExpired) return;
        await loadCustomers();
        await loadAlerts();
    });

    customerSelect.addEventListener('change', async () => {
        state.customerCode = customerSelect.value;
        state.page = 1;
        await saveDealerCustomerPreference();
        await loadAlerts();
    });

    refreshBtn.addEventListener('click', loadAlerts);
    prevBtn.addEventListener('click', () => { if (state.page > 1) { state.page--; loadAlerts(); } });
    nextBtn.addEventListener('click', () => { state.page++; loadAlerts(); });

    (async function init() {
        await loadDealers();
        if (state.sessionExpired) return;
        await loadCustomers();
        await loadAlerts();
    })();
})();

/*
CHANGELOG
2026-11-25 Codex
- Displayed all compatible OEM toner part numbers with yield and region in the device drill-down.
- Sent alert brand and model to the device-detail endpoint for exact catalog matching.
- Limited the list to current toner alerts so completed lifecycle records no longer inflate pagination.
- Sorted current toner alerts oldest-first to align with the Days Open column.
- Calculated Days Open from the latest matching low/empty panel-history event instead of the supply lifecycle InitialDate.
2026-11-27 Codex
- Show toner color, percentage, and timestamp on each row and flag stale readings.
- Show location and equipment ID from the available data and label the source of alert age.
- Render one row per device with all affected cartridges and separate device and alert counts.
- Add labels for the mobile device cards and support keyboard and touch access to device details.
2026-11-28 Codex
- Clear stale results and show a sign-in action when the session ends.
- Show full elapsed days only after a matching active opening date is verified in the device details.
- Load the toner history chart when its drill-down section is expanded.
- Remember the Toner Alerts theme across visits and mobile or desktop layout changes.
- Estimate Days Open for visible rows from observed toner history and retain verified openings when available.
*/
