<?php declare(strict_types=1);
// /cards/card_device_counters.php

require_once __DIR__ . '/../includes/api_bootstrap.php';   // standard API call wrapper

// Card metadata
$path       = 'Device/GetCounters';
$cardTitle  = 'Device Counters';
$columns    = [
    'ExternalIdentifier' => 'Equipment ID',
    'Model'              => 'Model',
    'MonoTotal'          => 'Mono',
    'ColorTotal'         => 'Color'
];

// Fetch & render (simple example)
try {
    $data = call_api($config, 'POST', $path, ['CustomerCode' => $customerCode]);
} catch (\Throwable $e) {
    echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    return;
}

echo '<section class="card">';
echo '<h3>' . $cardTitle . '</h3>';
echo '<table><thead><tr>';
foreach ($columns as $th) { echo '<th>' . $th . '</th>'; }
echo '</tr></thead><tbody>';

foreach ($data['Result'] ?? [] as $row) {
    echo '<tr>';
    foreach ($columns as $key => $_) {
        echo '<td>' . htmlspecialchars((string)($row[$key] ?? '')) . '</td>';
    }
    echo '</tr>';
}
echo '</tbody></table>';
echo '</section>';
