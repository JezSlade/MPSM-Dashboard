<?php
/**
 * Duplicate IPs Dashboard - Specialized Analysis View
 * Comprehensive analysis of devices sharing IP addresses
 * Following Engineering Standards Rule 5: Flat File Structure
 */

require 'config.php';
require 'functions.php';

requireAuth();
trackVisit('/duplicate-ips');

// Get user preferences
$userId = $_SESSION['user_id'];
$preferences = getUserPreferences($userId);
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($preferences['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Duplicate IPs Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/duplicate-ips.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body data-theme="<?= htmlspecialchars($preferences['theme'] ?? 'light') ?>">
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-network-wired"></i> Duplicate IPs Dashboard</h1>
            <div class="header-actions">
                <a href="index.php" class="btn-icon" title="Customer View">
                    <i class="fas fa-user"></i>
                    <span style="margin-left: 0.5rem; font-size: 0.875rem;">Customer View</span>
                </a>
                <a href="dealer.php" class="btn-icon" title="Dealer Dashboard">
                    <i class="fas fa-chart-line"></i>
                    <span style="margin-left: 0.5rem; font-size: 0.875rem;">Dealer Dashboard</span>
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
                <strong>Critical Network Issue</strong>
                <p>Multiple devices are sharing IP addresses, which can cause network conflicts and connectivity issues.</p>
            </div>
        </div>

        <!-- Summary Statistics -->
        <section class="section">
            <div class="section-header">
                <h2><i class="fas fa-tachometer-alt"></i> Duplicate IP Summary</h2>
                <div class="section-actions">
                    <button class="btn-refresh" onclick="loadDuplicateIPData(true)">
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
                    <h3>Severity Distribution</h3>
                    <canvas id="severity-chart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Top 10 Duplicate IPs</h3>
                    <canvas id="top-ips-chart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Devices by Status</h3>
                    <canvas id="status-chart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Affected vs Healthy Devices</h3>
                    <canvas id="health-chart"></canvas>
                </div>
            </div>
        </section>

        <!-- Detailed Duplicate IPs Table -->
        <section class="section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Duplicate IP Details</h2>
                <div class="section-actions">
                    <input type="search" id="ip-search" placeholder="Search IPs or devices..." autocomplete="off">
                    <select id="severity-filter">
                        <option value="all">All Severity</option>
                        <option value="critical">Critical (10+)</option>
                        <option value="high">High (5-9)</option>
                        <option value="medium">Medium (3-4)</option>
                        <option value="low">Low (2)</option>
                    </select>
                </div>
            </div>
            <div id="duplicates-table-container">
                <div class="loading">Loading duplicates...</div>
            </div>
        </section>

        <!-- Footer -->
        <footer style="text-align: center; padding: 2rem 0; color: var(--text-secondary); font-size: 0.875rem;">
            <p><?= APP_NAME ?> Duplicate IPs Dashboard &copy; <?= date('Y') ?></p>
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
    <script src="assets/duplicate-ips.js"></script>
</body>
</html>

<?php
/*
CHANGELOG
2025-12-03 Claude
- Initial implementation: Standalone duplicate IPs dashboard
- Summary cards showing key metrics
- 4 Chart.js visualizations for analysis
- Detailed table with search and filtering
- Reuses existing authentication and theme system
- Will be integrated as drill-down modal in future
*/
?>
