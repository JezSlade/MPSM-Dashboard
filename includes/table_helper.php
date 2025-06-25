<?php

/**
 * Advanced PHP Table Renderer
 * A comprehensive table rendering helper with extensive features
 * 
 * Features:
 * - Column management (show/hide, reorder)
 * - Multi-column sorting
 * - Advanced filtering (text, date, numeric, select)
 * - Pagination with customizable page sizes
 * - Export functionality (CSV, JSON)
 * - Responsive design
 * - Cell formatting and custom renderers
 * - Search functionality
 * - Row selection
 * - Action buttons
 * - Totals/summary rows
 */

class AdvancedTableRenderer
{
    private $data = [];
    private $columns = [];
    private $settings = [];
    private $totalRecords = 0;
    private $filteredRecords = 0;
    
    // Default configuration
    private $config = [
        'table_id' => 'advanced-table',
        'table_class' => 'table table-striped table-hover',
        'container_class' => 'table-responsive',
        'pagination_size' => 10,
        'show_pagination' => true,
        'show_search' => true,
        'show_column_selector' => true,
        'show_export' => true,
        'show_page_size_selector' => true,
        'show_info' => true,
        'sortable' => true,
        'filterable' => true,
        'selectable' => false,
        'checkbox_column' => false,
        'actions_column' => false,
        'responsive' => true,
        'stripe_rows' => true,
        'hover_rows' => true,
        'bordered' => false,
        'compact' => false,
        'dark_theme' => false,
        'sticky_header' => false,
        'show_totals' => false,
        'ajax_url' => null,
        'csrf_token' => null
    ];
    
    // Page size options
    private $pageSizeOptions = [5, 10, 25, 50, 100];
    
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->initializeSettings();
    }
    
    /**
     * Initialize default settings from request parameters
     */
    private function initializeSettings()
    {
        $this->settings = [
            'page' => max(1, intval($_GET['page'] ?? 1)),
            'page_size' => intval($_GET['page_size'] ?? $this->config['pagination_size']),
            'sort_column' => $_GET['sort'] ?? null,
            'sort_direction' => strtoupper($_GET['dir'] ?? 'ASC'),
            'search' => $_GET['search'] ?? '',
            'filters' => $_GET['filters'] ?? [],
            'visible_columns' => $_GET['columns'] ?? [],
            'selected_rows' => $_GET['selected'] ?? []
        ];
        
        // Ensure sort direction is valid
        if (!in_array($this->settings['sort_direction'], ['ASC', 'DESC'])) {
            $this->settings['sort_direction'] = 'ASC';
        }
    }
    
    /**
     * Define table columns
     */
    public function setColumns($columns)
    {
        $this->columns = [];
        foreach ($columns as $key => $column) {
            if (is_string($column)) {
                $this->columns[$key] = [
                    'key' => $key,
                    'label' => $column,
                    'sortable' => true,
                    'filterable' => true,
                    'visible' => true,
                    'type' => 'text',
                    'formatter' => null,
                    'class' => '',
                    'width' => null,
                    'align' => 'left'
                ];
            } else {
                $this->columns[$key] = array_merge([
                    'key' => $key,
                    'label' => ucfirst(str_replace('_', ' ', $key)),
                    'sortable' => true,
                    'filterable' => true,
                    'visible' => true,
                    'type' => 'text',
                    'formatter' => null,
                    'class' => '',
                    'width' => null,
                    'align' => 'left'
                ], $column);
            }
        }
        
        // Set visible columns from settings if not already set
        if (empty($this->settings['visible_columns'])) {
            $this->settings['visible_columns'] = array_keys(array_filter($this->columns, function($col) {
                return $col['visible'];
            }));
        }
        
        return $this;
    }
    
    /**
     * Set table data
     */
    public function setData($data, $totalRecords = null)
    {
        $this->data = $data;
        $this->totalRecords = $totalRecords ?? count($data);
        $this->filteredRecords = count($data);
        return $this;
    }
    
    /**
     * Apply search filter
     */
    private function applySearch($data)
    {
        if (empty($this->settings['search'])) {
            return $data;
        }
        
        $search = strtolower($this->settings['search']);
        return array_filter($data, function($row) use ($search) {
            foreach ($this->columns as $column) {
                if ($column['filterable'] && isset($row[$column['key']])) {
                    if (strpos(strtolower($row[$column['key']]), $search) !== false) {
                        return true;
                    }
                }
            }
            return false;
        });
    }
    
    /**
     * Apply column filters
     */
    private function applyFilters($data)
    {
        if (empty($this->settings['filters'])) {
            return $data;
        }
        
        foreach ($this->settings['filters'] as $column => $filter) {
            if (empty($filter) || !isset($this->columns[$column])) continue;
            
            $columnConfig = $this->columns[$column];
            
            $data = array_filter($data, function($row) use ($column, $filter, $columnConfig) {
                if (!isset($row[$column])) return true;
                
                $value = $row[$column];
                
                switch ($columnConfig['type']) {
                    case 'number':
                        return $this->applyNumericFilter($value, $filter);
                    case 'date':
                        return $this->applyDateFilter($value, $filter);
                    case 'select':
                        return $value == $filter;
                    default:
                        return stripos($value, $filter) !== false;
                }
            });
        }
        
        return $data;
    }
    
    /**
     * Apply numeric filter
     */
    private function applyNumericFilter($value, $filter)
    {
        if (preg_match('/^([><]=?|=)\s*(\d+(?:\.\d+)?)$/', $filter, $matches)) {
            $operator = $matches[1];
            $filterValue = floatval($matches[2]);
            $numValue = floatval($value);
            
            switch ($operator) {
                case '>': return $numValue > $filterValue;
                case '>=': return $numValue >= $filterValue;
                case '<': return $numValue < $filterValue;
                case '<=': return $numValue <= $filterValue;
                case '=': return $numValue == $filterValue;
            }
        }
        
        return stripos($value, $filter) !== false;
    }
    
    /**
     * Apply date filter
     */
    private function applyDateFilter($value, $filter)
    {
        try {
            $valueDate = new DateTime($value);
            $filterDate = new DateTime($filter);
            return $valueDate->format('Y-m-d') == $filterDate->format('Y-m-d');
        } catch (Exception $e) {
            return stripos($value, $filter) !== false;
        }
    }
    
    /**
     * Apply sorting
     */
    private function applySorting($data)
    {
        if (empty($this->settings['sort_column']) || !isset($this->columns[$this->settings['sort_column']])) {
            return $data;
        }
        
        $sortColumn = $this->settings['sort_column'];
        $sortDirection = $this->settings['sort_direction'];
        
        usort($data, function($a, $b) use ($sortColumn, $sortDirection) {
            $aVal = $a[$sortColumn] ?? '';
            $bVal = $b[$sortColumn] ?? '';
            
            // Handle numeric values
            if (is_numeric($aVal) && is_numeric($bVal)) {
                $result = $aVal <=> $bVal;
            } else {
                $result = strcasecmp($aVal, $bVal);
            }
            
            return $sortDirection === 'DESC' ? -$result : $result;
        });
        
        return $data;
    }
    
    /**
     * Apply pagination
     */
    private function applyPagination($data)
    {
        if (!$this->config['show_pagination']) {
            return $data;
        }
        
        $offset = ($this->settings['page'] - 1) * $this->settings['page_size'];
        return array_slice($data, $offset, $this->settings['page_size']);
    }
    
    /**
     * Process data through all filters, sorting, and pagination
     */
    private function processData()
    {
        $data = $this->data;
        
        // Apply search
        $data = $this->applySearch($data);
        
        // Apply filters
        $data = $this->applyFilters($data);
        
        $this->filteredRecords = count($data);
        
        // Apply sorting
        $data = $this->applySorting($data);
        
        // Apply pagination
        $data = $this->applyPagination($data);
        
        return $data;
    }
    
    /**
     * Format cell value
     */
    private function formatCell($value, $column)
    {
        if ($column['formatter'] && is_callable($column['formatter'])) {
            return call_user_func($column['formatter'], $value);
        }
        
        switch ($column['type']) {
            case 'date':
                return $this->formatDate($value);
            case 'currency':
                return $this->formatCurrency($value);
            case 'number':
                return $this->formatNumber($value);
            case 'boolean':
                return $value ? 'Yes' : 'No';
            case 'email':
                return "<a href='mailto:$value'>$value</a>";
            case 'url':
                return "<a href='$value' target='_blank'>$value</a>";
            case 'image':
                return "<img src='$value' alt='Image' style='max-width: 50px; max-height: 50px;'>";
            default:
                return htmlspecialchars($value);
        }
    }
    
    private function formatDate($value)
    {
        if (empty($value)) return '';
        try {
            $date = new DateTime($value);
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return htmlspecialchars($value);
        }
    }
    
    private function formatCurrency($value)
    {
        return '$' . number_format(floatval($value), 2);
    }
    
    private function formatNumber($value)
    {
        return number_format(floatval($value), 2);
    }
    
    /**
     * Generate column selector HTML
     */
    private function renderColumnSelector()
    {
        if (!$this->config['show_column_selector']) return '';
        
        $html = '<div class="table-column-selector dropdown">';
        $html .= '<button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">';
        $html .= '<i class="fas fa-columns"></i> Columns</button>';
        $html .= '<div class="dropdown-menu dropdown-menu-right p-3" style="min-width: 200px;">';
        
        foreach ($this->columns as $key => $column) {
            $checked = in_array($key, $this->settings['visible_columns']) ? 'checked' : '';
            $html .= '<div class="form-check">';
            $html .= "<input class='form-check-input column-toggle' type='checkbox' value='$key' id='col_$key' $checked>";
            $html .= "<label class='form-check-label' for='col_$key'>{$column['label']}</label>";
            $html .= '</div>';
        }
        
        $html .= '</div></div>';
        return $html;
    }
    
    /**
     * Generate search box HTML
     */
    private function renderSearchBox()
    {
        if (!$this->config['show_search']) return '';
        
        $value = htmlspecialchars($this->settings['search']);
        return "<div class='table-search'>
                    <input type='text' class='form-control' placeholder='Search...' value='$value' id='table-search'>
                </div>";
    }
    
    /**
     * Generate page size selector HTML
     */
    private function renderPageSizeSelector()
    {
        if (!$this->config['show_page_size_selector']) return '';
        
        $html = '<div class="table-page-size">';
        $html .= '<select class="form-control form-control-sm" id="page-size-selector">';
        
        foreach ($this->pageSizeOptions as $size) {
            $selected = $size == $this->settings['page_size'] ? 'selected' : '';
            $html .= "<option value='$size' $selected>$size</option>";
        }
        
        $html .= '</select> <span>entries per page</span></div>';
        return $html;
    }
    
    /**
     * Generate export buttons HTML
     */
    private function renderExportButtons()
    {
        if (!$this->config['show_export']) return '';
        
        return '<div class="table-export">
                    <button class="btn btn-outline-primary btn-sm" onclick="exportTable(\'csv\')">
                        <i class="fas fa-download"></i> CSV
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="exportTable(\'json\')">
                        <i class="fas fa-download"></i> JSON
                    </button>
                </div>';
    }
    
    /**
     * Generate table info HTML
     */
    private function renderTableInfo()
    {
        if (!$this->config['show_info']) return '';
        
        $start = ($this->settings['page'] - 1) * $this->settings['page_size'] + 1;
        $end = min($start + $this->settings['page_size'] - 1, $this->filteredRecords);
        
        return "<div class='table-info'>
                    Showing $start to $end of {$this->filteredRecords} entries
                    " . ($this->filteredRecords != $this->totalRecords ? "(filtered from {$this->totalRecords} total entries)" : "") . "
                </div>";
    }
    
    /**
     * Generate pagination HTML
     */
    private function renderPagination()
    {
        if (!$this->config['show_pagination'] || $this->filteredRecords <= $this->settings['page_size']) {
            return '';
        }
        
        $totalPages = ceil($this->filteredRecords / $this->settings['page_size']);
        $currentPage = $this->settings['page'];
        
        $html = '<nav><ul class="pagination justify-content-center">';
        
        // Previous button
        $disabled = $currentPage <= 1 ? 'disabled' : '';
        $html .= "<li class='page-item $disabled'>";
        $html .= "<a class='page-link' href='#' data-page='" . ($currentPage - 1) . "'>Previous</a>";
        $html .= "</li>";
        
        // Page numbers
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        if ($start > 1) {
            $html .= "<li class='page-item'><a class='page-link' href='#' data-page='1'>1</a></li>";
            if ($start > 2) {
                $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
            }
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $active = $i == $currentPage ? 'active' : '';
            $html .= "<li class='page-item $active'>";
            $html .= "<a class='page-link' href='#' data-page='$i'>$i</a>";
            $html .= "</li>";
        }
        
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
            }
            $html .= "<li class='page-item'><a class='page-link' href='#' data-page='$totalPages'>$totalPages</a></li>";
        }
        
        // Next button
        $disabled = $currentPage >= $totalPages ? 'disabled' : '';
        $html .= "<li class='page-item $disabled'>";
        $html .= "<a class='page-link' href='#' data-page='" . ($currentPage + 1) . "'>Next</a>";
        $html .= "</li>";
        
        $html .= '</ul></nav>';
        return $html;
    }
    
    /**
     * Generate filter row HTML
     */
    private function renderFilterRow()
    {
        if (!$this->config['filterable']) return '';
        
        $html = '<tr class="table-filters">';
        
        if ($this->config['checkbox_column']) {
            $html .= '<th></th>';
        }
        
        foreach ($this->settings['visible_columns'] as $key) {
            if (!isset($this->columns[$key])) continue;
            
            $column = $this->columns[$key];
            $value = htmlspecialchars($this->settings['filters'][$key] ?? '');
            
            if ($column['filterable']) {
                if ($column['type'] === 'select' && isset($column['options'])) {
                    $html .= '<th>';
                    $html .= "<select class='form-control form-control-sm column-filter' data-column='$key'>";
                    $html .= "<option value=''>All</option>";
                    foreach ($column['options'] as $optValue => $optLabel) {
                        $selected = $value == $optValue ? 'selected' : '';
                        $html .= "<option value='$optValue' $selected>$optLabel</option>";
                    }
                    $html .= '</select>';
                    $html .= '</th>';
                } else {
                    $placeholder = $column['type'] === 'number' ? 'e.g. >100, <=50' : 'Filter...';
                    $html .= "<th><input type='text' class='form-control form-control-sm column-filter' 
                                 data-column='$key' placeholder='$placeholder' value='$value'></th>";
                }
            } else {
                $html .= '<th></th>';
            }
        }
        
        if ($this->config['actions_column']) {
            $html .= '<th></th>';
        }
        
        $html .= '</tr>';
        return $html;
    }
    
    /**
     * Generate table header HTML
     */
    private function renderTableHeader()
    {
        $html = '<thead><tr>';
        
        if ($this->config['checkbox_column']) {
            $html .= '<th><input type="checkbox" id="select-all"></th>';
        }
        
        foreach ($this->settings['visible_columns'] as $key) {
            if (!isset($this->columns[$key])) continue;
            
            $column = $this->columns[$key];
            $class = $column['class'];
            $width = $column['width'] ? "width='{$column['width']}'" : '';
            $align = $column['align'];
            
            if ($column['sortable']) {
                $sortClass = '';
                $sortIcon = '';
                
                if ($this->settings['sort_column'] === $key) {
                    $sortClass = 'sorted';
                    $sortIcon = $this->settings['sort_direction'] === 'ASC' ? 
                        '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
                }
                
                $html .= "<th class='sortable $class $sortClass' data-column='$key' $width style='text-align: $align'>";
                $html .= "{$column['label']} $sortIcon";
                $html .= "</th>";
            } else {
                $html .= "<th class='$class' $width style='text-align: $align'>{$column['label']}</th>";
            }
        }
        
        if ($this->config['actions_column']) {
            $html .= '<th>Actions</th>';
        }
        
        $html .= '</tr>';
        $html .= $this->renderFilterRow();
        $html .= '</thead>';
        
        return $html;
    }
    
    /**
     * Generate table body HTML
     */
    private function renderTableBody($data)
    {
        $html = '<tbody>';
        
        if (empty($data)) {
            $colspan = count($this->settings['visible_columns']);
            if ($this->config['checkbox_column']) $colspan++;
            if ($this->config['actions_column']) $colspan++;
            
            $html .= "<tr><td colspan='$colspan' class='text-center'>No data available</td></tr>";
        } else {
            foreach ($data as $index => $row) {
                $html .= "<tr data-row-index='$index'>";
                
                if ($this->config['checkbox_column']) {
                    $rowId = $row['id'] ?? $index;
                    $checked = in_array($rowId, $this->settings['selected_rows']) ? 'checked' : '';
                    $html .= "<td><input type='checkbox' class='row-selector' value='$rowId' $checked></td>";
                }
                
                foreach ($this->settings['visible_columns'] as $key) {
                    if (!isset($this->columns[$key])) continue;
                    
                    $column = $this->columns[$key];
                    $value = $row[$key] ?? '';
                    $formattedValue = $this->formatCell($value, $column);
                    $class = $column['class'];
                    $align = $column['align'];
                    
                    $html .= "<td class='$class' style='text-align: $align'>$formattedValue</td>";
                }
                
                if ($this->config['actions_column']) {
                    $html .= '<td>';
                    $html .= $this->renderActionButtons($row, $index);
                    $html .= '</td>';
                }
                
                $html .= '</tr>';
            }
        }
        
        $html .= '</tbody>';
        return $html;
    }
    
    /**
     * Generate action buttons HTML
     */
    private function renderActionButtons($row, $index)
    {
        $rowId = $row['id'] ?? $index;
        return "<div class='btn-group btn-group-sm'>
                    <button class='btn btn-outline-primary' onclick='editRow($rowId)' title='Edit'>
                        <i class='fas fa-edit'></i>
                    </button>
                    <button class='btn btn-outline-danger' onclick='deleteRow($rowId)' title='Delete'>
                        <i class='fas fa-trash'></i>
                    </button>
                    <button class='btn btn-outline-info' onclick='viewRow($rowId)' title='View'>
                        <i class='fas fa-eye'></i>
                    </button>
                </div>";
    }
    
    /**
     * Generate table footer with totals
     */
    private function renderTableFooter($data)
    {
        if (!$this->config['show_totals']) return '';
        
        $html = '<tfoot><tr>';
        
        if ($this->config['checkbox_column']) {
            $html .= '<th></th>';
        }
        
        foreach ($this->settings['visible_columns'] as $key) {
            if (!isset($this->columns[$key])) continue;
            
            $column = $this->columns[$key];
            
            if ($column['type'] === 'number' || $column['type'] === 'currency') {
                $total = array_sum(array_column($data, $key));
                $formattedTotal = $this->formatCell($total, $column);
                $html .= "<th>$formattedTotal</th>";
            } else {
                $html .= '<th></th>';
            }
        }
        
        if ($this->config['actions_column']) {
            $html .= '<th></th>';
        }
        
        $html .= '</tr></tfoot>';
        return $html;
    }
    
    /**
     * Generate CSS styles
     */
    private function renderStyles()
    {
        return '<style>
            .table-container {
                margin: 20px 0;
            }
            
            .table-controls {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .table-controls-left,
            .table-controls-right {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            
            .table-search input {
                width: 250px;
            }
            
            .table-page-size {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .table-page-size select {
                width: 80px;
            }
            
            .table-export {
                display: flex;
                gap: 5px;
            }
            
            .sortable {
                cursor: pointer;
                user-select: none;
            }
            
            .sortable:hover {
                background-color: #f8f9fa;
            }
            
            .sorted {
                background-color: #e9ecef;
            }
            
            .table-filters input,
            .table-filters select {
                min-width: 100px;
            }
            
            .table-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 15px;
            }
            
            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            
            @media (max-width: 768px) {
                .table-controls {
                    flex-direction: column;
                    align-items: stretch;
                }
                
                .table-controls-left,
                .table-controls-right {
                    justify-content: center;
                }
                
                .table-search input {
                    width: 100%;
                }
            }
        </style>';
    }
    
    /**
     * Generate JavaScript functionality
     */
    private function renderScript()
    {
        $tableId = $this->config['table_id'];
        $ajaxUrl = $this->config['ajax_url'];
        $csrfToken = $this->config['csrf_token'];
        
        return "<script>
            let currentSettings = " . json_encode($this->settings) . ";
            
            // Update URL with current settings
            function updateUrl() {
                const url = new URL(window.location);
                Object.keys(currentSettings).forEach(key => {
                    if (currentSettings[key] && currentSettings[key] !== '') {
                        if (Array.isArray(currentSettings[key])) {
                            if (currentSettings[key].length > 0) {
                                url.searchParams.set(key, currentSettings[key].join(','));
                            } else {
                                url.searchParams.delete(key);
                            }
                        } else {
                            url.searchParams.set(key, currentSettings[key]);
                        }
                    } else {
                        url.searchParams.delete(key);
                    }
                });
                
                if ('$ajaxUrl') {
                    loadTableData();
                } else {
                    window.history.pushState({}, '', url);
                    location.reload();
                }
            }
            
            // Load table data via AJAX
            function loadTableData() {
                if (!'$ajaxUrl') return;
                
                const formData = new FormData();
                Object.keys(currentSettings).forEach(key => {
                    if (Array.isArray(currentSettings[key])) {
                        currentSettings[key].forEach(val => formData.append(key + '[]', val));
                    } else {
                        formData.append(key, currentSettings[key]);
                    }
                });
                
                if ('$csrfToken') {
                    formData.append('_token', '$csrfToken');
                }
                
                fetch('$ajaxUrl', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('$tableId-container').innerHTML = html;
                    initializeEventListeners();
                })
                .catch(error => console.error('Error loading table data:', error));
            }
            
            // Search functionality
            document.addEventListener('DOMContentLoaded', function() {
                initializeEventListeners();
            });
            
            function initializeEventListeners() {
                // Search input
                const searchInput = document.getElementById('table-search');
                if (searchInput) {
                    let searchTimeout;
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => {
                            currentSettings.search = this.value;
                            currentSettings.page = 1;
                            updateUrl();
                        }, 500);
                    });
                }
                
                // Page size selector
                const pageSizeSelector = document.getElementById('page-size-selector');
                if (pageSizeSelector) {
                    pageSizeSelector.addEventListener('change', function() {
                        currentSettings.page_size = parseInt(this.value);
                        currentSettings.page = 1;
                        updateUrl();
                    });
                }
                
                // Column toggles
                document.querySelectorAll('.column-toggle').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        if (this.checked) {
                            if (!currentSettings.visible_columns.includes(this.value)) {
                                currentSettings.visible_columns.push(this.value);
                            }
                        } else {
                            currentSettings.visible_columns = currentSettings.visible_columns.filter(col => col !== this.value);
                        }
                        updateUrl();
                    });
                });
                
                // Sortable columns
                document.querySelectorAll('.sortable').forEach(header => {
                    header.addEventListener('click', function() {
                        const column = this.dataset.column;
                        if (currentSettings.sort_column === column) {
                            currentSettings.sort_direction = currentSettings.sort_direction === 'ASC' ? 'DESC' : 'ASC';
                        } else {
                            currentSettings.sort_column = column;
                            currentSettings.sort_direction = 'ASC';
                        }
                        currentSettings.page = 1;
                        updateUrl();
                    });
                });
                
                // Column filters
                document.querySelectorAll('.column-filter').forEach(filter => {
                    let filterTimeout;
                    filter.addEventListener('input', function() {
                        clearTimeout(filterTimeout);
                        filterTimeout = setTimeout(() => {
                            const column = this.dataset.column;
                            if (!currentSettings.filters) currentSettings.filters = {};
                            currentSettings.filters[column] = this.value;
                            currentSettings.page = 1;
                            updateUrl();
                        }, 500);
                    });
                });
                
                // Pagination
                document.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const page = parseInt(this.dataset.page);
                        if (page && page > 0) {
                            currentSettings.page = page;
                            updateUrl();
                        }
                    });
                });
                
                // Select all checkbox
                const selectAll = document.getElementById('select-all');
                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('.row-selector');
                        checkboxes.forEach(cb => {
                            cb.checked = this.checked;
                            if (this.checked) {
                                if (!currentSettings.selected_rows.includes(cb.value)) {
                                    currentSettings.selected_rows.push(cb.value);
                                }
                            } else {
                                currentSettings.selected_rows = currentSettings.selected_rows.filter(id => id !== cb.value);
                            }
                        });
                    });
                }
                
                // Individual row selectors
                document.querySelectorAll('.row-selector').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        if (this.checked) {
                            if (!currentSettings.selected_rows.includes(this.value)) {
                                currentSettings.selected_rows.push(this.value);
                            }
                        } else {
                            currentSettings.selected_rows = currentSettings.selected_rows.filter(id => id !== this.value);
                        }
                        
                        // Update select all checkbox
                        const selectAll = document.getElementById('select-all');
                        if (selectAll) {
                            const allCheckboxes = document.querySelectorAll('.row-selector');
                            const checkedCheckboxes = document.querySelectorAll('.row-selector:checked');
                            selectAll.checked = allCheckboxes.length === checkedCheckboxes.length;
                            selectAll.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
                        }
                    });
                });
            }
            
            // Export functionality
            function exportTable(format) {
                const data = getCurrentTableData();
                
                if (format === 'csv') {
                    const csv = convertToCSV(data);
                    downloadFile(csv, 'table-export.csv', 'text/csv');
                } else if (format === 'json') {
                    const json = JSON.stringify(data, null, 2);
                    downloadFile(json, 'table-export.json', 'application/json');
                }
            }
            
            function getCurrentTableData() {
                const rows = [];
                const table = document.querySelector('#$tableId');
                const headers = Array.from(table.querySelectorAll('thead tr:first-child th')).map(th => th.textContent.trim());
                
                table.querySelectorAll('tbody tr').forEach(tr => {
                    const row = {};
                    tr.querySelectorAll('td').forEach((td, index) => {
                        if (headers[index]) {
                            row[headers[index]] = td.textContent.trim();
                        }
                    });
                    rows.push(row);
                });
                
                return rows;
            }
            
            function convertToCSV(data) {
                if (data.length === 0) return '';
                
                const headers = Object.keys(data[0]);
                const csvContent = [
                    headers.join(','),
                    ...data.map(row => headers.map(header => {
                        const value = row[header] || '';
                        return '\"' + value.toString().replace(/\"/g, '\"\"') + '\"';
                    }).join(','))
                ].join('\\n');
                
                return csvContent;
            }
            
            function downloadFile(content, filename, contentType) {
                const blob = new Blob([content], { type: contentType });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            }
            
            // Action button functions (to be customized)
            function editRow(id) {
                alert('Edit row with ID: ' + id);
                // Implement your edit logic here
            }
            
            function deleteRow(id) {
                if (confirm('Are you sure you want to delete this row?')) {
                    alert('Delete row with ID: ' + id);
                    // Implement your delete logic here
                }
            }
            
            function viewRow(id) {
                alert('View row with ID: ' + id);
                // Implement your view logic here
            }
        </script>";
    }
    
    /**
     * Render the complete table
     */
    public function render()
    {
        // Process the data
        $processedData = $this->processData();
        
        // Build table classes
        $tableClasses = [$this->config['table_class']];
        if ($this->config['bordered']) $tableClasses[] = 'table-bordered';
        if ($this->config['compact']) $tableClasses[] = 'table-sm';
        if ($this->config['dark_theme']) $tableClasses[] = 'table-dark';
        if ($this->config['hover_rows']) $tableClasses[] = 'table-hover';
        if ($this->config['stripe_rows']) $tableClasses[] = 'table-striped';
        
        $tableClass = implode(' ', $tableClasses);
        $containerId = $this->config['table_id'] . '-container';
        
        // Start building HTML
        $html = $this->renderStyles();
        
        $html .= "<div id='$containerId' class='table-container'>";
        
        // Controls row
        $html .= '<div class="table-controls">';
        $html .= '<div class="table-controls-left">';
        $html .= $this->renderSearchBox();
        $html .= $this->renderPageSizeSelector();
        $html .= '</div>';
        $html .= '<div class="table-controls-right">';
        $html .= $this->renderColumnSelector();
        $html .= $this->renderExportButtons();
        $html .= '</div>';
        $html .= '</div>';
        
        // Table wrapper
        $containerClass = $this->config['container_class'];
        if ($this->config['sticky_header']) {
            $containerClass .= ' sticky-header';
        }
        
        $html .= "<div class='$containerClass'>";
        $html .= "<table id='{$this->config['table_id']}' class='$tableClass'>";
        
        // Table content
        $html .= $this->renderTableHeader();
        $html .= $this->renderTableBody($processedData);
        $html .= $this->renderTableFooter($processedData);
        
        $html .= '</table>';
        $html .= '</div>';
        
        // Footer controls
        $html .= '<div class="table-footer">';
        $html .= $this->renderTableInfo();
        $html .= $this->renderPagination();
        $html .= '</div>';
        
        $html .= '</div>';
        
        $html .= $this->renderScript();
        
        return $html;
    }
    
    /**
     * Static method to quickly create and render a table
     */
    public static function create($data, $columns, $config = [])
    {
        $table = new self($config);
        return $table->setColumns($columns)->setData($data)->render();
    }
    
    /**
     * Get current settings (useful for AJAX endpoints)
     */
    public function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * Set settings programmatically
     */
    public function setSettings($settings)
    {
        $this->settings = array_merge($this->settings, $settings);
        return $this;
    }
    
    /**
     * Get processed data (useful for exports)
     */
    public function getProcessedData()
    {
        return $this->processData();
    }
    
    /**
     * Add custom CSS class to table
     */
    public function addClass($class)
    {
        $this->config['table_class'] .= ' ' . $class;
        return $this;
    }
    
    /**
     * Set table theme
     */
    public function setTheme($theme)
    {
        switch ($theme) {
            case 'dark':
                $this->config['dark_theme'] = true;
                break;
            case 'bordered':
                $this->config['bordered'] = true;
                break;
            case 'compact':
                $this->config['compact'] = true;
                break;
            case 'striped':
                $this->config['stripe_rows'] = true;
                break;
        }
        return $this;
    }
}

// Example usage:
/*
// Sample data
$data = [
    ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'age' => 30, 'salary' => 50000, 'status' => 'active', 'created_at' => '2024-01-15'],
    ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'age' => 25, 'salary' => 45000, 'status' => 'active', 'created_at' => '2024-01-10'],
    ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'age' => 35, 'salary' => 60000, 'status' => 'inactive', 'created_at' => '2024-01-05'],
];

// Column definitions
$columns = [
    'id' => ['label' => 'ID', 'type' => 'number', 'width' => '80px'],
    'name' => ['label' => 'Full Name', 'sortable' => true],
    'email' => ['label' => 'Email Address', 'type' => 'email'],
    'age' => ['label' => 'Age', 'type' => 'number', 'align' => 'center'],
    'salary' => ['label' => 'Salary', 'type' => 'currency', 'align' => 'right'],
    'status' => [
        'label' => 'Status', 
        'type' => 'select',
        'options' => ['active' => 'Active', 'inactive' => 'Inactive'],
        'formatter' => function($value) {
            $class = $value === 'active' ? 'success' : 'danger';
            return "<span class='badge badge-$class'>$value</span>";
        }
    ],
    'created_at' => ['label' => 'Created', 'type' => 'date']
];

// Configuration
$config = [
    'table_id' => 'users-table',
    'show_export' => true,
    'show_column_selector' => true,
    'checkbox_column' => true,
    'actions_column' => true,
    'show_totals' => true,
    'pagination_size' => 10
];

// Create and render table
$table = new AdvancedTableRenderer($config);
echo $table->setColumns($columns)->setData($data)->render();

// Or use the static method for quick creation
echo AdvancedTableRenderer::create($data, $columns, $config);
*/

?>