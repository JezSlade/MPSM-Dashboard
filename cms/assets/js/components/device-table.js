/**
 * Device Table Component
 * Displays devices in a sortable, filterable table
 */

import { api } from '../api-client.js';
import { state } from '../state-manager.js';

export class DeviceTable {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            throw new Error(`Container "${containerId}" not found`);
        }

        this.options = {
            pageSize: 50,
            sortColumn: 'serial_number',
            sortDirection: 'asc',
            ...options
        };

        this.devices = [];
        this.filteredDevices = [];
        this.currentPage = 1;
        this.sortColumn = this.options.sortColumn;
        this.sortDirection = this.options.sortDirection;

        this.render();
        this.attachEventListeners();
    }

    /**
     * Load devices from API
     */
    async loadDevices(filters = {}) {
        try {
            state.set('devicesLoading', true);

            const response = await api.get('/get-devices.php', {
                cache: true,
                cacheTTL: 300000 // 5 minutes
            });

            if (response.success && response.data) {
                this.devices = response.data;
                this.applyFilters(filters);
                this.renderTable();

                state.set('devicesLoaded', true);
                state.set('deviceCount', this.devices.length);
            }
        } catch (error) {
            console.error('Failed to load devices:', error);
            this.showError('Failed to load devices. Please try again.');
        } finally {
            state.set('devicesLoading', false);
        }
    }

    /**
     * Apply filters to devices
     */
    applyFilters(filters) {
        this.filteredDevices = this.devices.filter(device => {
            // Search filter
            if (filters.search) {
                const search = filters.search.toLowerCase();
                const searchable = [
                    device.serial_number,
                    device.model_name,
                    device.ip_address,
                    device.location
                ].join(' ').toLowerCase();

                if (!searchable.includes(search)) {
                    return false;
                }
            }

            // Customer filter
            if (filters.customer_code && device.customer_code !== filters.customer_code) {
                return false;
            }

            // Status filter
            if (filters.is_uninstalled !== undefined) {
                if (device.is_uninstalled !== filters.is_uninstalled) {
                    return false;
                }
            }

            return true;
        });

        this.currentPage = 1;
        this.renderTable();
    }

    /**
     * Sort devices by column
     */
    sortBy(column) {
        if (this.sortColumn === column) {
            // Toggle direction
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = column;
            this.sortDirection = 'asc';
        }

        this.filteredDevices.sort((a, b) => {
            const aVal = a[column] || '';
            const bVal = b[column] || '';

            if (this.sortDirection === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });

        this.renderTable();
    }

    /**
     * Render table HTML
     */
    renderTable() {
        const start = (this.currentPage - 1) * this.options.pageSize;
        const end = start + this.options.pageSize;
        const pageDevices = this.filteredDevices.slice(start, end);
        const totalPages = Math.ceil(this.filteredDevices.length / this.options.pageSize);

        const html = `
            <div class="table-header">
                <div class="table-stats">
                    Showing ${start + 1}-${Math.min(end, this.filteredDevices.length)}
                    of ${this.filteredDevices.length} devices
                </div>
            </div>

            <table class="device-table">
                <thead>
                    <tr>
                        <th data-sort="serial_number">Serial Number ${this.sortIcon('serial_number')}</th>
                        <th data-sort="model_name">Model ${this.sortIcon('model_name')}</th>
                        <th data-sort="ip_address">IP Address ${this.sortIcon('ip_address')}</th>
                        <th data-sort="location">Location ${this.sortIcon('location')}</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${pageDevices.map(device => this.renderRow(device)).join('')}
                </tbody>
            </table>

            ${this.renderPagination(totalPages)}
        `;

        this.container.querySelector('.table-container').innerHTML = html;
        this.attachTableEventListeners();
    }

    /**
     * Render single device row
     */
    renderRow(device) {
        const statusClass = device.is_uninstalled ? 'status-inactive' : 'status-active';
        const statusText = device.is_uninstalled ? 'Inactive' : 'Active';

        return `
            <tr data-serial="${device.serial_number}">
                <td><a href="#" class="device-link">${device.serial_number}</a></td>
                <td>${device.model_name || 'N/A'}</td>
                <td>${device.ip_address || 'N/A'}</td>
                <td>${device.location || 'N/A'}</td>
                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                <td>
                    <button class="btn-sm btn-view" data-serial="${device.serial_number}">View</button>
                </td>
            </tr>
        `;
    }

    /**
     * Render pagination controls
     */
    renderPagination(totalPages) {
        if (totalPages <= 1) return '';

        const pages = [];
        const maxVisible = 7;

        if (totalPages <= maxVisible) {
            for (let i = 1; i <= totalPages; i++) {
                pages.push(i);
            }
        } else {
            if (this.currentPage <= 4) {
                pages.push(1, 2, 3, 4, 5, '...', totalPages);
            } else if (this.currentPage >= totalPages - 3) {
                pages.push(1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages);
            } else {
                pages.push(1, '...', this.currentPage - 1, this.currentPage, this.currentPage + 1, '...', totalPages);
            }
        }

        return `
            <div class="pagination">
                <button class="btn-page" data-page="${this.currentPage - 1}"
                        ${this.currentPage === 1 ? 'disabled' : ''}>Previous</button>

                ${pages.map(page => {
                    if (page === '...') {
                        return '<span class="page-ellipsis">...</span>';
                    }
                    const active = page === this.currentPage ? 'active' : '';
                    return `<button class="btn-page ${active}" data-page="${page}">${page}</button>`;
                }).join('')}

                <button class="btn-page" data-page="${this.currentPage + 1}"
                        ${this.currentPage === totalPages ? 'disabled' : ''}>Next</button>
            </div>
        `;
    }

    /**
     * Get sort icon for column
     */
    sortIcon(column) {
        if (this.sortColumn !== column) return '';
        return this.sortDirection === 'asc' ? '↑' : '↓';
    }

    /**
     * Render initial container
     */
    render() {
        this.container.innerHTML = `
            <div class="device-table-component">
                <div class="table-controls">
                    <input type="text" id="deviceSearch" placeholder="Search devices..." class="search-input">
                    <button id="refreshDevices" class="btn-primary">Refresh</button>
                </div>
                <div class="table-container">
                    <div class="loading">Loading devices...</div>
                </div>
            </div>
        `;
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
        const searchInput = this.container.querySelector('#deviceSearch');
        const refreshBtn = this.container.querySelector('#refreshDevices');

        // Search with debounce
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.applyFilters({ search: e.target.value });
            }, 300);
        });

        // Refresh button
        refreshBtn.addEventListener('click', () => {
            api.clearCache('/get-devices.php');
            this.loadDevices();
        });
    }

    /**
     * Attach table-specific event listeners (called after each render)
     */
    attachTableEventListeners() {
        // Sort headers
        this.container.querySelectorAll('th[data-sort]').forEach(th => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', () => {
                this.sortBy(th.dataset.sort);
            });
        });

        // Pagination buttons
        this.container.querySelectorAll('.btn-page').forEach(btn => {
            btn.addEventListener('click', () => {
                const page = parseInt(btn.dataset.page);
                if (page > 0) {
                    this.currentPage = page;
                    this.renderTable();
                }
            });
        });

        // View buttons
        this.container.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const serial = btn.dataset.serial;
                this.onDeviceClick(serial);
            });
        });

        // Device links
        this.container.querySelectorAll('.device-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const serial = link.closest('tr').dataset.serial;
                this.onDeviceClick(serial);
            });
        });
    }

    /**
     * Handle device click (can be overridden)
     */
    onDeviceClick(serial) {
        console.log('Device clicked:', serial);
        state.set('selectedDevice', serial);
    }

    /**
     * Show error message
     */
    showError(message) {
        this.container.querySelector('.table-container').innerHTML = `
            <div class="error-message">${message}</div>
        `;
    }
}
