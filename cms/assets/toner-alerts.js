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
    };

    const dealerSelect = document.getElementById('ta-dealer');
    const customerSelect = document.getElementById('ta-customer');
    const rowsEl = document.getElementById('ta-rows');
    const summaryEl = document.getElementById('ta-summary');
    const pageLabelEl = document.getElementById('ta-page-label');
    const prevBtn = document.getElementById('ta-prev');
    const nextBtn = document.getElementById('ta-next');

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

    function renderRows(alerts) {
        if (!alerts.length) {
            rowsEl.innerHTML = '<tr><td colspan="7" class="ta-empty">No toner alerts found.</td></tr>';
            return;
        }

        rowsEl.innerHTML = alerts.map(a => {
            const pct = a.ActualResidualPercentage ?? a.InitialResidualPercentage ?? null;
            const cls = levelClass(pct);
            const device = [a.ProductBrand, a.ProductModel].filter(Boolean).join(' ') || '-';
            const location = [a.Office && a.Office.Description, a.Department].filter(Boolean).join(' / ') || '-';
            const supply = [a.ColorType, a.SupplyTypeDescription || a.SupplyType].filter(Boolean).join(' ') || '-';
            const updated = (a.ActualDate || a.InitialDate || '').replace('T', ' ').slice(0, 16);

            return `
                <tr>
                    <td>${escapeHtml(a.CustomerDescription || a.CustomerCode || '-')}</td>
                    <td>${escapeHtml(device)} <span style="color:var(--ta-text-dim)">${escapeHtml(a.SerialNumber || '')}</span></td>
                    <td>${escapeHtml(location)}</td>
                    <td>${escapeHtml(supply)}</td>
                    <td>
                        <div class="ta-level">
                            <div class="ta-level-bar"><div class="ta-level-fill ${cls}" style="width:${pct ?? 0}%"></div></div>
                            <span class="ta-level-pct">${pct !== null ? pct + '%' : '-'}</span>
                        </div>
                    </td>
                    <td>${escapeHtml(a.Warning || '')}</td>
                    <td>${escapeHtml(updated)}</td>
                </tr>
            `;
        }).join('');
    }

    async function loadAlerts() {
        rowsEl.innerHTML = '<tr><td colspan="7" class="ta-loading">Loading toner alerts...</td></tr>';
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
            rowsEl.innerHTML = `<tr><td colspan="7" class="ta-empty">Failed to load toner alerts: ${escapeHtml(error.message)}</td></tr>`;
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
