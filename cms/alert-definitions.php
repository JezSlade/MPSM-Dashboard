<?php
/**
 * Alert Definitions Management
 * Admin UI for managing alert code to description mappings
 */
require 'config.php';
require 'functions.php';

requireAuth();
trackVisit('/alert-definitions');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Definitions - MPSM Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .definitions-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .definitions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .definitions-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .definitions-filters input,
        .definitions-filters select {
            padding: 8px 12px;
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 6px;
            background: var(--card-bg, #fff);
            color: var(--text-color, #1e293b);
        }

        .definitions-filters input {
            min-width: 250px;
        }

        .definitions-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg, #fff);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .definitions-table th,
        .definitions-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
        }

        .definitions-table th {
            background: var(--header-bg, #f8fafc);
            font-weight: 600;
            color: var(--text-muted, #64748b);
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .definitions-table tr:hover {
            background: var(--hover-bg, #f1f5f9);
        }

        .alert-code-cell {
            font-family: monospace;
            font-size: 0.9em;
            background: var(--code-bg, #f1f5f9);
            padding: 4px 8px;
            border-radius: 4px;
        }

        .category-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
        }

        .category-paper { background: #fef3c7; color: #92400e; }
        .category-toner { background: #dbeafe; color: #1e40af; }
        .category-service { background: #f3e8ff; color: #6b21a8; }
        .category-error { background: #fee2e2; color: #991b1b; }
        .category-warning { background: #ffedd5; color: #9a3412; }
        .category-info { background: #e0f2fe; color: #075985; }
        .category-default { background: #f1f5f9; color: #475569; }

        .severity-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .severity-info { background: #dbeafe; color: #1e40af; }
        .severity-warning { background: #fef3c7; color: #92400e; }
        .severity-high { background: #ffedd5; color: #9a3412; }
        .severity-critical { background: #fee2e2; color: #991b1b; }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-buttons button {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
        }

        .btn-edit {
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-delete {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-edit:hover { background: #bfdbfe; }
        .btn-delete:hover { background: #fecaca; }

        .unmapped-section {
            margin-top: 30px;
            padding: 20px;
            background: var(--card-bg, #fff);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .unmapped-section h3 {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .unmapped-count {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85em;
        }

        .unmapped-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
        }

        .unmapped-item:last-child {
            border-bottom: none;
        }

        .unmapped-info {
            flex: 1;
        }

        .unmapped-code {
            font-family: monospace;
            font-weight: 600;
        }

        .unmapped-desc {
            font-size: 0.9em;
            color: var(--text-muted, #64748b);
            margin-top: 4px;
        }

        .unmapped-stats {
            font-size: 0.8em;
            color: var(--text-muted, #64748b);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--card-bg, #fff);
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
        }

        .modal-header h2 {
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: var(--text-muted, #64748b);
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 6px;
            background: var(--input-bg, #fff);
            color: var(--text-color, #1e293b);
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 20px;
            border-top: 1px solid var(--border-color, #e2e8f0);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-muted, #64748b);
        }

        .empty-state i {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .stats-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            flex: 1;
            padding: 16px 20px;
            background: var(--card-bg, #fff);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-value {
            font-size: 2em;
            font-weight: 700;
            color: var(--primary-color, #3b82f6);
        }

        .stat-label {
            font-size: 0.9em;
            color: var(--text-muted, #64748b);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-tags"></i> Alert Definitions</h1>
            <div class="header-actions">
                <a class="btn-icon" href="index.php" title="Back to Dashboard">
                    <i class="fas fa-home"></i>
                </a>
                <a class="btn-icon" href="command-center.php" title="Command Center">
                    <i class="fas fa-shield-alt"></i>
                </a>
                <button id="theme-toggle" class="btn-icon" title="Toggle theme">
                    <i class="fas fa-moon"></i>
                </button>
                <button id="logout-btn" class="btn-icon" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </header>

    <main class="definitions-container">
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value" id="stat-total">-</div>
                <div class="stat-label">Total Definitions</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="stat-unmapped">-</div>
                <div class="stat-label">Unmapped Alerts</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="stat-categories">-</div>
                <div class="stat-label">Categories</div>
            </div>
        </div>

        <div class="card">
            <div class="definitions-header">
                <h2>Manage Alert Labels</h2>
                <div class="definitions-filters">
                    <input type="text" id="search-input" placeholder="Search alerts...">
                    <select id="category-filter">
                        <option value="">All Categories</option>
                    </select>
                    <button class="btn btn-primary" onclick="openCreateModal()">
                        <i class="fas fa-plus"></i> Add Definition
                    </button>
                </div>
            </div>

            <div id="definitions-list">
                <div class="loading">Loading definitions...</div>
            </div>
        </div>

        <div class="unmapped-section">
            <h3>
                <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i>
                Unmapped Alert Codes
                <span class="unmapped-count" id="unmapped-count">0</span>
            </h3>
            <p style="color: var(--text-muted); margin-bottom: 15px;">
                These alert codes have been received but don't have display names defined yet.
            </p>
            <div id="unmapped-list">
                <div class="loading">Loading unmapped alerts...</div>
            </div>
        </div>
    </main>

    <!-- Edit/Create Modal -->
    <div id="definition-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Add Alert Definition</h2>
                <button class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="definition-form" onsubmit="saveDefinition(event)">
                <div class="modal-body">
                    <input type="hidden" id="edit-id">

                    <div class="form-group">
                        <label for="alert-code">Alert Code <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="alert-code" required placeholder="e.g., 808, MAINT_001, PAPER_JAM">
                    </div>

                    <div class="form-group">
                        <label for="display-name">Display Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="display-name" required placeholder="Human-readable name shown to users">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" placeholder="Detailed description of what this alert means and any recommended actions"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category">
                                <option value="">Select Category</option>
                                <option value="Paper">Paper</option>
                                <option value="Toner">Toner</option>
                                <option value="Service">Service</option>
                                <option value="Error">Error</option>
                                <option value="Warning">Warning</option>
                                <option value="Info">Info</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Hardware">Hardware</option>
                                <option value="Network">Network</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="severity-override">Severity Override</label>
                            <select id="severity-override">
                                <option value="">Use Default</option>
                                <option value="info">Info</option>
                                <option value="warning">Warning</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="original-description">Original MPS Description (read-only)</label>
                        <input type="text" id="original-description" readonly style="background: var(--header-bg, #f8fafc);">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-container"></div>

    <script src="assets/app.js"></script>
    <script>
        let allDefinitions = [];
        let allCategories = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadDefinitions();
            loadUnmapped();

            document.getElementById('search-input').addEventListener('input', filterDefinitions);
            document.getElementById('category-filter').addEventListener('change', filterDefinitions);
        });

        async function loadDefinitions() {
            try {
                const response = await fetch('api/command-center.php?action=get_alert_definitions');
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Failed to load definitions');
                }

                allDefinitions = data.definitions;
                allCategories = data.categories;

                document.getElementById('stat-total').textContent = data.count;
                document.getElementById('stat-categories').textContent = data.categories.length;

                // Populate category filter
                const categoryFilter = document.getElementById('category-filter');
                categoryFilter.innerHTML = '<option value="">All Categories</option>';
                data.categories.forEach(cat => {
                    categoryFilter.innerHTML += `<option value="${escapeHtml(cat)}">${escapeHtml(cat)}</option>`;
                });

                renderDefinitions(allDefinitions);
            } catch (error) {
                console.error('Error loading definitions:', error);
                document.getElementById('definitions-list').innerHTML =
                    `<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Error loading definitions</p></div>`;
            }
        }

        async function loadUnmapped() {
            try {
                const response = await fetch('api/command-center.php?action=get_unmapped_alerts&limit=50');
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Failed to load unmapped alerts');
                }

                document.getElementById('stat-unmapped').textContent = data.count;
                document.getElementById('unmapped-count').textContent = data.count;

                renderUnmapped(data.unmapped);
            } catch (error) {
                console.error('Error loading unmapped:', error);
                document.getElementById('unmapped-list').innerHTML =
                    `<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Error loading unmapped alerts</p></div>`;
            }
        }

        function renderDefinitions(definitions) {
            const container = document.getElementById('definitions-list');

            if (definitions.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <p>No alert definitions found</p>
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <i class="fas fa-plus"></i> Create First Definition
                        </button>
                    </div>
                `;
                return;
            }

            let html = `
                <table class="definitions-table">
                    <thead>
                        <tr>
                            <th>Alert Code</th>
                            <th>Display Name</th>
                            <th>Category</th>
                            <th>Severity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            definitions.forEach(def => {
                const categoryClass = def.category ? `category-${def.category.toLowerCase()}` : 'category-default';
                const severityClass = def.severity_override ? `severity-${def.severity_override}` : '';

                html += `
                    <tr>
                        <td><span class="alert-code-cell">${escapeHtml(def.alert_code)}</span></td>
                        <td>
                            <strong>${escapeHtml(def.display_name)}</strong>
                            ${def.description ? `<div style="font-size: 0.85em; color: var(--text-muted);">${escapeHtml(def.description.substring(0, 100))}${def.description.length > 100 ? '...' : ''}</div>` : ''}
                        </td>
                        <td>
                            ${def.category ? `<span class="category-badge ${categoryClass}">${escapeHtml(def.category)}</span>` : '-'}
                        </td>
                        <td>
                            ${def.severity_override ? `<span class="severity-badge ${severityClass}">${escapeHtml(def.severity_override)}</span>` : '-'}
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="openEditModal(${def.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete" onclick="deleteDefinition(${def.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function renderUnmapped(unmapped) {
            const container = document.getElementById('unmapped-list');

            if (unmapped.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-check-circle" style="color: #22c55e;"></i>
                        <p>All alert codes have definitions!</p>
                    </div>
                `;
                return;
            }

            let html = '';
            unmapped.forEach(item => {
                html += `
                    <div class="unmapped-item">
                        <div class="unmapped-info">
                            <div class="unmapped-code">${escapeHtml(item.alert_code)}</div>
                            ${item.original_description ? `<div class="unmapped-desc">${escapeHtml(item.original_description)}</div>` : ''}
                        </div>
                        <div class="unmapped-stats">
                            ${item.occurrence_count} occurrences
                        </div>
                        <button class="btn btn-primary" onclick="openCreateModalWithCode('${escapeHtml(item.alert_code)}', '${escapeHtml(item.original_description || '')}')">
                            <i class="fas fa-plus"></i> Define
                        </button>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function filterDefinitions() {
            const search = document.getElementById('search-input').value.toLowerCase();
            const category = document.getElementById('category-filter').value;

            let filtered = allDefinitions;

            if (search) {
                filtered = filtered.filter(def =>
                    def.alert_code.toLowerCase().includes(search) ||
                    def.display_name.toLowerCase().includes(search) ||
                    (def.description && def.description.toLowerCase().includes(search))
                );
            }

            if (category) {
                filtered = filtered.filter(def => def.category === category);
            }

            renderDefinitions(filtered);
        }

        function openCreateModal() {
            document.getElementById('modal-title').textContent = 'Add Alert Definition';
            document.getElementById('edit-id').value = '';
            document.getElementById('alert-code').value = '';
            document.getElementById('alert-code').disabled = false;
            document.getElementById('display-name').value = '';
            document.getElementById('description').value = '';
            document.getElementById('category').value = '';
            document.getElementById('severity-override').value = '';
            document.getElementById('original-description').value = '';
            document.getElementById('definition-modal').classList.add('active');
        }

        function openCreateModalWithCode(alertCode, originalDesc) {
            openCreateModal();
            document.getElementById('alert-code').value = alertCode;
            document.getElementById('original-description').value = originalDesc;
            // Suggest a display name based on original description
            if (originalDesc) {
                document.getElementById('display-name').value = originalDesc;
            }
        }

        function openEditModal(id) {
            const def = allDefinitions.find(d => d.id == id);
            if (!def) return;

            document.getElementById('modal-title').textContent = 'Edit Alert Definition';
            document.getElementById('edit-id').value = def.id;
            document.getElementById('alert-code').value = def.alert_code;
            document.getElementById('alert-code').disabled = true;
            document.getElementById('display-name').value = def.display_name;
            document.getElementById('description').value = def.description || '';
            document.getElementById('category').value = def.category || '';
            document.getElementById('severity-override').value = def.severity_override || '';
            document.getElementById('original-description').value = def.original_description || '';
            document.getElementById('definition-modal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('definition-modal').classList.remove('active');
        }

        async function saveDefinition(event) {
            event.preventDefault();

            const editId = document.getElementById('edit-id').value;
            const data = {
                alert_code: document.getElementById('alert-code').value,
                display_name: document.getElementById('display-name').value,
                description: document.getElementById('description').value || null,
                category: document.getElementById('category').value || null,
                severity_override: document.getElementById('severity-override').value || null,
                original_description: document.getElementById('original-description').value || null
            };

            if (editId) {
                data.id = parseInt(editId);
            }

            try {
                const response = await fetch('api/command-center.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: editId ? 'update_alert_definition' : 'create_alert_definition',
                        ...data
                    })
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error || 'Failed to save');
                }

                showToast(editId ? 'Definition updated!' : 'Definition created!', 'success');
                closeModal();
                loadDefinitions();
                loadUnmapped();
            } catch (error) {
                console.error('Error saving:', error);
                showToast('Error: ' + error.message, 'error');
            }
        }

        async function deleteDefinition(id) {
            if (!confirm('Are you sure you want to delete this definition?')) {
                return;
            }

            try {
                const response = await fetch(`api/command-center.php?action=delete_alert_definition&id=${id}`);
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error || 'Failed to delete');
                }

                showToast('Definition deleted!', 'success');
                loadDefinitions();
                loadUnmapped();
            } catch (error) {
                console.error('Error deleting:', error);
                showToast('Error: ' + error.message, 'error');
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
    </script>
</body>
</html>
<?php
/*
CHANGELOG
2025-11-24 Codex
- Created Alert Definitions management page
- CRUD interface for managing alert code to description mappings
- Shows unmapped alerts from panel messages for easy definition creation
- Category and search filtering
- Stats overview for total definitions, unmapped alerts, and categories
*/
?>
