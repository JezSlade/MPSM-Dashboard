<?php declare(strict_types=1);
// /views/dashboard.php

// 0) Enable full error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// 1) Shared helpers + config parser + API caller
require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 2) Determine which customer is selected (via ?customer=CODE), fallback to default
$customerCode = $_GET['customer'] 
              ?? $config['DEALER_CODE'] 
              ?? '';

// 3) Look up that customer’s human‐friendly name
$customerName = 'All Customers';
try {
    // Build a GetCustomersRequest payload that returns everyone
    $custPayload = [
        'DealerCode' => $config['DEALER_CODE'] ?? '',
        'PageNumber' => 1,
        'PageRows'   => 2147483647,
        'SortColumn' => 'Description',
        'SortOrder'  => 'Asc',
    ];
    $custResp = call_api($config, 'POST', 'Customer/GetCustomers', $custPayload);
    $list     = $custResp['Result'] ?? [];
    foreach ($list as $c) {
        // The API returns 'Code' and 'Description'
        if (!empty($customerCode) && ($c['Code'] ?? '') === $customerCode) {
            $customerName = $c['Description'] ?? $c['Name'] ?? $customerCode;
            break;
        }
    }
} catch (\Throwable $e) {
    // On error, just show code or default
    if ($customerCode) {
        $customerName = $customerCode;
    }
}

// 4) Scan all card files in /cards/
$cardsDir  = __DIR__ . '/../cards/';
$allCards  = array_filter(
    scandir($cardsDir),
    fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'php'
);

// 5) Determine which cards to display (cookie-stored or all)
if (!empty($_COOKIE['visible_cards'])) {
    $visibleCards = array_intersect(
        explode(',', $_COOKIE['visible_cards']),
        $allCards
    );
} else {
    $visibleCards = $allCards;
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

  <!-- Header with dynamic customer name -->
  <header class="dashboard-header">
    <h1>Dashboard for <?= htmlspecialchars($customerName) ?></h1>
    <button class="gear-icon" title="View Preferences">⚙️</button>
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

</body>
</html>
