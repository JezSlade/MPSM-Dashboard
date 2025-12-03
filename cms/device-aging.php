<?php
/**
 * Device Aging Dashboard - Fleet Age Analysis View
 * Comprehensive analysis of device age calculated from serial numbers
 * Following Engineering Standards Rule 5: Flat File Structure
 */

require 'config.php';
require 'functions.php';

requireAuth();
trackVisit('/device-aging');

// Get user preferences
$userId = $_SESSION['user_id'];
$preferences = getUserPreferences($userId);
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($preferences['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Device Aging Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/device-aging.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body data-theme="<?= htmlspecialchars($preferences['theme'] ?? 'light') ?>">
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-clock"></i> Device Aging Dashboard</h1>
            <div class="header-actions">
                <a href="index.php" class="btn-icon" title="Customer View">
                    <i class="fas fa-user"></i>
                    <span style="margin-left: 0.5rem; font-size: 0.875rem;">Customer View</span>
                </a>
                <a href="dealer.php" class="btn-icon" title="Dealer Dashboard">
                    <i class="fas fa-chart-line"></i>
                    <span style="margin-left: 0.5rem; font-size: 0.875rem;">Dealer Dashboard</span>
                </a>
                <a href="duplicate-ips.php" class="btn-icon" title="Duplicate IPs">
                    <i class="fas fa-network-wired"></i>
                    <span style="margin-left: 0.5rem; font-size: 0.875rem;">Duplicate IPs</span>
                </a>
                <a href="command-center.php" class="btn-icon" title="Command Center">
                    <i class="fas fa-shield-alt"></i>
                </a>
                <button id="theme-toggle" class="btn-icon" title="Toggle theme">
                    <i class="fas fa-<?= $preferences['theme'] === 'dark' ? 'sun' : 'moon' ?>"></i>
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

    <!-- Main Content -->
    <main class="container">
        <!-- Alert Banner -->
        <div id="alert-banner" class="alert-banner" style="display: none;">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="alert-content">
                <strong>Aging Fleet Detected</strong>
                <p>A significant portion of your fleet is approaching or exceeding recommended replacement age.</p>
            </div>
        </div>

        <!-- Summary Statistics -->
        <section class="section">
            <div class="section-header">
                <h2><i class="fas fa-tachometer-alt"></i> Fleet Age Summary</h2>
                <div class="section-actions">
                    <button class="btn-refresh" onclick="loadDeviceAgeData(true)">
                        <i class="fas fa-sync-alt"></i> Force Refresh
                    </button>
                </div>
            </div>
            <div id="summary-cards" class="summary-grid">
                <div class="loading">Loading summary...</div>
            </div>
        </section>

        <!-- Visual Analytics -->
        <section class="section">
            <div class="section-header">
                <h2><i class="fas fa-chart-bar"></i> Visual Analysis</h2>
            </div>
            <div class="charts-grid">
                <div class="chart-card">
                    <h3>Fleet Age Distribution</h3>
                    <canvas id="age-distribution-chart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Average Age by Customer (Top 10)</h3>
                    <canvas id="customer-age-chart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Devices with Unknown Age</h3>
                    <canvas id="unknown-reasons-chart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Fleet Health Assessment</h3>
                    <canvas id="health-assessment-chart"></canvas>
                </div>
            </div>
        </section>

        <!-- Detailed Customer Table -->
        <section class="section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Customer Age Details</h2>
                <div class="section-actions">
                    <input type="search" id="customer-search" placeholder="Search customers..." autocomplete="off">
                    <select id="age-filter">
                        <option value="all">All Ages</option>
                        <option value="critical">Critical (5+ years)</option>
                        <option value="warning">Warning (3-5 years)</option>
                        <option value="good">Good (1-3 years)</option>
                        <option value="excellent">Excellent (<1 year)</option>
                    </select>
                </div>
            </div>
            <div id="customers-table-container">
                <div class="loading">Loading customers...</div>
            </div>
        </section>

        <!-- Footer -->
        <footer style="text-align: center; padding: 2rem 0; color: var(--text-secondary); font-size: 0.875rem;">
            <p><?= APP_NAME ?> Device Aging Dashboard &copy; <?= date('Y') ?></p>
            <p style="margin-top: 0.5rem;">
                Dealer: <strong><?= DEFAULT_DEALER_CODE ?></strong> |
                User: <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Unknown') ?></strong>
            </p>
        </footer>
    </main>

    <!-- Toast Container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <!-- Scripts -->
    <script src="assets/shared.js"></script>
    <script src="assets/device-aging.js"></script>
</body>
</html>

<?php
/*
CHANGELOG
2025-12-03 Claude
- Initial implementation: Standalone device aging dashboard
- Summary cards showing key age metrics
- 4 Chart.js visualizations for fleet age analysis
- Detailed customer table with search and filtering
- Reuses existing authentication and theme system
- Complements device-age-report.php API created by Codex
*/
?>
