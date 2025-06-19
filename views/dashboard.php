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
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/heroicons@2.0.13/dist/heroicons.min.js"></script>
</head>
<body class="flex flex-col h-full bg-gray-100 dark:bg-gray-900">

  <!-- —————————————————————————————————————————————————————— -->
  <!-- GLOBAL APP HEADER (CMYK-themed controls + status + theme) -->
  <!-- —————————————————————————————————————————————————————— -->
  <header class="app-header flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 shadow-md">
    <div class="flex items-center space-x-4">
      <!-- Status Light -->
      <span class="h-3 w-3 rounded-full bg-green-500"></span>
      <!-- Theme Toggle -->
      <button
        id="theme-toggle"
        class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400"
        title="Toggle Light/Dark"
      >
        <!-- Heroicon: sun/moon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m8-8h1M3 12H2m15.364 6.364l.707.707M6.343 6.343l-.707-.707"/>
        </svg>
      </button>
    </div>

    <div class="flex items-center space-x-4">
      <!-- Clear Cookies (Cyan Trash) -->
      <button
        onclick="clearSessionCookies()"
        class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-300"
        title="Clear Session Cookies"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-cyan-500" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0
                   01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1
                   0 00-1-1m-4 0h4"/>
        </svg>
      </button>

      <!-- Hard Refresh (Magenta Refresh) -->
      <button
        onclick="hardRefresh()"
        class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-300"
        title="Hard Refresh"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-magenta-500" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582M20 20v-5h-.581M5.64
                   19.364A9 9 0 1119.364 5.64"/>
        </svg>
      </button>

      <!-- Debug Log (Yellow Bug) -->
      <button
        onclick="openDebugLog()"
        class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-300"
        title="View Debug Log"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
          <path d="M11.049 2.927c.3-.921 1.603-.921
                   1.902 0l1.286 3.963a1 1 0 00.95.69h4.162c.969
                   0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0
                   00-.363 1.118l1.286 3.963c.3.921-.755
                   1.688-1.54 1.118l-3.37-2.448a1
                   1 0 00-1.176 0l-3.37 2.448c-.784.57-1.84-.197-1.54-1.118l1.286-3.963a1 1 0
                   00-.362-1.118L2.963 9.39c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0
                   00.95-.69l1.285-3.963z"/>
        </svg>
      </button>
    </div>
  </header>


  <!-- —————————————————————————————————————————————————————— -->
  <!-- DASHBOARD HEADER (Title + Preferences) -->
  <!-- —————————————————————————————————————————————————————— -->
  <header class="dashboard-header flex items-center justify-between px-6 py-3 bg-gray-800 bg-opacity-50 backdrop-blur-sm">
    <h1 class="text-xl font-medium">Dashboard for <?= htmlspecialchars($customerName) ?></h1>
    <button
      class="gear-icon p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-400"
      onclick="togglePreferencesModal(true)"
      title="View Preferences"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400" fill="none"
           viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6V4m0 16v-2m8-8h2M4 12H2m15.364
                 6.364l1.414-1.414M6.343 6.343l1.414-1.414m0
                 12.728l-1.414-1.414M17.657
                 6.343l1.414 1.414"/>
      </svg>
    </button>
  </header>

  <!-- Include preferences modal -->
  <?php include __DIR__ . '/../components/preferences-modal.php'; ?>

  <!-- —————————————————————————————————————————————————————— -->
  <!-- MAIN CONTENT: Cards Grid -->
  <!-- —————————————————————————————————————————————————————— -->
  <main class="flex-1 overflow-auto p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($visibleCards as $card): ?>
        <?php include $cardsDir . $card; ?>
      <?php endforeach; ?>
    </div>
  </main>

  <!-- —————————————————————————————————————————————————————— -->
  <!-- SCRIPTS -->
  <!-- —————————————————————————————————————————————————————— -->
  <script>
    function clearSessionCookies() {
      document.cookie.split(";").forEach(c=> {
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
    // Theme toggle stub (implement your dark/light switch)
    document.getElementById('theme-toggle')
            .addEventListener('click',()=>{/* toggle theme */});
  </script>

</body>
</html>
