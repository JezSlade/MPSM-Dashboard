<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPS Monitor Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body data-theme="light">
    <!-- Header -->
    <header class="dashboard-header">
        <div class="container">
            <h1>MPS Monitor Dashboard</h1>
            <div class="header-actions">
                <button id="theme-toggle" class="btn-icon" title="Toggle theme">
                    <span class="icon-theme">🌙</span>
                </button>
                <button id="refresh-btn" class="btn-icon" title="Refresh">
                    <span class="icon-refresh">🔄</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="dashboard-nav">
        <div class="container">
            <button class="nav-tab active" data-tab="dashboard">Dashboard</button>
            <button class="nav-tab" data-tab="admin">Admin</button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container">
        <!-- Dashboard Tab -->
        <div id="dashboard-tab" class="tab-content active">
            <!-- Customer Dashboard Header -->
            <section id="customer-dashboard" class="customer-header">
                <div class="loading">Loading customer dashboard...</div>
            </section>

            <!-- Dashboard Cards Grid -->
            <div class="dashboard-grid">
                <!-- Printer List Card -->
                <div class="card card-printers">
                    <div class="card-header">
                        <h2>Printers</h2>
                        <span class="card-count" id="printer-count">0</span>
                    </div>
                    <div class="card-body">
                        <div id="printer-list" class="loading">Loading printers...</div>
                    </div>
                </div>

                <!-- Errors & Alerts Card -->
                <div class="card card-errors">
                    <div class="card-header">
                        <h2>Errors & Alerts</h2>
                        <span class="card-count" id="error-count">0</span>
                    </div>
                    <div class="card-body">
                        <div id="error-list" class="loading">Loading errors...</div>
                    </div>
                </div>

                <!-- Toner Levels Card -->
                <div class="card card-toner">
                    <div class="card-header">
                        <h2>Toner Levels</h2>
                    </div>
                    <div class="card-body">
                        <div id="toner-list" class="loading">Loading toner status...</div>
                    </div>
                </div>

                <!-- Meter Reads Card -->
                <div class="card card-meters">
                    <div class="card-header">
                        <h2>Meter Reads</h2>
                    </div>
                    <div class="card-body">
                        <div id="meter-list" class="loading">Loading meter data...</div>
                    </div>
                </div>

                <!-- Recent Activity Card -->
                <div class="card card-activity">
                    <div class="card-header">
                        <h2>Recent Activity</h2>
                    </div>
                    <div class="card-body">
                        <div id="activity-list" class="loading">Loading activity...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Tab -->
        <div id="admin-tab" class="tab-content">
            <div class="admin-container">
                <h2>Administration</h2>

                <!-- Default Settings -->
                <section class="admin-section">
                    <h3>Default Settings</h3>
                    <div class="form-group">
                        <label for="dealer-select">Default Dealer Code:</label>
                        <select id="dealer-select" class="form-control">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="customer-select">Default Customer:</label>
                        <select id="customer-select" class="form-control">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    <button id="save-defaults" class="btn btn-primary">Save Defaults</button>
                </section>

                <!-- Dashboard Settings -->
                <section class="admin-section">
                    <h3>Dashboard Settings</h3>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="auto-refresh">
                            Auto-refresh dashboard
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="refresh-interval">Refresh interval (seconds):</label>
                        <input type="number" id="refresh-interval" class="form-control" value="60" min="10" max="300">
                    </div>
                </section>

                <!-- Traffic Metrics -->
                <section class="admin-section">
                    <h3>Traffic Metrics</h3>
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-label">Total Visitors</div>
                            <div class="metric-value" id="total-visitors">0</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-label">Unique Visitors</div>
                            <div class="metric-value" id="unique-visitors">0</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-label">Active Sessions</div>
                            <div class="metric-value" id="active-sessions">0</div>
                        </div>
                    </div>
                    <div class="access-log">
                        <h4>Recent Access Log</h4>
                        <div id="access-log-list"></div>
                    </div>
                </section>

                <!-- Data Management -->
                <section class="admin-section">
                    <h3>Data Management</h3>
                    <button id="clear-cache" class="btn">Clear Cache</button>
                    <button id="export-settings" class="btn">Export Settings</button>
                    <button id="import-settings" class="btn">Import Settings</button>
                    <input type="file" id="import-file" accept=".json" style="display:none">
                </section>
            </div>
        </div>
    </main>

    <!-- Device Detail Modal -->
    <div id="device-modal" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Device Details</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="modal-body">
                <div class="loading">Loading device details...</div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container"></div>

    <!-- Scripts -->
    <script src="assets/js/table-utils.js"></script>
    <script src="assets/js/api.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
