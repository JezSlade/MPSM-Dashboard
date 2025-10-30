<?php
/**
 * MPSM Dashboard - Main Entry Point
 * Following Engineering Standards Rule 5: Flat File Structure
 */

require 'config.php';
require 'functions.php';

requireAuth();
trackVisit('/');

// Initialize tables on first run
try {
    initializeTables();
} catch (Exception $e) {
    error_log("Table initialization warning: " . $e->getMessage());
}

// Get user preferences
$userId = $_SESSION['user_id'];
$preferences = getUserPreferences($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body data-theme="<?= htmlspecialchars($preferences['theme'] ?? 'light') ?>">
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1><?= APP_NAME ?></h1>
            <div class="header-search">
                <div class="global-device-search">
                    <i class="fas fa-search"></i>
                    <input type="search" id="global-device-search" placeholder="Search all devices..." autocomplete="off">
                    <div id="global-search-results" class="global-search-dropdown" style="display:none;"></div>
                </div>
            </div>
            <div class="header-actions">
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

    <!-- Navigation -->
    <nav class="nav">
        <div class="container">
            <button class="nav-tab active" data-tab="dashboard">Dashboard</button>
            <button class="nav-tab" data-tab="admin">Admin</button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container">
        <!-- Dashboard Tab -->
        <div id="dashboard-tab" class="tab-content active">
            <!-- Customer Header with Metric Cards -->
            <section id="customer-header" class="customer-header">
                <div class="loading">Loading...</div>
            </section>

            <!-- Dashboard Grid - 2x2 Snapshot Cards -->
            <div class="dashboard-grid">
                <!-- Hidden elements for backward compatibility -->
                <span id="device-count" style="display:none;">0</span>
                <span id="alert-count" style="display:none;">0</span>
                <span id="connectors-hidden-count" style="display:none;">0</span>
                <div id="device-list" style="display:none;"></div>
                <div id="supply-alerts" style="display:none;"></div>
                <div id="dashboard-card-container" class="dashboard-card-grid"></div>
            </div>
        </div>

        <!-- Admin Tab -->
        <div id="admin-tab" class="tab-content">
            <!-- Admin Section Navigation -->
            <div class="admin-nav">
                <button class="admin-nav-btn active" data-section="customer">
                    <i class="fas fa-building"></i>
                    <span>Customer Settings</span>
                </button>
                <button class="admin-nav-btn" data-section="system">
                    <i class="fas fa-heartbeat"></i>
                    <span>System Monitoring</span>
                </button>
                <button class="admin-nav-btn" data-section="catalog">
                    <i class="fas fa-sitemap"></i>
                    <span>Endpoint Catalog</span>
                </button>
                <button class="admin-nav-btn" data-section="dashboard">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard Config</span>
                </button>
                <button class="admin-nav-btn" data-section="users">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </button>
            </div>

            <!-- Customer Settings Section -->
            <div id="admin-customer" class="admin-section active">
                <div class="admin-grid">
                    <section class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-cog"></i> API Configuration</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label><i class="fas fa-tag"></i> Dealer Code:</label>
                                <input type="text" id="dealer-code" class="form-control" value="<?= htmlspecialchars($preferences['dealerCode'] ?? DEFAULT_DEALER_CODE) ?>">
                                <small class="form-help">Your unique dealer identifier for API requests</small>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-fingerprint"></i> Dealer ID:</label>
                                <input type="text" id="dealer-id" class="form-control" value="<?= htmlspecialchars($preferences['dealerId'] ?? DEFAULT_DEALER_ID) ?>">
                                <small class="form-help">Numeric dealer ID for backend authentication</small>
                            </div>
                        </div>
                    </section>

                    <section class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-building"></i> Active Customer</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label><i class="fas fa-search"></i> Find Customer:</label>
                                <input type="text" id="customer-search" class="form-control" placeholder="Search by name or code">
                                <small class="form-help">Type at least 2 characters to filter customers</small>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-list"></i> Select Customer:</label>
                                <select id="customer-select" class="form-control">
                                    <option value="">Loading customers...</option>
                                </select>
                                <small class="form-help">Choose a customer to populate the fields below</small>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-hashtag"></i> Customer Code:</label>
                                <input type="text" id="customer-code" class="form-control" value="<?= htmlspecialchars($preferences['customerCode'] ?? DEFAULT_CUSTOMER_CODE) ?>">
                                <small class="form-help">Customer code to display on dashboard</small>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Customer Name:</label>
                                <input type="text" id="customer-name" class="form-control" value="<?= htmlspecialchars($preferences['customerName'] ?? DEFAULT_CUSTOMER_NAME) ?>">
                                <small class="form-help">Display name for customer header banner</small>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="admin-actions">
                    <button id="save-settings" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                    <button class="btn btn-secondary" onclick="location.reload()">
                        <i class="fas fa-undo"></i> Reset Changes
                    </button>
                </div>
            </div>

            <!-- System Monitoring Section -->
            <div id="admin-system" class="admin-section">
                <div class="admin-grid">
                    <section class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-heartbeat"></i> System Health</h2>
                            <button id="test-health" class="btn btn-secondary">
                                <i class="fas fa-sync-alt"></i> Test Now
                            </button>
                        </div>
                        <div id="health-status" class="card-body">
                            <div class="loading">Click "Test Now" to check system health...</div>
                        </div>
                    </section>

                    <section class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-chart-line"></i> Visitor Tracking</h2>
                            <button id="refresh-visitors" class="btn btn-secondary">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                        <div id="visitor-logs" class="card-body">
                            <div class="loading">Loading visitor logs...</div>
                        </div>
                    </section>
                </div>
            </div>

            <!-- Dashboard Configuration Section -->
            <div id="admin-catalog" class="admin-section">
                <section class="card">
                    <div class="card-header catalog-header">
                        <h2><i class="fas fa-sitemap"></i> Endpoint Catalog</h2>
                        <div class="catalog-actions">
                            <input type="search" id="catalog-search" class="form-control" placeholder="Search endpoints">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="catalog-layout">
                            <aside class="catalog-sidebar">
                                <ul id="catalog-category-list" class="catalog-category-list"></ul>
                            </aside>
                            <section class="catalog-content">
                                <div id="catalog-stats" class="catalog-stats"></div>
                                <div id="catalog-table" class="catalog-table"></div>
                            </section>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Dashboard Configuration Section -->
            <div id="admin-dashboard" class="admin-section">
                <section class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-th-large"></i> Dashboard Cards</h2>
                    </div>
                    <div class="card-body">
                        <p class="info-text">
                            <i class="fas fa-info-circle"></i>
                            Configure which metric cards appear on your dashboard. Click cards to expand to detailed views.
                        </p>
                        <div id="dashboard-card-config" class="dashboard-config-grid"></div>
                    </div>
                </section>
            </div>

            <!-- User Management Section -->
            <div id="admin-users" class="admin-section">
                <section class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-users"></i> User Management</h2>
                    </div>
                    <div class="card-body">
                        <div class="user-management">
                            <form id="user-create-form" class="user-form">
                                <h3><i class="fas fa-user-plus"></i> Add User</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Username</label>
                                        <input type="text" id="create-username" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" id="create-password" class="form-control" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> Create User
                                </button>
                            </form>
                            <div class="user-table-wrapper">
                                <table id="user-table" class="table">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="3">
                                                <div class="loading">Loading users...</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Device Detail Modal -->
    <div id="device-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-device-name">Device Details</h2>
                <button class="modal-close" onclick="closeDeviceModal()">&times;</button>
            </div>
            <div id="modal-device-body" class="modal-body">
                <div class="loading">Loading device details...</div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/table-utils.js"></script>
    <script src="assets/js/card-registry.js"></script>
    <script src="assets/js/card-manager.js"></script>
    <script src="assets/app.js"></script>
</body>
</html>
