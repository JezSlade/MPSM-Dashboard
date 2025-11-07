<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
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
<body>
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-shield-alt"></i> Command Center</h1>
            <div class="header-actions">
                <a class="btn-icon" href="index.php" title="Back to Dashboard">
                    <i class="fas fa-home"></i>
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
            <button class="monitor-tab-btn" data-tab="rules">
                <i class="fas fa-cog"></i> Notification Rules
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
                        <label class="toggle-label">
                            <input type="checkbox" id="notification-auto-refresh" checked>
                            <span>Auto-refresh (10s)</span>
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div id="notifications-container">
                        <div class="loading">Loading notifications...</div>
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
                                <input type="text" id="rule-alert-pattern" name="alert_code_pattern" class="form-control" placeholder="e.g., JAM% or E-001">
                            </div>
                            <div class="form-group">
                                <label for="rule-device-pattern">Device Serial Pattern</label>
                                <input type="text" id="rule-device-pattern" name="device_serial_pattern" class="form-control" placeholder="e.g., SN-%">
                            </div>
                            <div class="form-group">
                                <label for="rule-customer-pattern">Customer Code Pattern</label>
                                <input type="text" id="rule-customer-pattern" name="customer_code_pattern" class="form-control" placeholder="e.g., CUST-%">
                            </div>
                        </div>
                    </div>

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

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <script src="assets/app.js"></script>
    <script src="assets/command-center.js"></script>
</body>
</html>
