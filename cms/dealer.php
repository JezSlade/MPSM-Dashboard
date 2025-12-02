<?php
/**
 * Dealer Dashboard - Dealer-Level Intelligence View
 * Presents aggregate metrics across all customers for dealer decision-making
 * Following Engineering Standards Rule 5: Flat File Structure
 */

require 'config.php';
require 'functions.php';

requireAuth();
trackVisit('/dealer');

// Get user preferences
$userId = $_SESSION['user_id'];
$preferences = getUserPreferences($userId);
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($preferences['theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Dealer Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/dealer.css">
</head>
<body data-theme="<?= htmlspecialchars($preferences['theme'] ?? 'light') ?>">
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-chart-line"></i> Dealer Dashboard</h1>
            <div class="header-actions">
                <a href="index.php" class="btn-icon" title="Customer View">
                    <i class="fas fa-user"></i>
                    <span style="margin-left: 0.5rem; font-size: 0.875rem;">Customer View</span>
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
        <!-- Dealer Scorecard -->
        <section class="section">
            <div class="section-header">
                <h2><i class="fas fa-tachometer-alt"></i> Dealer Scorecard</h2>
                <div class="section-actions">
                    <button class="btn-refresh" onclick="loadDealerDashboard(true)">
                        <i class="fas fa-sync-alt"></i> Force Refresh
                    </button>
                </div>
            </div>
            <div id="dealer-scorecard" class="dealer-scorecard">
                <div class="loading">Loading metrics...</div>
            </div>
        </section>

        <!-- Data Quality Dashboard -->
        <section class="section">
            <div class="section-header">
                <h2><i class="fas fa-clipboard-check"></i> Data Quality Dashboard</h2>
            </div>
            <div id="data-quality-container">
                <div class="loading">Loading quality metrics...</div>
            </div>
        </section>

        <!-- Customer Portfolio Table -->
        <section class="section">
            <div class="section-header">
                <h2><i class="fas fa-building"></i> Customer Portfolio</h2>
                <div class="section-actions">
                    <input type="search" id="portfolio-search" placeholder="Search customers..." autocomplete="off">
                    <select id="portfolio-filter">
                        <option value="all">All Customers</option>
                        <option value="critical">Critical Health (&lt;70%)</option>
                        <option value="attention">Needs Attention (70-90%)</option>
                        <option value="healthy">Healthy (90%+)</option>
                    </select>
                </div>
            </div>
            <div id="portfolio-table-container">
                <div class="loading">Loading portfolio...</div>
            </div>
        </section>

        <!-- Footer -->
        <footer style="text-align: center; padding: 2rem 0; color: var(--text-secondary); font-size: 0.875rem;">
            <p><?= APP_NAME ?> Dealer Dashboard &copy; <?= date('Y') ?></p>
            <p style="margin-top: 0.5rem;">
                Dealer: <strong><?= DEFAULT_DEALER_CODE ?></strong> |
                User: <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Unknown') ?></strong>
            </p>
        </footer>
    </main>

    <!-- Scripts -->
    <script src="assets/shared.js"></script>
    <script src="assets/dealer.js"></script>
</body>
</html>

<?php
/*
CHANGELOG
2025-12-02 Claude
- Initial implementation: Dealer dashboard for dealer-level intelligence
- Three main sections: Dealer Scorecard (12 metrics), Data Quality Dashboard (4 metrics), Customer Portfolio (table)
- Reuses existing authentication, theme system, and UI patterns from index.php
- Integrates with dealer.js for data fetching and rendering
- Provides drill-down to customer view via portfolio table
2025-12-06 Codex
- Rebranded page strings, assets, IDs, and tracking path from Executive to Dealer throughout the dashboard shell.
*/
?>
