<?php
require 'config.php';
require 'functions.php';

requireAuth();
trackVisit('/command-center');
$userId = $_SESSION['user_id'] ?? null;
$preferences = getUserPreferences($userId);
$theme = htmlspecialchars($preferences['theme'] ?? 'light', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Center - MPSM Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body data-theme="<?= $theme ?>" data-alert-center-standalone="1">
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-shield-alt"></i> Command Center</h1>
            <div class="header-actions">
                <a class="btn-icon" href="index.php" title="Back to Dashboard">
                    <i class="fas fa-home"></i>
                </a>
                <a class="btn-icon" href="alert-definitions.php" title="Manage Alert Labels">
                    <i class="fas fa-tags"></i>
                </a>
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

    <main>
        <?php require __DIR__ . '/partials/alert-center.php'; ?>
    </main>
    <div id="toast-container"></div>

    <script src="assets/shared.js"></script>
    <script src="assets/app.js"></script>
    <script>
        window.ALERT_CENTER_BOOTSTRAP = {
            autoMount: true,
            rootSelector: '#alert-center-root',
            embedded: false
        };
    </script>
    <script src="assets/command-center.js"></script>
</body>
</html>
<!--
CHANGELOG
2025-11-26 Codex
- Added searchable datalist suggestions for Alert/Device/Customer patterns in rule modal.
- No layout change to markup structure; relies on new modal CSS for proper sizing and scrolling.
2025-11-28 Codex
- Added Alert Labels CRUD: Create Label button, definition modal, Edit/Delete action buttons
- Removed "Manage in full" link (functionality now integrated into Command Center)
- Fixed Tools tab: Removed iframes, replaced with tool cards linking to standalone tools
- Tool cards now open device-lifecycle.php and payload-debugger.php in new tabs
-->
