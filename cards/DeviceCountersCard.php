<?php
// cards/DeviceCountersCard.php — Equipment counters scoped to selected customer
declare(strict_types=1);

require_once __DIR__ . '/../includes/card_base.php';   // loads .env, constants, and auth/client
require_once __DIR__ . '/../includes/api_client.php';  // defines api_request()

// 1) Pull in the globally selected customer
$selectedCustomer = $_COOKIE['customer'] ?? null;

// 2) Card wrapper settings (cache, indicator, TTL) come from card_base or cookies if needed

?>
<div
  id="DeviceCountersCard"
  class="glass-card p-4 rounded-lg bg-white/20 backdrop-blur-md border border-gray-600"
>
  <header class="mb-3 flex items-center justify-between">
    <h2 class="text-xl font-semibold">Device Counters</h2>
  </header>

  <?php
  // 3) Fetch device data from MPSM API
  try {
      $resp = api_request('Device/List', [
          'DealerCode'   => DEALER_CODE,
          'CustomerCode' => $selectedCustomer,
          'PageNumber'   => 1,
          'PageRows'     => 15,
          'SortColumn'   => 'ExternalIdentifier',
          'SortOrder'    => 'Asc',
      ]);
      $payload = $resp['data'] ?? $resp;
  } catch (RuntimeException $e) {
      echo '<p class="text-red-400">Failed to load devices: ' . htmlspecialchars($e->getMessage()) . '</p>';
      echo '</div>'; // close card
      return;
  }

  // 4) Normalize items
  $devices = $payload['items'] ?? $payload['Result'] ?? $payload;

  // 5) Render table
  ?>
  <div class="overflow-auto">
    <table class="min-w-full divide-y divide-gray-700 text-sm">
      <thead class="bg-gray-800 text-white">
        <tr>
          <th class="px-4 py-2 text-left">Equipment ID</th>
          <th class="px-4 py-2 text-left">IP Address</th>
          <th class="px-4 py-2 text-left">Model</th>
          <th class="px-4 py-2 text-left">Warnings</th>
        </tr>
      </thead>
      <tbody class="bg-gray-700 divide-y divide-gray-600">
        <?php foreach ($devices as $d): 
          $id       = htmlspecialchars($d['ExternalIdentifier'] ?? '', ENT_QUOTES);
          $ip       = htmlspecialchars($d['IpAddress']           ?? '', ENT_QUOTES);
          $model    = htmlspecialchars($d['ModelName']           ?? '', ENT_QUOTES);
          $warnings = htmlspecialchars(implode(', ', $d['Warnings'] ?? []), ENT_QUOTES);
        ?>
        <tr>
          <td class="px-4 py-2"><?= $id ?></td>
          <td class="px-4 py-2"><?= $ip ?></td>
          <td class="px-4 py-2"><?= $model ?></td>
          <td class="px-4 py-2"><?= $warnings ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
