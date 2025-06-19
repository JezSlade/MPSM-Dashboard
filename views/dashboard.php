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

// 4) Determine visible cards (cookie-set but allow empty = no cards)
$cardsDir  = __DIR__ . '/../cards/';
$cardFiles = array_filter(
  scandir($cardsDir),
  fn($f)=> pathinfo($f, PATHINFO_EXTENSION)==='php'
);

if (isset($_COOKIE['visible_cards'])) {
    // explode and drop any empty strings
    $sel = array_filter(explode(',', $_COOKIE['visible_cards']), 'strlen');
    // intersect with existing files
    $visibleCards = array_values(array_intersect($sel, $cardFiles));
} else {
    // no cookie = show all
    $visibleCards = $cardFiles;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard – <?= htmlspecialchars($customerName) ?></title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Heroicons (outline) -->
  <script src="https://unpkg.com/heroicons@2.0.13/dist/heroicons.min.js"></script>
</head>
<body class="bg-gray-900 text-gray-100">

  <!-- PAGE HEADER -->
  <header class="flex items-center justify-between p-4 bg-gray-800 bg-opacity-50 backdrop-blur-md shadow-lg">
    <div class="flex items-center space-x-4">
      <h1 class="text-2xl font-semibold">MPS Monitor Dashboard</h1>
      <!-- Status light -->
      <span class="h-3 w-3 rounded-full bg-green-400"></span>
    </div>

    <div class="flex items-center space-x-3">
      <!-- Clear Cookies -->
      <button
        onclick="clearSessionCookies()"
        class="p-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500"
        title="Clear Session Cookies"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </button>

      <!-- Hard Refresh -->
      <button
        onclick="hardRefresh()"
        class="p-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        title="Hard Refresh"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582M20 20v-5h-.581M5.64 19.364A9 9 0 1119.364 5.64" />
        </svg>
      </button>

      <!-- View Debug Log -->
      <button
        onclick="openDebugLog()"
        class="p-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-500"
        title="View Debug Log"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" viewBox="0 0 24 24" fill="currentColor">
          <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.963a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.363 1.118l1.286 3.963c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.84-.197-1.54-1.118l1.286-3.963a1 1 0 00-.362-1.118L2.963 9.39c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.285-3.963z"/>
        </svg>
      </button>

      <!-- Preferences Gear -->
      <button
        class="p-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 gear-icon"
        onclick="togglePreferencesModal(true)"
        title="View Preferences"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.963a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.363 1.118l1.286 3.963c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.84-.197-1.54-1.118l1.286-3.963a1 1 0 00-.362-1.118L2.963 9.39c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.285-3.963z"/>
        </svg>
      </button>

      <!-- Theme Toggle -->
      <button class="p-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <!-- assume you already have your theme toggle icon here -->
        <svg class="h-6 w-6 text-indigo-300" /*...*/></svg>
      </button>
    </div>
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
        '/components/debug-log.php',
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
