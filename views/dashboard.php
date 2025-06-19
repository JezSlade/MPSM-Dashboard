<?php declare(strict_types=1);
// /views/dashboard.php

// ——————————————————————————————————————————————————————————————
// DEBUG BLOCK (Always at Top)
// ——————————————————————————————————————————————————————————————
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// 0) Shared helpers + config
require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// ——————————————————————————————————————————————————————————————
// 1) Resolve Customer Context
// ——————————————————————————————————————————————————————————————
$customerCode = $_GET['customer']
    ?? $_COOKIE['customer']
    ?? $config['DEALER_CODE']
    ?? '';
if (isset($_GET['customer'])) {
    setcookie('customer', $customerCode, time()+31536000, '/');
}

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
    // fallback silently
}

// ——————————————————————————————————————————————————————————————
// 2) Determine which cards to show
// ——————————————————————————————————————————————————————————————
$cardsDir  = __DIR__ . '/../cards/';
$allFiles  = array_filter(scandir($cardsDir), fn($f)=> pathinfo($f,PATHINFO_EXTENSION)==='php');

// parse cookie (allow empty = no cards)
if (isset($_COOKIE['visible_cards'])) {
    $sel = array_filter(explode(',', $_COOKIE['visible_cards']), 'strlen');
    $visibleCards = array_values(array_intersect($sel, $allFiles));
} else {
    $visibleCards = $allFiles;
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <title>Dashboard – <?= htmlspecialchars($customerName) ?></title>
  <!-- If not already loaded globally -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col h-full bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">

  <!-- DASHBOARD HEADER (Title + Preferences) -->
  <header class="dashboard-header flex items-center justify-between px-6 py-3 bg-gray-800 bg-opacity-50 backdrop-blur-sm">
    <h1 class="text-xl font-medium">Dashboard for <?= htmlspecialchars($customerName) ?></h1>
    <button
      class="gear-icon p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-400"
      onclick="togglePreferencesModal(true)"
      title="View Preferences"
    >
      <!-- Gear Icon -->
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400" fill="none"
           viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6V4m0 16v-2m8-8h2M4 12H2m15.364
                 6.364l1.414-1.414M6.343 6.343l1.414-1.414
                 m0 12.728l-1.414-1.414M17.657
                 6.343l1.414 1.414"/>
      </svg>
    </button>
  </header>

  <!-- Include preferences modal -->
  <?php include __DIR__ . '/../components/preferences-modal.php'; ?>

  <!-- MAIN CONTENT: Cards Grid -->
  <main class="flex-1 overflow-auto p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($visibleCards as $card): ?>
        <?php include $cardsDir . $card; ?>
      <?php endforeach; ?>
    </div>
  </main>

  <!-- SCRIPTS -->
  <script>
    // Keep debug-log, clearCookies, refresh functions
    function clearSessionCookies() {
      document.cookie.split(";").forEach(c => {
        document.cookie = c.trim().replace(/=.*/, "=;expires=Thu,01 Jan 1970 00:00:00 UTC;path=/");
      });
      alert("Session cookies cleared.");
    }
    function hardRefresh() {
      window.location.reload(true);
    }
    function openDebugLog() {
      window.open('/components/debug-log.php','DebugLog','width=800,height=600');
    }
    function togglePreferencesModal(show) {
      document.getElementById('preferences-modal')
              .classList.toggle('hidden', !show);
    }
  </script>

</body>
</html>
