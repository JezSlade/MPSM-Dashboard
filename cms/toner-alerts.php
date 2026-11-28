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
$layout = match ($_GET['view'] ?? '') {
    'mobile' => 'mobile',
    'desktop' => 'desktop',
    default => 'auto',
};
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
<body class="toner-alerts ta-layout-<?= $layout ?>"
      data-dealer-code="<?= htmlspecialchars($preferences['dealerCode'] ?? DEFAULT_DEALER_CODE) ?>"
      data-customer-code="<?= htmlspecialchars($preferences['customerCode'] ?? '') ?>">
    <header class="ta-header">
        <h1><i class="fas fa-print"></i> Toner Alerts</h1>
        <nav aria-label="Dashboard navigation">
            <a class="ta-mobile-link" href="toner-alerts.php?view=mobile"><i class="fas fa-mobile-screen" aria-hidden="true"></i> Mobile view</a>
            <a class="ta-desktop-link" href="toner-alerts.php?view=desktop"><i class="fas fa-desktop" aria-hidden="true"></i> Desktop view</a>
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

    <div class="ta-summary" id="ta-summary" role="status"></div>

    <div class="ta-table-wrap">
        <table class="ta-table" role="table" aria-label="Toner alerts by device">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader">Toner Alerts</th>
                    <th scope="col" role="columnheader">Device Model</th>
                    <th scope="col" role="columnheader">Systel Equip ID</th>
                    <th scope="col" role="columnheader">Serial Number</th>
                    <th scope="col" role="columnheader">Location</th>
                    <th scope="col" role="columnheader">Days Open</th>
                </tr>
            </thead>
            <tbody id="ta-rows" role="rowgroup">
                <tr><td colspan="6" class="ta-loading">Select a dealership to load toner alerts...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="ta-pagination">
        <button id="ta-prev">&larr; Prev</button>
        <span id="ta-page-label"></span>
        <button id="ta-next">Next &rarr;</button>
    </div>

    <dialog id="ta-device-dialog" class="ta-modal" aria-labelledby="ta-modal-title">
        <button id="ta-modal-close" type="button" class="ta-modal-close" aria-label="Close device details" autofocus>
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
        <div id="ta-modal-body"></div>
    </dialog>

    <script src="assets/shared.js"></script>
    <script src="assets/toner-alerts.js"></script>
</body>
</html>

<?php
/*
CHANGELOG
2026-11-25 Codex
- Renamed the toner-alert age column from Occurrences to Days Open.
2026-11-27 Codex
- Link mobile and desktop layouts and use a native device details dialog.
*/
?>
