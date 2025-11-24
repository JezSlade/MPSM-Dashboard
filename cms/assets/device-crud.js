(() => {
    const root = document.getElementById('device-crud-root');
    if (!root) {
        return;
    }

    const endpoints = {
        list: 'api/device-list.php',
        create: 'api/device-create.php',
        update: 'api/device-update.php',
        delete: 'api/device-delete.php',
    };

    const tableBody = document.getElementById('device-table-body');
    const paginationInfo = document.getElementById('device-pagination-info');
    const prevButton = document.getElementById('device-prev-page');
    const nextButton = document.getElementById('device-next-page');
    const toast = document.getElementById('device-crud-toast');
    const refreshButton = document.getElementById('device-refresh');

    const filterForm = document.getElementById('device-filter-form');
    const filterCustomer = document.getElementById('filter-customer');
    const filterSearch = document.getElementById('filter-search');
    const filterPageRows = document.getElementById('filter-page-rows');
    const filterReset = document.getElementById('device-filter-reset');

    const createModal = document.getElementById('device-create-modal');
    const createForm = document.getElementById('device-create-form');
    const createOpen = document.getElementById('device-create-open');

    const updateModal = document.getElementById('device-update-modal');
    const updateForm = document.getElementById('device-update-form');
    const updateDeviceLabel = document.getElementById('update-device-label');
    const updateDeviceSerial = document.getElementById('update-device-serial');
    const updateDeviceCurrent = document.getElementById('update-device-current');
    const updateDeviceId = document.getElementById('update-device-id');
    const updateManaged = document.getElementById('update-managed');
    const updateAlerts = document.getElementById('update-alerts');
    const updateRepeatAlerts = document.getElementById('update-repeat-alerts');
    const createManaged = document.getElementById('create-managed');

    const state = {
        page: 1,
        pageRows: parseInt(filterPageRows?.value ?? '50', 10),
        search: '',
        customerCode: '',
        total: 0,
        totalPages: 1,
        loading: false,
        devices: [],
    };

    function showToast(message, variant = 'success') {
        if (!toast) {
            return;
        }
        toast.textContent = message;
        toast.className = `device-toast show ${variant}`;
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3200);
    }

    function buildQuery() {
        const params = new URLSearchParams();
        params.set('page', String(state.page));
        params.set('pageRows', String(state.pageRows));
        if (state.customerCode) {
            params.set('customerCode', state.customerCode);
        }
        if (state.search) {
            params.set('search', state.search);
        }
        params.set('sortColumn', 'AssetNumber');
        params.set('sortOrder', 'Asc');
        return params.toString();
    }

    function setLoading(isLoading) {
        state.loading = isLoading;
        if (isLoading) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7">
                        <div class="loading-row">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Loading devices...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    function resolveDeviceId(device) {
        return (
            device.DeviceId ||
            device.Id ||
            device.InstalledProductId ||
            device.id ||
            device.deviceId ||
            ''
        );
    }

    function resolveStatus(device) {
        const status = device.StatusDescription || device.Status || device.DeviceStatus || '';
        const isUninstalled = device.IsUninstalled || device.Uninstalled === true;

        if (isUninstalled) {
            return { label: 'Uninstalled', className: 'uninstalled', icon: 'fa-box-archive' };
        }

        if (!status) {
            return { label: 'Unknown', className: 'unknown', icon: 'fa-question-circle' };
        }

        const normalized = String(status).toLowerCase();
        if (normalized.includes('active') || normalized === '1') {
            return { label: status, className: 'active', icon: 'fa-circle-check' };
        }
        if (normalized.includes('inactive') || normalized === '0') {
            return { label: status, className: 'inactive', icon: 'fa-triangle-exclamation' };
        }

        return { label: status, className: 'unknown', icon: 'fa-info-circle' };
    }

    function formatDate(value) {
        if (!value) {
            return '—';
        }
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleString();
    }

    function renderDevices(devices) {
        state.devices = devices;

        if (!devices.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-muted">
                        No devices matched the current filters.
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = devices
            .map((device) => {
                const deviceId = resolveDeviceId(device);
                const status = resolveStatus(device);
                const customer = device.CustomerDescription || device.CustomerName || device.CustomerCode || '—';
                const brand = device.ProductBrand || device.Brand || '—';
                const model = device.ProductModel || device.Model || '—';
                const asset = device.AssetNumber || device.Asset || '—';
                const updated = device.LastUpdateDateTime
                    || device.LastUpdate
                    || device.SysUpdatedOn
                    || device.SysCreatedOn
                    || null;

                return `
                    <tr data-device-id="${deviceId}">
                        <td>
                            <strong>${device.SerialNumber || device.Serial || '—'}</strong><br>
                            <span class="text-muted">${deviceId}</span>
                        </td>
                        <td>${customer}</td>
                        <td>${brand} / ${model}</td>
                        <td>${asset}</td>
                        <td>
                            <span class="status-pill ${status.className}">
                                <i class="fas ${status.icon}"></i>
                                ${status.label}
                            </span>
                        </td>
                        <td>${formatDate(updated)}</td>
                        <td>
                            <div class="device-actions">
                                <button type="button" class="btn btn-secondary btn-small" data-action="edit-device">
                                    <i class="fas fa-pen"></i> Edit
                                </button>
                                <button type="button" class="btn btn-danger btn-small" data-action="delete-device">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            })
            .join('');
    }

    function updatePaginationMeta(total) {
        state.total = total;
        state.totalPages = Math.max(1, Math.ceil(total / state.pageRows));

        if (paginationInfo) {
            paginationInfo.textContent = `Page ${state.page} of ${state.totalPages} • ${total} devices`;
        }

        if (prevButton) {
            prevButton.disabled = state.page <= 1;
        }

        if (nextButton) {
            nextButton.disabled = state.page >= state.totalPages;
        }
    }

    async function loadDevices() {
        if (state.loading) {
            return;
        }

        try {
            setLoading(true);
            const response = await fetch(`${endpoints.list}?${buildQuery()}`, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.error || `Request failed with status ${response.status}`);
            }

            renderDevices(payload.devices || []);
            updatePaginationMeta(payload.total || 0);
        } catch (error) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-muted">
                        <i class="fas fa-exclamation-triangle"></i>
                        ${error.message}
                    </td>
                </tr>
            `;
            showToast(error.message || 'Unable to load devices', 'error');
        } finally {
            state.loading = false;
        }
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('show');
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('show');
        }
    }

    function bindModalDismiss() {
        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = button.getAttribute('data-modal-close');
                if (target) {
                    closeModal(target);
                }
            });
        });

        [createModal, updateModal].forEach((modal) => {
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.classList.remove('show');
                }
            });
        });
    }

    function resetCreateForm() {
        createForm?.reset();
        const defaultCustomer = document.getElementById('create-customer');
        if (defaultCustomer) {
            defaultCustomer.value = defaultCustomer.getAttribute('value') || '';
        }
    }

    async function handleCreate(event) {
        event.preventDefault();
        if (!createForm) {
            return;
        }

        const formData = new FormData(createForm);
        const payload = Object.fromEntries(formData.entries());

        if (createManaged) {
            payload.isManaged = createManaged.checked;
        }

        try {
            const response = await fetch(endpoints.create, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.error || `Create failed with status ${response.status}`);
            }

            showToast('Device created successfully');
            closeModal('device-create-modal');
            resetCreateForm();
            state.page = 1;
            await loadDevices();
        } catch (error) {
            showToast(error.message || 'Unable to create device', 'error');
        }
    }

    function populateUpdateForm(device) {
        if (!device) {
            return;
        }

        const deviceId = resolveDeviceId(device);
        const serial = device.SerialNumber || device.Serial || '-';
        const brand = device.ProductBrand || device.Brand || '-';
        const model = device.ProductModel || device.Model || '-';

        updateDeviceId.value = deviceId;

        if (updateDeviceSerial) {
            updateDeviceSerial.textContent = serial;
        }
        if (updateDeviceLabel) {
            updateDeviceLabel.textContent = deviceId;
        }
        if (updateDeviceCurrent) {
            updateDeviceCurrent.textContent = `${brand} / ${model}`;
        }

        document.getElementById('update-brand').value = '';
        document.getElementById('update-model').value = '';
        document.getElementById('update-standard-model-id').value = device.StandardModelId || '';
        document.getElementById('update-standard-model-clean').value = device.StandardModelClean || '';
        document.getElementById('update-project').value = device.ProjectId || '';
        document.getElementById('update-office').value = device.OfficeId || '';

        updateManaged.checked = Boolean(device.IsManaged);
        updateAlerts.checked = Boolean(device.IsGenerateAlert);
        updateRepeatAlerts.checked = Boolean(device.ManageRepeatedAlerts);
    }

    async function handleUpdate(event) {
        event.preventDefault();
        if (!updateForm) {
            return;
        }

        const formData = new FormData(updateForm);
        const payload = Object.fromEntries(formData.entries());

        payload.isManaged = updateManaged.checked;
        payload.isGenerateAlert = updateAlerts.checked;
        payload.manageRepeatedAlerts = updateRepeatAlerts.checked;

        try {
            const response = await fetch(endpoints.update, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.error || `Update failed with status ${response.status}`);
            }

            showToast('Device updated successfully');
            closeModal('device-update-modal');
            await loadDevices();
        } catch (error) {
            showToast(error.message || 'Unable to update device', 'error');
        }
    }

    async function handleDelete(deviceId) {
        if (!deviceId) {
            return;
        }

        const confirmation = window.confirm('Delete this device? This action cannot be undone.');
        if (!confirmation) {
            return;
        }

        try {
            const response = await fetch(endpoints.delete, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ deviceId }),
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.error || `Delete failed with status ${response.status}`);
            }

            showToast('Device deleted successfully', 'warning');
            await loadDevices();
        } catch (error) {
            showToast(error.message || 'Unable to delete device', 'error');
        }
    }

    function handleTableActions(event) {
        const button = event.target.closest('button[data-action]');
        if (!button) {
            return;
        }

        const row = button.closest('tr[data-device-id]');
        if (!row) {
            return;
        }

        const deviceId = row.getAttribute('data-device-id');
        const device = state.devices.find((item) => String(resolveDeviceId(item)) === String(deviceId));

        switch (button.getAttribute('data-action')) {
            case 'edit-device':
                if (device) {
                    populateUpdateForm(device);
                    openModal('device-update-modal');
                } else {
                    showToast('Unable to load device details for edit', 'error');
                }
                break;
            case 'delete-device':
                handleDelete(deviceId);
                break;
            default:
                break;
        }
    }

    function bindFilters() {
        filterForm?.addEventListener('submit', (event) => {
            event.preventDefault();
            state.search = filterSearch?.value.trim() ?? '';
            state.customerCode = filterCustomer?.value.trim() ?? '';
            state.pageRows = parseInt(filterPageRows?.value ?? '50', 10);
            state.page = 1;
            loadDevices();
        });

        filterReset?.addEventListener('click', () => {
            if (filterCustomer) filterCustomer.value = '';
            if (filterSearch) filterSearch.value = '';
            if (filterPageRows) filterPageRows.value = '50';
            state.search = '';
            state.customerCode = '';
            state.pageRows = 50;
            state.page = 1;
            loadDevices();
        });

        prevButton?.addEventListener('click', () => {
            if (state.page > 1) {
                state.page -= 1;
                loadDevices();
            }
        });

        nextButton?.addEventListener('click', () => {
            if (state.page < state.totalPages) {
                state.page += 1;
                loadDevices();
            }
        });
    }

    function bindEvents() {
        tableBody?.addEventListener('click', handleTableActions);

        createOpen?.addEventListener('click', () => {
            resetCreateForm();
            openModal('device-create-modal');
        });

        createForm?.addEventListener('submit', handleCreate);
        updateForm?.addEventListener('submit', handleUpdate);
        refreshButton?.addEventListener('click', () => loadDevices());
    }

    function init() {
        bindModalDismiss();
        bindFilters();
        bindEvents();
        loadDevices();
    }

    init();
})();
