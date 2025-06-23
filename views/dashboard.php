<?php declare(strict_types=1);
// /views/dashboard.php

// ─── Bootstrap & debug ────────────────────────────────────────
require_once __DIR__ . '/../includes/debug.php';
require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// ─── 1) SESSION + Determine customer code ─────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_GET['customer']) && $_GET['customer'] !== '') {
    $_SESSION['selectedCustomer'] = trim($_GET['customer']);
}
$customerCode = $_SESSION['selectedCustomer']
    ?? $config['DEALER_CODE']
    ?? '';

// ─── 2) Look up human‐readable customer name ─────────────────
$customerName = 'All Customers';
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
            $customerName = $c['Description'] 
                          ?? $c['Name'] 
                          ?? $customerName;
            break;
        }
    }
} catch (\Throwable $e) {
    // ignore failures
}

// ─── 3) Define exactly which cards to show ──────────────────
// only our new customer-devices card
$visibleCards = ['card_customer_devices.php'];
$cardsDir     = __DIR__ . '/../cards/';
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard – <?= htmlspecialchars($customerName) ?></title>
  <link rel="stylesheet" href="/public/css/styles.css" />
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

  <!-- Preferences Modal -->
  <?php include __DIR__ . '/../components/preferences-modal.php'; ?>

  <header class="dashboard-header flex items-center justify-between px-6 py-3 bg-gray-800 bg-opacity-50 backdrop-blur-sm">
    <h1 class="text-xl font-semibold"><?= htmlspecialchars($customerName) ?></h1>
    <button id="preferences-toggle"
            onclick="togglePreferencesModal(true)"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-400"
            title="Preferences">
      <i data-feather="settings" class="h-8 w-8 text-purple-400"></i>
    </button>
  </header>

  <main class="flex-1 overflow-auto p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($visibleCards as $card): ?>
        <?php include $cardsDir . $card; ?>
      <?php endforeach; ?>
    </div>
  </main>

</body>
</html>
