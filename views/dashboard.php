<?php declare(strict_types=1);
// /views/dashboard.php

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

require_once __DIR__ . '/../includes/api_functions.php';

// Load environment config
$config = parse_env_file(__DIR__ . '/../.env');

// === Customer selection logic ===
$customerCode = $_GET['customer'] 
    ?? $_COOKIE['customer'] 
    ?? $config['DEALER_CODE'] 
    ?? '';
if (isset($_GET['customer'])) {
    setcookie('customer', $customerCode, time()+31536000, '/');
}

// Fetch customer name for display
$customerName = 'All Customers';
try {
    $resp = call_api($config, 'POST', 'Customer/GetCustomers', [
        'DealerCode'  => $config['DEALER_CODE'] ?? '',
        'PageNumber'  => 1,
        'PageRows'    => 2147483647,
        'SortColumn'  => 'Description',
        'SortOrder'   => 'Asc',
    ]);
    foreach ($resp['customers'] ?? $resp['Result'] ?? [] as $c) {
        if (($c['Code'] ?? '') === $customerCode) {
            $customerName = $c['Description'] ?? $c['Name'] ?? $customerName;
            break;
        }
    }
} catch (\Throwable $e) {
    // Log or ignore
}

// === Card visibility logic ===
// Load all card IDs and titles
$cardsConfig = include __DIR__ . '/../config/cards.php';
$allIds      = array_keys($cardsConfig);

// Determine which cards to show based on cookie
if (isset($_COOKIE['visible_cards'])) {
    $sel = array_filter(array_map('trim', explode(',', $_COOKIE['visible_cards'])), 'strlen');
    $visibleIds = count($sel) > 0
        ? array_values(array_intersect($sel, $allIds))
        : $allIds;
} else {
    $visibleIds = $allIds;
}

?><!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
  <meta charset="UTF-8"/>
  <title>Dashboard – <?= htmlspecialchars($customerName) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="/public/css/styles.css"/>
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

  <!-- Preferences toggle button (in header) -->
  <!-- assumes header.php and navigation.php already rendered -->

  <main class="flex-1 overflow-auto p-6">
    <h1 class="text-2xl font-bold mb-4 tooltip" title="Current selected customer">
      <?= htmlspecialchars($customerName) ?>
    </h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($visibleIds as $id):
          $file = __DIR__ . "/../cards/card_{$id}.php";
          if (file_exists($file)) {
              include $file;
          } else {
              // compute title safely before echoing
              $title = $cardsConfig[$id] ?? $id;
              echo '<div class="panel text-red-500 tooltip" title="Card missing">' 
                   . htmlspecialchars($title) 
                   . '</div>';
          }
      endforeach; ?>
    </div>
  </main>

  <!-- Footer (loads preferences modal, etc.) -->
  <?php require __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
