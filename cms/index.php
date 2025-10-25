<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPS Monitor Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/card-management.css">
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
            <button class="nav-tab" data-tab="admin">Settings</button>
            <button class="nav-tab" data-tab="cards">Card Management</button>
            <button class="nav-tab" data-tab="cache">Cache</button>
            <button class="nav-tab" data-tab="traffic">Traffic</button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container">
        <!-- Dashboard Tab -->
        <div id="dashboard-tab" class="tab-content active">
            <!-- Customer Overview Header -->
            <section id="customer-dashboard" class="customer-header">
                <div class="loading">Loading overview...</div>
            </section>

            <!-- Dynamic Dashboard Cards Grid -->
            <div class="dashboard-grid">
                <!-- Cards will be dynamically rendered by CardManager -->
            </div>
        </div>


        <!-- Settings Tab -->
        <div id="admin-tab" class="tab-content">
            <div class="admin-container">
                <h2>Dashboard Settings</h2>

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

                <!-- Dashboard Options -->
                <section class="admin-section">
                    <h3>Dashboard Options</h3>
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

        <!-- Card Management Tab -->
        <div id="cards-tab" class="tab-content">
            <div class="card-management-wrapper">
                <h2>Card Management</h2>
                <p class="help-text">Customize which cards appear on your dashboard and arrange their order by dragging.</p>
                <div class="card-management-container">
                    <!-- Card management UI will be rendered here by CardManager -->
                </div>
            </div>
        </div>

        <!-- Cache Dashboard Tab -->
        <div id="cache-tab" class="tab-content">
            <div class="cache-dashboard-wrapper">
                <h2>Cache Dashboard</h2>
                <section class="admin-section">
                    <div class="cache-overview">
                        <div class="cache-stat-grid">
                            <div class="cache-stat-card">
                                <div class="cache-stat-label">Hit Rate</div>
                                <div class="cache-stat-value" id="cache-hit-rate">-</div>
                            </div>
                            <div class="cache-stat-card">
                                <div class="cache-stat-label">Total Entries</div>
                                <div class="cache-stat-value" id="cache-total-entries">-</div>
                            </div>
                            <div class="cache-stat-card">
                                <div class="cache-stat-label">Cache Size</div>
                                <div class="cache-stat-value" id="cache-total-size">-</div>
                            </div>
                            <div class="cache-stat-card">
                                <div class="cache-stat-label">Hits / Misses</div>
                                <div class="cache-stat-value" id="cache-hit-miss">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="cache-actions">
                        <button id="refresh-cache-stats" class="btn btn-secondary">🔄 Refresh Stats</button>
                        <button id="warm-cache" class="btn btn-primary">🔥 Warm Cache</button>
                        <button id="clear-all-cache" class="btn btn-danger">🗑️ Clear All Cache</button>
                    </div>
                    <div id="cache-entries-container" style="margin-top: 20px;">
                        <h4>Cache Entries</h4>
                        <div id="cache-entries-list" class="loading">Loading cache data...</div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Traffic Metrics Tab -->
        <div id="traffic-tab" class="tab-content">
            <div class="traffic-dashboard-wrapper">
                <h2>Traffic Metrics</h2>
                <section class="admin-section">
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
    <script src="assets/js/card-registry.js"></script>
    <script src="assets/js/card-manager.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
