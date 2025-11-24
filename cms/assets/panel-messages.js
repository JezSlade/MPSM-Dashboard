(() => {
    const tableBody = document.getElementById('message-table-body');
    const lastRefresh = document.getElementById('last-refresh');
    const refreshBtn = document.getElementById('refresh-btn');
    const limitSelect = document.getElementById('message-limit');
    const hoursSelect = document.getElementById('hours-window');
    const modal = document.getElementById('payload-modal');
    const modalClose = document.getElementById('modal-close');
    const payloadViewer = document.getElementById('payload-viewer');

    let timerId = null;

    async function fetchMessages() {
        try {
            const limit = limitSelect.value || '200';
            const hours = hoursSelect.value;

            const params = new URLSearchParams({ limit });
            if (hours) {
                params.set('hours', hours);
            }

            const response = await fetch(`api/get-panel-messages.php?${params.toString()}`, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            const payload = await response.json();
            if (!payload.success) {
                throw new Error(payload.error || 'Unknown API error');
            }

            renderRows(payload.messages || []);
            updateTimestamp();
        } catch (error) {
            renderError(error.message);
        }
    }

    function renderRows(rows) {
        if (!Array.isArray(rows) || rows.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6">No panel messages captured yet.</td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = rows.map((row) => {
            const customer = [
                row.customer_code ? `<strong>${escapeHtml(row.customer_code)}</strong>` : null,
                row.customer_description ? `<div>${escapeHtml(row.customer_description)}</div>` : null,
            ].filter(Boolean).join('');

            const displayName = row.display_name || row.panel_configuration || row.maintenance_alert_code || 'Alert';
            const alert = `
                <div><strong>${escapeHtml(displayName)}</strong></div>
                ${row.maintenance_alert_code ? `<div style="font-size: 0.85em; color: #64748b;">Code: ${escapeHtml(row.maintenance_alert_code)}</div>` : ''}
            `;

            const received = row.received_at ? new Date(row.received_at).toLocaleString() : '–';

            return `
                <tr data-id="${row.id}">
                    <td>${received}</td>
                    <td>${customer || '–'}</td>
                    <td>${escapeHtml(row.device_serial || '–')}</td>
                    <td>${alert || '–'}</td>
                    <td>${escapeHtml(row.panel_configuration || '–')}</td>
                    <td>
                        <button class="btn btn-secondary btn-small" data-action="view-payload">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderError(message) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    ${escapeHtml(message)}
                </td>
            </tr>
        `;
        if (lastRefresh) {
            lastRefresh.textContent = 'Error';
        }
    }

    function updateTimestamp() {
        if (lastRefresh) {
            const now = new Date();
            lastRefresh.textContent = `Last refresh: ${now.toLocaleTimeString()}`;
        }
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function attachHandlers() {
        refreshBtn?.addEventListener('click', () => {
            fetchMessages();
        });

        limitSelect?.addEventListener('change', fetchMessages);
        hoursSelect?.addEventListener('change', fetchMessages);

        tableBody?.addEventListener('click', (event) => {
            const target = event.target.closest('button[data-action="view-payload"]');
            if (!target) {
                return;
            }

            const row = target.closest('tr');
            if (!row) {
                return;
            }

            const id = row.getAttribute('data-id');
            showPayload(id);
        });

        modalClose?.addEventListener('click', hideModal);
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                hideModal();
            }
        });
    }

    async function showPayload(id) {
        try {
            const limit = limitSelect.value || '200';
            const hours = hoursSelect.value;
            const params = new URLSearchParams({ limit });
            if (hours) {
                params.set('hours', hours);
            }

            const response = await fetch(`api/get-panel-messages.php?${params.toString()}`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            });

            const payload = await response.json();
            if (!payload.success) {
                throw new Error(payload.error || 'Unknown API error');
            }

            const message = (payload.messages || []).find((row) => String(row.id) === String(id));
            if (!message) {
                throw new Error('Payload not found in current window');
            }

            const pretty = typeof message.payload === 'object'
                ? JSON.stringify(message.payload, null, 2)
                : String(message.payload);

            payloadViewer.textContent = pretty;
            modal.classList.add('active');
        } catch (error) {
            payloadViewer.textContent = `Error loading payload: ${error.message}`;
            modal.classList.add('active');
        }
    }

    function hideModal() {
        modal?.classList.remove('active');
    }

    function scheduleAutoRefresh() {
        if (timerId) {
            clearInterval(timerId);
        }
        timerId = setInterval(fetchMessages, 30000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        attachHandlers();
        fetchMessages();
        scheduleAutoRefresh();
    });
})();
