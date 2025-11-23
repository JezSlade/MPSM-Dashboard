<?php
require 'config.php';
require 'functions.php';

requireAuth();
trackVisit('/mobile');

// Initialize tables to keep experience aligned with main dashboard
try {
    initializeTables();
} catch (Exception $e) {
    error_log("Mobile landing init warning: " . $e->getMessage());
}

$userId = $_SESSION['user_id'];
$preferences = getUserPreferences($userId);

$theme = htmlspecialchars($preferences['theme'] ?? 'light', ENT_QUOTES, 'UTF-8');
$customerCode = htmlspecialchars($preferences['customerCode'] ?? DEFAULT_CUSTOMER_CODE, ENT_QUOTES, 'UTF-8');
$customerName = htmlspecialchars($preferences['customerName'] ?? DEFAULT_CUSTOMER_NAME, ENT_QUOTES, 'UTF-8');
$dealerCode = htmlspecialchars($preferences['dealerCode'] ?? DEFAULT_DEALER_CODE, ENT_QUOTES, 'UTF-8');
$dealerId = htmlspecialchars($preferences['dealerId'] ?? DEFAULT_DEALER_ID, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>MPSM Mobile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/mobile.css">
</head>
<body data-theme="<?php echo $theme; ?>">
    <div class="mobile-page">
        <header class="mobile-header">
            <div class="header-top">
                <div class="brand">
                    <span class="brand-icon"><i class="fas fa-bolt"></i></span>
                    <div>
                        <div class="brand-label">MPSM</div>
                        <div class="brand-sub">Mobile</div>
                    </div>
                </div>
                <div class="quick-links">
                    <a class="icon-btn" href="index.php" title="Full Dashboard"><i class="fas fa-desktop"></i></a>
                    <button class="icon-btn" id="mobile-refresh" title="Refresh">
                        <i class="fas fa-rotate"></i>
                    </button>
                    <button class="icon-btn" id="mobile-logout" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
            <div class="header-context">
                <div>
                    <div class="context-label">Customer</div>
                    <div class="context-value"><?php echo $customerName ?: 'Active Customer'; ?></div>
                </div>
                <div class="context-chip">
                    <i class="fas fa-hashtag"></i>
                    <span><?php echo $customerCode; ?></span>
                </div>
            </div>
            <div class="search-box">
                <label for="mobile-search" class="sr-only">Search devices</label>
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="search" id="mobile-search" placeholder="Search devices by serial, model, asset..." autocomplete="off" inputmode="search">
                    <button id="mobile-search-clear" class="icon-btn small" title="Clear search" aria-label="Clear search">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="mobile-search-results" class="mobile-search-results"></div>
            </div>
        </header>

        <main class="mobile-content">
            <section class="mobile-quick-grid">
                <button class="quick-card" data-target="alerts">
                    <div class="quick-icon critical"><i class="fas fa-bell"></i></div>
                    <div>
                        <div class="quick-label">Alerts</div>
                        <div id="mobile-alert-count" class="quick-value">--</div>
                    </div>
                </button>
                <button class="quick-card" data-target="lookup">
                    <div class="quick-icon primary"><i class="fas fa-print"></i></div>
                    <div>
                        <div class="quick-label">Lookup</div>
                        <div class="quick-value">Devices</div>
                    </div>
                </button>
                <a class="quick-card" href="device-lifecycle.php">
                    <div class="quick-icon success"><i class="fas fa-server"></i></div>
                    <div>
                        <div class="quick-label">Lifecycle</div>
                        <div class="quick-value">Manage</div>
                    </div>
                </a>
            </section>

            <section id="mobile-alerts" class="mobile-section active" data-section="alerts">
                <div class="section-header">
                    <div>
                        <p class="section-eyebrow">System alerts</p>
                        <h2>Active alerts</h2>
                    </div>
                    <div class="section-actions">
                        <button id="mobile-alert-filter" class="icon-btn small" title="Filter alerts"><i class="fas fa-filter"></i></button>
                        <button id="mobile-alert-refresh" class="icon-btn small" title="Refresh alerts"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div id="mobile-alert-list" class="mobile-card-list">
                    <div class="empty-state">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading alerts...</p>
                    </div>
                </div>
            </section>

            <section id="mobile-lookup" class="mobile-section" data-section="lookup">
                <div class="section-header">
                    <div>
                        <p class="section-eyebrow">Device search</p>
                        <h2>Results</h2>
                    </div>
                    <div class="section-actions">
                        <button id="mobile-lookup-refresh" class="icon-btn small" title="Refresh search"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div id="mobile-lookup-results" class="mobile-card-list">
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <p>Start typing to search devices</p>
                    </div>
                </div>
            </section>

            <section id="mobile-more" class="mobile-section" data-section="more">
                <div class="section-header">
                    <div>
                        <p class="section-eyebrow">Other areas</p>
                        <h2>Tools & admin</h2>
                    </div>
                </div>
                <div class="link-list">
                    <a class="link-item" href="command-center.php">
                        <span><i class="fas fa-shield-alt"></i> Command Center</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a class="link-item" href="panel-message-monitor.php">
                        <span><i class="fas fa-satellite-dish"></i> Panel Monitor</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a class="link-item" href="payload-debugger.php">
                        <span><i class="fas fa-bug"></i> Payload Debugger</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a class="link-item" href="system-diagnostics.php">
                        <span><i class="fas fa-stethoscope"></i> System Diagnostics</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a class="link-item" href="db-inspector.php">
                        <span><i class="fas fa-database"></i> Database Inspector</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </section>
        </main>

        <nav class="mobile-nav">
            <button class="nav-btn active" data-target="alerts">
                <i class="fas fa-bell"></i>
                <span>Alerts</span>
            </button>
            <button class="nav-btn" data-target="lookup">
                <i class="fas fa-print"></i>
                <span>Lookup</span>
            </button>
            <a class="nav-btn" href="device-lifecycle.php">
                <i class="fas fa-server"></i>
                <span>Lifecycle</span>
            </a>
            <button class="nav-btn" data-target="more">
                <i class="fas fa-ellipsis-h"></i>
                <span>More</span>
            </button>
        </nav>
    </div>

    <div id="mobile-device-modal" class="mobile-modal" aria-hidden="true">
        <div class="mobile-modal-content">
            <header class="mobile-modal-header">
                <div>
                    <p class="section-eyebrow">Device</p>
                    <h3 id="mobile-modal-title">Device details</h3>
                </div>
                <button id="mobile-modal-close" class="icon-btn">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div id="mobile-modal-body" class="mobile-modal-body">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading device...</p>
                </div>
            </div>
            <footer class="mobile-modal-footer">
                <a id="mobile-modal-lifecycle" class="btn-primary" href="device-lifecycle.php">
                    <i class="fas fa-server"></i> Open Lifecycle
                </a>
                <button id="mobile-modal-refresh" class="btn-secondary">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </footer>
        </div>
    </div>

    <script>
        window.MPSM_MOBILE_CONFIG = {
            customerCode: "<?php echo $customerCode; ?>",
            customerName: "<?php echo $customerName; ?>",
            dealerCode: "<?php echo $dealerCode; ?>",
            dealerId: "<?php echo $dealerId; ?>",
            theme: "<?php echo $theme; ?>"
        };
    </script>
    <script src="assets/mobile.js"></script>
</body>
</html>
<?php
/*
CHANGELOG
2025-11-24 Codex
- Added dedicated mobile landing page with quick alerts, device lookup, lifecycle access, and links to admin/dev tools.
- Reused existing authentication, preferences, and device/alert APIs to keep logic DRY while enabling mobile-first navigation.
*/
?>
