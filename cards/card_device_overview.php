<?php declare(strict_types=1);
// cards/card_device_overview.php

// 1) Fetch our config & functions
require_once __DIR__ . '/../includes/api_functions.php';
$config    = parse_env_file(__DIR__ . '/../.env');
$customerCode = $_COOKIE['customer'] ?? ($config['DEALER_CODE'] ?? '');

// 2) Fetch devices for this customer
try {
  $devicesRes = call_api(
    $config,
    'GET',
    '/Device/Get',
    ['DealerCode' => $customerCode, 'PageNumber' => 1, 'PageRows' => 1] // just need total count
  );
  $totalDevices = $devicesRes['TotalRows'] ?? count($devicesRes['Result'] ?? []);
} catch (Exception $e) {
  $totalDevices = null;
  $errorMsg     = $e->getMessage();
}

// 3) Fetch customer list to resolve the name
try {
  $custRes = call_api($config, 'GET', '/Customers/List', ['DealerCode'=>$config['DEALER_CODE']]);
  // Find selected
  $custList = $custRes['Result'] ?? [];
  $selected = array_filter($custList, fn($c)=>($c['Code'] ?? '')===$customerCode);
  $custName = $selected ? $selected[0]['Description'] : '—';
} catch (Exception $e) {
  $custName = 'Unknown';
}

// 4) Render
?>
<div class="panel neon-glass-panel">
  <h3 class="text-lg mb-2">Device Overview</h3>
  <?php if (isset($errorMsg)): ?>
    <p class="text-red-400">Error: <?= htmlspecialchars($errorMsg) ?></p>
  <?php else: ?>
    <p><strong>Customer:</strong> <?= htmlspecialchars($custName) ?></p>
    <p><strong>Total Devices:</strong> <?= intval($totalDevices) ?></p>
  <?php endif; ?>
</div>
