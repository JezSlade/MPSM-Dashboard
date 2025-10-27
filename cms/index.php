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
            <!-- Customer Header -->
            <section id="customer-header" class="customer-header">
                <div class="loading">Loading...</div>
            </section>

            <!-- Device List -->
            <section class="card">
                <div class="card-header">
                    <h2>Fleet Devices</h2>
                    <span id="device-count" class="badge">0</span>
                </div>
                <div id="device-list" class="card-body">
                    <div class="loading">Loading devices...</div>
                </div>
            </section>

            <!-- Supply Alerts -->
            <section class="card">
                <div class="card-header">
                    <h2>Supply Alerts & Warnings</h2>
                    <span id="alert-count" class="badge badge-warning">0</span>
                </div>
                <div id="supply-alerts" class="card-body">
                    <div class="loading">Loading supply alerts...</div>
                </div>
            </section>
        </div>

        <!-- Admin Tab -->
        <div id="admin-tab" class="tab-content">
            <section class="card">
                <div class="card-header">
                    <h2>Settings</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Dealer Code:</label>
                        <input type="text" id="dealer-code" class="form-control" value="<?= htmlspecialchars($preferences['dealerCode'] ?? DEFAULT_DEALER_CODE) ?>">
                    </div>
                    <div class="form-group">
                        <label>Dealer ID:</label>
                        <input type="text" id="dealer-id" class="form-control" value="<?= htmlspecialchars($preferences['dealerId'] ?? DEFAULT_DEALER_ID) ?>">
                    </div>
                    <div class="form-group">
                        <label>Customer Code:</label>
                        <input type="text" id="customer-code" class="form-control" value="<?= htmlspecialchars($preferences['customerCode'] ?? DEFAULT_CUSTOMER_CODE) ?>">
                    </div>
                    <div class="form-group">
                        <label>Customer Name:</label>
                        <input type="text" id="customer-name" class="form-control" value="<?= htmlspecialchars($preferences['customerName'] ?? DEFAULT_CUSTOMER_NAME) ?>">
                    </div>
                    <button id="save-settings" class="btn btn-primary">Save Settings</button>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2>System Health</h2>
                    <button id="test-health" class="btn btn-secondary">Test Now</button>
                </div>
                <div id="health-status" class="card-body">
                    <div class="loading">Click "Test Now" to check system health...</div>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2>Visitor Tracking</h2>
                    <button id="refresh-visitors" class="btn btn-secondary">Refresh</button>
                </div>
                <div id="visitor-logs" class="card-body">
                    <div class="loading">Loading visitor logs...</div>
                </div>
            </section>
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
    <script src="assets/app.js"></script>
</body>
</html>
