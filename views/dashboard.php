<?php declare(strict_types=1);
// /views/dashboard.php

// — DEBUG BLOCK —
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// Helpers + config
require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// Customer context
$customerCode = $_GET['customer'] ?? $_COOKIE['customer'] ?? $config['DEALER_CODE'] ?? '';
if (isset($_GET['customer'])) {
    setcookie('customer', $customerCode, time() + 31536000, '/');
}
$customerName = $customerCode ?: 'All Customers';
try {
    $resp = call_api($config, 'POST', 'Customer/GetCustomers', [
        'DealerCode' => $config['DEALER_CODE'] ?? '',
        'PageNumber' => 1,
        'PageRows'   => 2147483647,
        'SortColumn' => 'Description',
        'SortOrder'  => 'Asc',
    ]);
    foreach ($resp['Result'] ?? [] as $c) {
        if (($c['Code'] ?? '') === $customerCode) {
            $customerName = $c['Description'] ?? $c['Name'] ?? $customerCode;
            break;
        }
    }
} catch (\Throwable $e) {
    // ignore
}

// Cards visibility
$cardsDir = __DIR__ . '/../cards/';
$allFiles = scandir($cardsDir);
$allCards = array_filter($allFiles, fn($f) =>
    pathinfo($f, PATHINFO_EXTENSION) === 'php'
    && str_starts_with($f, 'card_')
);

if (isset($_COOKIE['visible_cards'])) {
    $sel = array_filter(explode(',', $_COOKIE['visible_cards']), 'strlen');
    $visibleCards = array_values(array_intersect($sel, $allCards));
} else {
    $visibleCards = $allCards;
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – <?= htmlspecialchars($customerName) ?></title>
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

    <!-- Preferences Modal -->
    <?php include __DIR__ . '/../components/preferences-modal.php'; ?>

    <header class="dashboard-header flex items-center justify-between px-6 py-3 bg-gray-800 bg-opacity-50 backdrop-blur-sm">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($customerName) ?></h1>
        <!-- Preferences icon -->
        <button id="preferences-toggle"
                class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-400"
                title="Preferences"
                onclick="togglePreferencesModal(true)">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11.049 2.927c.3-.92 1.603-.92 1.902 0a1.03 1.03 0 00.95.69h2.091a1.03 1.03 0 00.64-1.788l-1.578-1.578a1.03 1.03 0 000-1.414l1.578-1.578a1.03 1.03 0 01.64 1.788h-2.091a1.03 1.03 0 00-.95-.69c-.3-.92-1.603-.92-1.902 0a1.03 1.03 0 00-.95.69H8.96a1.03 1.03 0 01-.64 1.788l1.578 1.578a1.03 1.03 0 000 1.414L8.32 4.405a1.03 1.03 0 01.64-1.788h1.438c.44 0 .84-.308.951-.69zM12 8a4 4 0 100 8 4 4 0 000-8z"/>
          </svg>
        </button>
    </header>

    <main class="flex-1 overflow-auto p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($visibleCards as $card): ?>
          <?php include $cardsDir . $card; ?>
        <?php endforeach; ?>
      </div>
    </main>

    <script>
      function togglePreferencesModal(show) {
        document.getElementById('preferences-modal')
                .classList.toggle('hidden', !show);
      }
    </script>
</body>
</html>
