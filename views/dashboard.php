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

// 1) Determine selected customer code
$customerCode = $_GET['customer'] 
              ?? $_COOKIE['customer'] 
              ?? $config['DEALER_CODE'] 
              ?? '';

// 2) Resolve human-friendly customer name
$customerName = $customerCode ?: 'All Customers';
try {
    $custResp = call_api($config, 'POST', 'Customer/GetCustomers', [
        'DealerCode' => $config['DEALER_CODE'] ?? '',
        'PageNumber' => 1,
        'PageRows'   => 2147483647,
        'SortColumn' => 'Description',
        'SortOrder'  => 'Asc',
    ]);
    foreach ($custResp['Result'] ?? [] as $c) {
        if (($c['Code'] ?? '') === $customerCode) {
            $customerName = $c['Description'] ?? $c['Name'] ?? $customerCode;
            break;
        }
    }
} catch (\Throwable $e) {
    // fallback to code
}

// 3) Scan cards directory
$cardsDir  = __DIR__ . '/../cards/';
$cardFiles = array_filter(
    scandir($cardsDir),
    fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'php'
);

// 4) Determine visible cards (cookie or all)
if (!empty($_COOKIE['visible_cards'])) {
    $visibleCards = array_intersect(
        explode(',', $_COOKIE['visible_cards']),
        $cardFiles
    );
} else {
    $visibleCards = $cardFiles;
}
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
      title="Clear session cookies"
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
    function clearSessionCookies() {
      document.cookie.split(";").forEach(function(c) {
        document.cookie = c.trim().replace(/=.*/, "=;expires=Thu,01 Jan 1970 00:00:00 UTC;path=/");
      });
      alert("Session cookies cleared.");
    }

    function hardRefresh() {
      window.location.reload(true);
    }

    function openDebugLog() {
      window.open(
        '/logs/debug.log',
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
