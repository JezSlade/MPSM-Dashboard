<?php
require 'config.php';
require 'functions.php';

requireAuth();
trackVisit('/command-center');
$userId = $_SESSION['user_id'] ?? null;
$preferences = getUserPreferences($userId);
$theme = htmlspecialchars($preferences['theme'] ?? 'light', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Center - MPSM Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body data-theme="<?= $theme ?>">
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-shield-alt"></i> Command Center</h1>
            <div class="header-actions">
                <a class="btn-icon" href="index.php" title="Back to Dashboard">
                    <i class="fas fa-home"></i>
                </a>
                <a class="btn-icon" href="alert-definitions.php" title="Manage Alert Labels">
                    <i class="fas fa-tags"></i>
                </a>
                <button id="theme-toggle" class="btn-icon" title="Toggle theme">
                    <i class="fas fa-moon"></i>
                </button>
                <button id="refresh-btn" class="btn-icon" title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button id="logout-btn" class="btn-icon" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </header>

    <main class="monitor-container">
        <!-- Tab Navigation -->
        <div class="monitor-tabs">
            <button class="monitor-tab-btn active" data-tab="notifications">
                <i class="fas fa-bell"></i> Active Notifications
            </button>
            <button class="monitor-tab-btn" data-tab="panel">
                <i class="fas fa-stream"></i> Panel Stream
            </button>
            <button class="monitor-tab-btn" data-tab="rules">
                <i class="fas fa-cog"></i> Notification Rules
            </button>
            <button class="monitor-tab-btn" data-tab="definitions">
                <i class="fas fa-tags"></i> Alert Labels
            </button>
            <button class="monitor-tab-btn" data-tab="tools">
                <i class="fas fa-toolbox"></i> Tools
            </button>
            <button class="monitor-tab-btn" data-tab="statistics">
                <i class="fas fa-chart-bar"></i> Alert Statistics
            </button>
        </div>

        <!-- Tab 1: Active Notifications -->
        <div id="tab-notifications" class="tab-panel active" data-tab="notifications">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Active Notifications</h2>
                        <p class="text-muted">Dashboard notifications triggered by panel message rules</p>
                    </div>
                    <div class="monitor-controls">
                        <select id="notification-filter" class="form-control">
                            <option value="">All Severities</option>
                            <option value="critical">Critical</option>
                            <option value="high">High</option>
                            <option value="warning">Warning</option>
                            <option value="info">Info</option>
                        </select>
                        <select id="notification-customer-filter" class="form-control">
                            <option value="">All Customers</option>
                        </select>
                        <label class="toggle-label">
                            <input type="checkbox" id="notification-auto-refresh" checked>
                            <span>Auto-refresh (10s)</span>
                        </label>
                    </div>                </div>
                <div class="card-body">
                    <div id="notifications-container">
                        <div class="loading">Loading notifications...</div>
                    </div>
                    <div class="section-actions" style="margin-top:1rem; display:flex; justify-content:flex-end;">
                        <button id="notification-load-more" class="btn btn-secondary" style="display:none;">
                            <i class="fas fa-plus"></i> Load More
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Notification Rules -->
        <div id="tab-rules" class="tab-panel" data-tab="rules">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Notification Rules</h2>
                        <p class="text-muted">Define patterns and thresholds for automatic notifications</p>
                    </div>
                    <button id="create-rule-btn" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Rule
                    </button>
                </div>
                <div class="card-body">
                    <div id="rules-container">
                        <div class="loading">Loading rules...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Alert Statistics -->
                <!-- Tab: Panel Stream (Live Panel Messages) -->
        <div id="tab-panel" class="tab-panel" data-tab="panel">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Panel Stream</h2>
                        <p class="text-muted">Live panel message callbacks by time window and customer</p>
                    </div>
                    <div class="monitor-controls">
                        <div>
                            <label for="cc-panel-limit">Limit</label>
                            <select id="cc-panel-limit" class="form-control">
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200" selected>200</option>
                                <option value="300">300</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                        <div>
                            <label for="cc-panel-hours">Hours</label>
                            <select id="cc-panel-hours" class="form-control">
                                <option value="">All</option>
                                <option value="1">Last 1 hour</option>
                                <option value="6">Last 6 hours</option>
                                <option value="12">Last 12 hours</option>
                                <option value="24">Last 24 hours</option>
                                <option value="48">Last 48 hours</option>
                                <option value="72">Last 72 hours</option>
                                <option value="168">Last 7 days</option>
                            </select>
                        </div>
                        <div>
                            <label for="cc-panel-customer">Customer</label>
                            <input id="cc-panel-customer" class="form-control" type="text" placeholder="e.g., ABC123">
                            <small class="text-muted">Filters table; leave blank for all</small>
                        </div>
                        <div class="panel-paging">
                            <label>&nbsp;</label>
                            <div class="panel-paging-controls">
                                <button id="cc-panel-prev" class="btn btn-secondary" type="button">
                                    <i class="fas fa-chevron-left"></i> Prev
                                </button>
                                <span id="cc-panel-page" class="badge">Page 1</span>
                                <button id="cc-panel-next" class="btn btn-secondary" type="button">
                                    Next <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <button id="cc-panel-refresh" class="btn btn-primary" type="button">
                            <i class="fas fa-sync-alt"></i> Refresh Now
                        </button>
                        <span id="cc-panel-last-refresh" class="badge">Auto refresh: 30s</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Received</th>
                                    <th>Customer</th>
                                    <th>Device</th>
                                    <th>Alert</th>
                                    <th>Panel Config</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="cc-panel-tbody">
                                <tr><td colspan="6">Waiting for data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Alert Labels (Definitions) -->
        <div id="tab-definitions" class="tab-panel" data-tab="definitions">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Alert Labels</h2>
                        <p class="text-muted">Map MPSM alert codes to human-readable names</p>
                    </div>
                    <button class="btn btn-primary" onclick="openDefinitionModal()">
                        <i class="fas fa-plus"></i> Create Label
                    </button>
                </div>
                <div class="card-body">
                    <div id="definitions-container">
                        <div class="loading">Loading alert labels...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Tools (Lifecycle & Debugger) -->
        <div id="tab-tools" class="tab-panel" data-tab="tools">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Developer Tools</h2>
                        <p class="text-muted">Device management and payload testing utilities</p>
                    </div>
                </div>
                <div class="card-body">
                    <div style="display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); padding: 1rem;">
                        <!-- Device Lifecycle Tool -->
                        <div class="tool-card">
                            <div class="tool-icon">
                                <i class="fas fa-network-wired"></i>
                            </div>
                            <h3>Device Lifecycle Management</h3>
                            <p>Create, update, and retire devices while maintaining complete audit trails. Search by serial number, filter by customer, and manage device inventory.</p>
                            <div class="tool-features">
                                <div><i class="fas fa-check"></i> Create new devices</div>
                                <div><i class="fas fa-check"></i> Update device details</div>
                                <div><i class="fas fa-check"></i> Retire/archive devices</div>
                                <div><i class="fas fa-check"></i> Full audit history</div>
                            </div>
                            <a href="device-lifecycle.php" class="btn btn-primary" target="_blank" rel="noopener">
                                <i class="fas fa-external-link-alt"></i> Open Device Lifecycle
                            </a>
                        </div>

                        <!-- Payload Debugger Tool -->
                        <div class="tool-card">
                            <div class="tool-icon">
                                <i class="fas fa-bug"></i>
                            </div>
                            <h3>Payload Debugger</h3>
                            <p>Test and validate panel message payloads before sending to production. Inspect JSON structure, verify field mappings, and troubleshoot integration issues.</p>
                            <div class="tool-features">
                                <div><i class="fas fa-check"></i> Test JSON payloads</div>
                                <div><i class="fas fa-check"></i> Validate field mappings</div>
                                <div><i class="fas fa-check"></i> Debug API responses</div>
                                <div><i class="fas fa-check"></i> View processed output</div>
                            </div>
                            <a href="payload-debugger.php" class="btn btn-primary" target="_blank" rel="noopener">
                                <i class="fas fa-external-link-alt"></i> Open Payload Debugger
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-statistics" class="tab-panel" data-tab="statistics">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Alert Aggregations</h2>
                        <p class="text-muted">Frequency analysis of panel messages</p>
                    </div>
                    <div class="monitor-controls">
                        <select id="stats-sort" class="form-control">
                            <option value="recent">Most Recent</option>
                            <option value="frequent">Most Frequent</option>
                            <option value="critical">Critical First</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div id="statistics-container">
                        <div class="loading">Loading statistics...</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Rule Editor Modal -->
    <div id="rule-modal" class="modal" style="display: none;">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2 id="rule-modal-title">Create Notification Rule</h2>
                <button class="modal-close" onclick="closeRuleModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="rule-form">
                <div class="modal-body">
                    <input type="hidden" id="rule-id" name="id">

                    <!-- Basic Info -->
                    <div class="form-section">
                        <h3>Basic Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="rule-name">Rule Name <span class="required">*</span></label>
                                <input type="text" id="rule-name" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="rule-severity">Severity <span class="required">*</span></label>
                                <select id="rule-severity" name="severity" class="form-control" required>
                                    <option value="info">Info</option>
                                    <option value="warning">Warning</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="rule-description">Description</label>
                            <textarea id="rule-description" name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Pattern Matching -->
                    <div class="form-section">
                        <h3>Pattern Matching</h3>
                        <p class="form-help">Use % as wildcard (e.g., "E-%" matches E-001, E-002, etc.)</p>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="rule-alert-pattern">Alert Code Pattern</label>
                                <input type="text" id="rule-alert-pattern" name="alert_code_pattern" class="form-control" list="alert-code-options" placeholder="e.g., JAM% or E-001">
                            </div>
                            <div class="form-group">
                                <label for="rule-device-pattern">Device Serial Pattern</label>
                                <input type="text" id="rule-device-pattern" name="device_serial_pattern" class="form-control" list="device-serial-options" placeholder="e.g., SN-%">
                            </div>
                            <div class="form-group">
                                <label for="rule-customer-pattern">Customer (Name) Pattern</label>
                                <input type="text" id="rule-customer-pattern" name="customer_code_pattern" class="form-control" list="customer-code-options" placeholder="e.g., CAPE FEAR%">
                                <small class="field-hint">Select by customer name; the rule uses customer code under the hood. Use % as wildcard.</small>
                            </div>
                        </div>
                    </div>
                    <!-- Pattern suggestion lists (populated by JS) -->
                    <datalist id="alert-code-options"></datalist>
                    <datalist id="device-serial-options"></datalist>
                    <datalist id="customer-code-options"></datalist>

                    <!-- Frequency Threshold -->
                    <div class="form-section">
                        <h3>Frequency Threshold (Optional)</h3>
                        <p class="form-help">Only trigger notification if threshold is met</p>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="rule-freq-count">Occurrence Count</label>
                                <input type="number" id="rule-freq-count" name="frequency_count" class="form-control" min="1" placeholder="e.g., 5">
                            </div>
                            <div class="form-group">
                                <label for="rule-freq-window">Time Window (hours)</label>
                                <input type="number" id="rule-freq-window" name="frequency_window_hours" class="form-control" min="1" placeholder="e.g., 24">
                            </div>
                            <div class="form-group">
                                <label for="rule-freq-type">Frequency Type</label>
                                <select id="rule-freq-type" name="frequency_type" class="form-control">
                                    <option value="same_device">Same Device</option>
                                    <option value="same_alert">Same Alert (Any Device)</option>
                                    <option value="same_customer">Same Customer</option>
                                    <option value="any">Any (Total Count)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Template -->
                    <div class="form-section">
                        <h3>Notification Template</h3>
                        <p class="form-help">Available variables: {severity}, {device}, {alert}, {customer}, {count}, {window}, {rule_name}</p>
                        <div class="form-group">
                            <label for="rule-title">Notification Title</label>
                            <input type="text" id="rule-title" name="notification_title" class="form-control" placeholder="{severity} Alert - {device} has {alert}">
                        </div>
                        <div class="form-group">
                            <label for="rule-message">Notification Message</label>
                            <textarea id="rule-message" name="notification_message" class="form-control" rows="2" placeholder="{device} has triggered {alert} {count} times in the past {window}"></textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-section">
                        <h3>Actions</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="rule-show-dashboard" name="show_dashboard" checked>
                                    Show in Dashboard Hero Header
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="rule-auto-dismiss">Auto-dismiss (hours)</label>
                                <input type="number" id="rule-auto-dismiss" name="auto_dismiss_hours" class="form-control" min="1" placeholder="e.g., 24">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeRuleModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Rule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Definition Editor Modal -->
    <div id="definition-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="definition-modal-title">Create Alert Label</h2>
                <button class="modal-close" onclick="closeDefinitionModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="definition-form">
                <div class="modal-body">
                    <input type="hidden" id="definition-id" name="id">

                    <div class="form-group">
                        <label for="definition-alert-code">Alert Code <span class="required">*</span></label>
                        <input type="text" id="definition-alert-code" name="alert_code" class="form-control" required placeholder="e.g., E-001">
                    </div>

                    <div class="form-group">
                        <label for="definition-display-name">Display Name <span class="required">*</span></label>
                        <input type="text" id="definition-display-name" name="display_name" class="form-control" required placeholder="e.g., Emergency Stop">
                    </div>

                    <div class="form-group">
                        <label for="definition-category">Category</label>
                        <input type="text" id="definition-category" name="category" class="form-control" placeholder="e.g., Safety">
                    </div>

                    <div class="form-group">
                        <label for="definition-severity">Severity Override</label>
                        <select id="definition-severity" name="severity_override" class="form-control">
                            <option value="">None</option>
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="definition-description">Description</label>
                        <textarea id="definition-description" name="description" class="form-control" rows="3" placeholder="Optional detailed description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDefinitionModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Label
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <script src="assets/shared.js"></script>
    <script src="assets/app.js"></script>
    <script src="assets/command-center.js"></script>
</body>
</html>
<!--
CHANGELOG
2025-11-26 Codex
- Added searchable datalist suggestions for Alert/Device/Customer patterns in rule modal.
- No layout change to markup structure; relies on new modal CSS for proper sizing and scrolling.
2025-11-28 Codex
- Added Alert Labels CRUD: Create Label button, definition modal, Edit/Delete action buttons
- Removed "Manage in full" link (functionality now integrated into Command Center)
- Fixed Tools tab: Removed iframes, replaced with tool cards linking to standalone tools
- Tool cards now open device-lifecycle.php and payload-debugger.php in new tabs
-->

