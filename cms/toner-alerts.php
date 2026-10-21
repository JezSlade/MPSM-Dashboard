<?php
/**
 * Toner Alerts - all toner supply alerts for a selectable dealer + customer
 * Following Engineering Standards Rule 5: Flat File Structure
 */
require 'config.php';
require 'functions.php';

requireAuth();
trackVisit('/toner-alerts');

$userId = $_SESSION['user_id'];
$preferences = getUserPreferences($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Toner Alerts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/toner-alerts.css">
</head>
<body class="toner-alerts"
      data-dealer-code="<?= htmlspecialchars($preferences['dealerCode'] ?? DEFAULT_DEALER_CODE) ?>"
      data-customer-code="<?= htmlspecialchars($preferences['customerCode'] ?? '') ?>">
    <header class="ta-header">
        <h1><i class="fas fa-print"></i> Toner Alerts</h1>
        <nav>
            <a href="index.php" title="Dashboard"><i class="fas fa-home"></i> Dashboard</a>
            <a href="dealer.php" title="Dealer Dashboard"><i class="fas fa-chart-line"></i> Dealer Dashboard</a>
        </nav>
    </header>

    <div class="ta-filters">
        <div>
            <label for="ta-dealer">Dealership</label>
            <select id="ta-dealer"><option value="">Loading dealers...</option></select>
        </div>
        <div>
            <label for="ta-customer">Customer</label>
            <select id="ta-customer"><option value="">All Customers</option></select>
        </div>
        <button id="ta-refresh"><i class="fas fa-sync-alt"></i> Refresh</button>
    </div>

    <div class="ta-summary" id="ta-summary"></div>

    <div class="ta-table-wrap">
        <table class="ta-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Device</th>
                    <th>Location</th>
                    <th>Supply</th>
                    <th>Residual</th>
                    <th>Warning</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody id="ta-rows">
                <tr><td colspan="7" class="ta-loading">Select a dealership to load toner alerts...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="ta-pagination">
        <button id="ta-prev">&larr; Prev</button>
        <span id="ta-page-label"></span>
        <button id="ta-next">Next &rarr;</button>
    </div>

    <script src="assets/shared.js"></script>
    <script src="assets/toner-alerts.js"></script>
</body>
</html>
