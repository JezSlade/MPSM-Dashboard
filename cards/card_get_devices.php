<?php declare(strict_types=1);
// /cards/card_get_devices.php

require __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

$payload = [
  'CustomerCode' => $config['DEALER_CODE'],
];

$data = call_api($config, 'POST', 'Device/GetDevices', $payload);
?>
<div class="card">
  <h3>Devices List</h3>
  <ul>
    <?php foreach ($data['Result'] ?? [] as $dev): ?>
      <li><?= htmlspecialchars($dev['ExternalIdentifier']) ?> (<?= htmlspecialchars($dev['Model']) ?>)</li>
    <?php endforeach; ?>
  </ul>
</div>
