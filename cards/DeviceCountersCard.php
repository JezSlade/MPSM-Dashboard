<?php
// cards/DeviceCountersCard.php
declare(strict_types=1);

// 1) Bootstrap card UI, env, auth & API client
require_once __DIR__ . '/../includes/card_base.php';
require_once __DIR__ . '/../includes/api_client.php';

// 2) Open card wrapper & header
card_base_start('DeviceCountersCard', 'Device Counters');

// 3) Read selected customer
$customer = $_COOKIE['customer'] ?? null;
if (!$customer) {
    echo '<p class="text-gray-400">Please select a customer.</p>';
    card_base_end('DeviceCountersCard');
    return;
}

// 4) Fetch device list
try {
    $resp = api_request('Device/List', [
        'DealerCode'   => DEALER_CODE,
        'CustomerCode' => $customer,
        'PageNumber'   => 1,
        'PageRows'     => 15,
        'SortColumn'   => 'ExternalIdentifier',
        'SortOrder'    => 'Asc',
    ]);
    $data = $resp['data'] ?? $resp;
} catch (RuntimeException $e) {
    echo '<p class="text-red-400">Error loading devices.</p>';
    card_base_end('DeviceCountersCard');
    return;
}

// 5) Normalize payload
$devices = $data['items'] ?? $data['Result'] ?? $data;

// 6) Render table
echo '<div class="overflow-auto">';
echo '<table class="min-w-full divide-y divide-gray-700 text-sm">';
echo '<thead class="bg-gray-800 text-white"><tr>'
   . '<th class="px-4 py-2 text-left">Equipment ID</th>'
   . '<th class="px-4 py-2 text-left">IP Address</th>'
   . '<th class="px-4 py-2 text-left">Model</th>'
   . '<th class="px-4 py-2 text-left">Warnings</th>'
   . '</tr></thead>';
echo '<tbody class="bg-gray-700 divide-y divide-gray-600">';
foreach ($devices as $d) {
    $id     = htmlspecialchars($d['ExternalIdentifier'] ?? '', ENT_QUOTES);
    $ip     = htmlspecialchars($d['IpAddress']          ?? '', ENT_QUOTES);
    $model  = htmlspecialchars($d['ModelName']          ?? '', ENT_QUOTES);
    $warns  = htmlspecialchars(implode(', ', $d['Warnings'] ?? []), ENT_QUOTES);
    echo '<tr>';
    echo "<td class=\"px-4 py-2\">{$id}</td>";
    echo "<td class=\"px-4 py-2\">{$ip}</td>";
    echo "<td class=\"px-4 py-2\">{$model}</td>";
    echo "<td class=\"px-4 py-2\">{$warns}</td>";
    echo '</tr>';
}
echo '</tbody></table></div>';

// 7) Close card wrapper & footer
card_base_end('DeviceCountersCard');
