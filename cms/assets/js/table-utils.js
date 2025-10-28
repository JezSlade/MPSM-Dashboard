/**
 * TableUtils - reusable paginated, sortable, searchable table renderer.
 * Follows Engineering Standards: no third-party dependencies, reuse-friendly.
 */

const TableUtils = (function () {
    'use strict';

    function createState(rows, options) {
        return {
            originalRows: Array.isArray(rows) ? rows : [],
            filteredRows: Array.isArray(rows) ? rows.slice() : [],
            currentPage: 1,
            pageSize: options.pageSize || 50,
            sortColumn: options.defaultSort?.column || null,
            sortDirection: options.defaultSort?.direction || 'asc',
            searchTerm: ''
        };
    }

    function applySearch(state, columns) {
        if (!state.searchTerm) {
            state.filteredRows = state.originalRows.slice();
            return;
        }

        const term = state.searchTerm.toLowerCase();
        state.filteredRows = state.originalRows.filter(row => {
            return columns.some(col => {
                if (col.hidden) return false;
                const value = typeof col.accessor === 'function'
                    ? col.accessor(row)
                    : row[col.id];
                return String(value ?? '').toLowerCase().includes(term);
            });
        });
    }

    function applySort(state, columns) {
        if (!state.sortColumn) return;
        const column = columns.find(col => col.id === state.sortColumn);
        if (!column || column.sortable === false) return;

        const directionFactor = state.sortDirection === 'desc' ? -1 : 1;
        const compare = column.compare || defaultCompare;

        state.filteredRows.sort((a, b) => {
            const valueA = typeof column.accessor === 'function'
                ? column.accessor(a)
                : a[column.id];
            const valueB = typeof column.accessor === 'function'
                ? column.accessor(b)
                : b[column.id];
            return compare(valueA, valueB) * directionFactor;
        });
    }

    function defaultCompare(a, b) {
        const valueA = a ?? '';
        const valueB = b ?? '';

        if (!isNaN(valueA) && !isNaN(valueB)) {
            return Number(valueA) - Number(valueB);
        }

        return String(valueA).localeCompare(String(valueB), undefined, { sensitivity: 'base' });
    }

    function getPagedRows(state) {
        const start = (state.currentPage - 1) * state.pageSize;
        return state.filteredRows.slice(start, start + state.pageSize);
    }

    function renderSearch(container, state, columns, rerender) {
        const searchInput = document.createElement('input');
        searchInput.type = 'search';
        searchInput.placeholder = 'Search...';
        searchInput.className = 'table-search';

        searchInput.addEventListener('input', (event) => {
            state.searchTerm = event.target.value.trim();
            state.currentPage = 1;
            applySearch(state, columns);
            applySort(state, columns);
            rerender();
        });

        container.appendChild(searchInput);
    }

    function renderTableHeader(table, columns, state, rerender) {
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');

        columns.forEach(column => {
            if (column.hidden) return;
            const th = document.createElement('th');
            th.textContent = column.label || column.id;
            if (column.sortable !== false) {
                th.classList.add('sortable');
                th.addEventListener('click', () => {
                    if (state.sortColumn === column.id) {
                        state.sortDirection = state.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        state.sortColumn = column.id;
                        state.sortDirection = 'asc';
                    }
                    applySort(state, columns);
                    state.currentPage = 1;
                    rerender();
                });

                if (state.sortColumn === column.id) {
                    th.dataset.direction = state.sortDirection;
                }
            }

            headerRow.appendChild(th);
        });

        thead.appendChild(headerRow);
        table.appendChild(thead);
    }

    function renderTableBody(table, columns, rows, rowClick) {
        const tbody = document.createElement('tbody');

        if (!rows.length) {
            const emptyRow = document.createElement('tr');
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = columns.filter(col => !col.hidden).length || 1;
            emptyCell.className = 'table-empty';
            emptyCell.innerHTML = '<i class="fas fa-info-circle"></i> No data available';
            emptyRow.appendChild(emptyCell);
            tbody.appendChild(emptyRow);
        } else {
            rows.forEach(row => {
                const tr = document.createElement('tr');
                if (typeof rowClick === 'function') {
                    tr.classList.add('table-row-clickable');
                    tr.addEventListener('click', () => rowClick(row));
                }
                columns.forEach(column => {
                    if (column.hidden) return;
                    const td = document.createElement('td');
                    const rawValue = typeof column.accessor === 'function'
                        ? column.accessor(row)
                        : row[column.id];

                    td.innerHTML = column.format
                        ? column.format(rawValue, row)
                        : escapeHtml(rawValue);

                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        }

        table.appendChild(tbody);
    }

    function renderPagination(container, state, rerender) {
        const totalPages = Math.max(1, Math.ceil(state.filteredRows.length / state.pageSize));
        const pagination = document.createElement('div');
        pagination.className = 'table-pagination';

        const info = document.createElement('span');
        info.className = 'pagination-info';
        info.textContent = `Page ${state.currentPage} of ${totalPages}`;

        const controls = document.createElement('div');
        controls.className = 'pagination-controls';

        const prevBtn = document.createElement('button');
        prevBtn.className = 'btn btn-secondary';
        prevBtn.textContent = 'Prev';
        prevBtn.disabled = state.currentPage === 1;
        prevBtn.addEventListener('click', () => {
            if (state.currentPage > 1) {
                state.currentPage -= 1;
                rerender();
            }
        });

        const nextBtn = document.createElement('button');
        nextBtn.className = 'btn btn-secondary';
        nextBtn.textContent = 'Next';
        nextBtn.disabled = state.currentPage >= totalPages;
        nextBtn.addEventListener('click', () => {
            if (state.currentPage < totalPages) {
                state.currentPage += 1;
                rerender();
            }
        });

        controls.appendChild(prevBtn);
        controls.appendChild(nextBtn);

        pagination.appendChild(info);
        pagination.appendChild(controls);

        container.appendChild(pagination);
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function render(container, options) {
        const {
            columns = [],
            rows = [],
            pageSize = 50,
            defaultSort = null,
            onRowClick = null
        } = options;
        container.innerHTML = '';

        const state = createState(rows, { pageSize, defaultSort });
        applySearch(state, columns);
        if (defaultSort?.column) {
            state.sortColumn = defaultSort.column;
            state.sortDirection = defaultSort.direction || 'asc';
        }
        applySort(state, columns);

        const searchContainer = document.createElement('div');
        searchContainer.className = 'table-search-container';
        container.appendChild(searchContainer);

        const tableWrapper = document.createElement('div');
        tableWrapper.className = 'table-wrapper';
        container.appendChild(tableWrapper);

        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'table-pagination-container';
        container.appendChild(paginationContainer);

        const rerender = () => {
            tableWrapper.innerHTML = '';
            paginationContainer.innerHTML = '';

            const table = document.createElement('table');
            table.className = 'table';

            renderTableHeader(table, columns, state, rerender);
            renderTableBody(table, columns, getPagedRows(state), onRowClick);

            tableWrapper.appendChild(table);
            renderPagination(paginationContainer, state, rerender);
        };

        renderSearch(searchContainer, state, columns, rerender);
        rerender();

        return {
            updateRows(newRows) {
                state.originalRows = Array.isArray(newRows) ? newRows : [];
                state.searchTerm = '';
                state.currentPage = 1;
                applySearch(state, columns);
                applySort(state, columns);
                rerender();
            }
        };
    }

    return {
        renderTable: render
    };
})();
