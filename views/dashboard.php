<?php declare(strict_types=1);
// /views/dashboard.php

// ─── Bootstrap & Debug & API Client ─────────────────────────
require_once __DIR__ . '/../includes/debug.php';
require_once __DIR__ . '/../includes/api_client.php';
$env = load_env(__DIR__ . '/../.env');

// ─── 1) SESSION + capture ?customer=… ───────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_GET['customer'])) {
    $_SESSION['selectedCustomer'] = trim($_GET['customer']);
}
$customerCode = $_SESSION['selectedCustomer']
              ?? ($env['DEALER_CODE'] ?? '')
              ?? '';
              
// ─── 2) Resolve human‐readable customer name ────────────────
$customerName = 'All Customers';
if ($customerCode !== '') {
    $resp = api_call(
        $env,
        'POST',
        '/Customer/GetCustomers',
        [
          'DealerCode' => $env['DEALER_CODE'] ?? '',
          'PageNumber' => 1,
          'PageRows'   => PHP_INT_MAX,
          'SortColumn' => 'Description',
          'SortOrder'  => 'Asc',
        ]
    );
    if (!empty($resp['IsValid']) && !empty($resp['Result'])) {
        foreach ($resp['Result'] as $c) {
            if (($c['Code'] ?? '') === $customerCode) {
                $customerName = $c['Description'] 
                              ?? $c['Name'] 
                              ?? $customerName;
                break;
            }
        }
    }
}
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
      <?php
        // 3) Include every card_*.php under /cards/
        $cardsDir = __DIR__ . '/../cards/';
        foreach (glob($cardsDir . 'card_*.php') ?: [] as $cardPath) {
            include $cardPath;
        }
      ?>
    </div>
  </main>

</body>
</html>
