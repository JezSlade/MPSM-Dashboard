<?php declare(strict_types=1);
// /views/dashboard.php

// --- DEBUG BLOCK (Always at Top) ---
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// … existing customer lookup and card scanning …

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard – <?= htmlspecialchars($customerName) ?></title>
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>

  <header class="dashboard-header">
    <h1>Dashboard for <?= htmlspecialchars($customerName) ?></h1>

    <!-- Clear all session cookies -->
    <button
      class="btn-icon"
      onclick="clearSessionCookies()"
      title="Clear all session cookies"
    >🧹</button>

    <!-- Hard refresh the page -->
    <button
      class="btn-icon"
      onclick="hardRefresh()"
      title="Hard Refresh"
    >🔄</button>

    <!-- Show debug log in popup -->
    <button
      class="btn-icon"
      onclick="openDebugLog()"
      title="View Debug Log"
    >🐞</button>

    <!-- View preferences modal -->
    <button
      class="gear-icon"
      onclick="togglePreferencesModal(true)"
      title="View Preferences"
    >⚙️</button>
  </header>

  <!-- Preferences Modal Component -->
  <?php include __DIR__ . '/../components/preferences-modal.php'; ?>

  <main class="glass-main">
    <div class="card-grid">
      <?php foreach ($visibleCards as $card): ?>
        <?php include $cardsDir . $card; ?>
      <?php endforeach; ?>
    </div>
  </main>

  <script>
    // remove all cookies for the current path
    function clearSessionCookies() {
      document.cookie.split(";").forEach(function(c) {
        document.cookie = c.trim().replace(/=.*/, "=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/");
      });
      alert("All session cookies cleared.");
    }

    // force reload from server (bypass cache)
    function hardRefresh() {
      window.location.reload(true);
    }

    // open debug log in a new popup window
    function openDebugLog() {
      const url = '/logs/debug.log';
      window.open(
        url,
        'DebugLogWindow',
        'width=800,height=600,menubar=no,toolbar=no,location=no,status=no'
      );
    }
  </script>

  <style>
    .btn-icon {
      background: none;
      border: none;
      color: inherit;
      font-size: 1.2rem;
      cursor: pointer;
      margin: 0 0.5rem;
    }
  </style>
</body>
</html>
