<?php declare(strict_types=1);
// /cards/card_get_device_pings.php

require __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

$payload = ['deviceId' => 'PUT_DEVICE_ID_HERE'];

$data = call_api($config, 'POST', 'Device/GetDevicePings', $payload);
?>
<div class="card">
  <h3>Device Pings</h3>
  <pre><?php print_r($data); ?></pre>
</div>
