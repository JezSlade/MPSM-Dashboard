/**
 * Reusable Table and Pagination Utilities
 * Used by all dashboard cards for consistent UX
 */

const TableUtils = (function() {
    'use strict';

    /**
     * Create paginated sortable table
     * @param {Array} data - Array of data objects
     * @param {Array} columns - Column definitions [{key, label, render?, sortable?}]
     * @param {Object} options - {pageSize, onRowClick, className}
     * @returns {Object} {html, handlers}
     */
    function createPaginatedTable(data, columns, options = {}) {
        const pageSize = options.pageSize || 50;
        const currentPage = options.currentPage || 1;
        const totalPages = Math.ceil(data.length / pageSize);

        // Paginate data
        const startIdx = (currentPage - 1) * pageSize;
        const endIdx = startIdx + pageSize;
        const pageData = data.slice(startIdx, endIdx);

        // Build table HTML
        const tableClass = options.className || 'data-table';
        const html = `
            <div class="table-container">
                <div class="table-controls">
                    <div class="table-info">
                        Showing ${startIdx + 1}-${Math.min(endIdx, data.length)} of ${data.length}
                    </div>
                    <div class="table-pagination">
                        <select class="page-size-select">
                            <option value="25" ${pageSize === 25 ? 'selected' : ''}>25</option>
                            <option value="50" ${pageSize === 50 ? 'selected' : ''}>50</option>
                            <option value="100" ${pageSize === 100 ? 'selected' : ''}>100</option>
                            <option value="${data.length}">All</option>
                        </select>
                        <button class="btn-page" data-page="prev" ${currentPage === 1 ? 'disabled' : ''}>‹ Prev</button>
                        <span class="page-numbers">Page ${currentPage} of ${totalPages}</span>
                        <button class="btn-page" data-page="next" ${currentPage === totalPages ? 'disabled' : ''}>Next ›</button>
                    </div>
                </div>
                <table class="${tableClass}">
                    <thead>
                        <tr>
                            ${columns.map(col => `
                                <th class="${col.sortable !== false ? 'sortable' : ''}" data-key="${col.key}">
                                    ${col.label}
                                </th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${pageData.map((row, idx) => `
                            <tr class="data-row" data-index="${startIdx + idx}">
                                ${columns.map(col => {
                                    const value = row[col.key];
                                    const rendered = col.render ? col.render(value, row) : value;
                                    return `<td>${rendered}</td>`;
                                }).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        // Return HTML and setup function
        return {
            html,
            setup: function(container, callbacks = {}) {
                // Sort handling
                container.querySelectorAll('th.sortable').forEach(th => {
                    th.addEventListener('click', () => {
                        if (callbacks.onSort) {
                            callbacks.onSort(th.dataset.key);
                        }
                    });
                });

                // Pagination handling
                const pageSizeSelect = container.querySelector('.page-size-select');
                if (pageSizeSelect && callbacks.onPageSizeChange) {
                    pageSizeSelect.addEventListener('change', (e) => {
                        callbacks.onPageSizeChange(parseInt(e.target.value));
                    });
                }

                container.querySelectorAll('.btn-page').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const direction = btn.dataset.page;
                        if (callbacks.onPageChange) {
                            callbacks.onPageChange(direction);
                        }
                    });
                });

                // Row click handling
                if (callbacks.onRowClick) {
                    container.querySelectorAll('.data-row').forEach(row => {
                        row.addEventListener('click', () => {
                            const index = parseInt(row.dataset.index);
                            callbacks.onRowClick(data[index], index);
                        });
                    });
                }
            }
        };
    }

    /**
     * Format toner level with color coding
     */
    function renderTonerLevel(percent) {
        if (percent === null || percent === undefined) return '-';
        const level = parseInt(percent);
        let className = 'level-high';
        if (level < 20) className = 'level-critical';
        else if (level < 40) className = 'level-low';

        return `<span class="toner-level ${className}">${level}%</span>`;
    }

    /**
     * Format counter value
     */
    function renderCounter(value) {
        if (!value) return '-';
        return parseInt(value).toLocaleString();
    }

    /**
     * Format status badge
     */
    function renderStatus(status, isOffline) {
        const statusText = isOffline ? 'Offline' : (status || 'Online');
        const className = isOffline ? 'offline' : 'online';
        return `<span class="status-badge ${className}">${statusText}</span>`;
    }

    /**
     * Format date
     */
    function renderDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleString();
    }

    /**
     * Create expandable card section
     */
    function createExpandableCard(title, snapshotHtml, detailsHtml) {
        return `
            <div class="card-section">
                <div class="card-header-expandable">
                    <h3>${title}</h3>
                    <button class="btn-expand">
                        <span class="expand-icon">▼</span>
                        <span class="expand-text">Show Details</span>
                    </button>
                </div>
                <div class="card-snapshot">
                    ${snapshotHtml}
                </div>
                <div class="card-details" style="display: none;">
                    ${detailsHtml}
                </div>
            </div>
        `;
    }

    /**
     * Setup expand/collapse functionality
     */
    function setupExpandable(container) {
        container.querySelectorAll('.btn-expand').forEach(btn => {
            btn.addEventListener('click', function() {
                const section = this.closest('.card-section');
                const details = section.querySelector('.card-details');
                const icon = this.querySelector('.expand-icon');
                const text = this.querySelector('.expand-text');

                if (details.style.display === 'none') {
                    details.style.display = 'block';
                    icon.textContent = '▲';
                    text.textContent = 'Hide Details';
                } else {
                    details.style.display = 'none';
                    icon.textContent = '▼';
                    text.textContent = 'Show Details';
                }
            });
        });
    }

    // Public API
    return {
        createPaginatedTable,
        renderTonerLevel,
        renderCounter,
        renderStatus,
        renderDate,
        createExpandableCard,
        setupExpandable
    };
})();
