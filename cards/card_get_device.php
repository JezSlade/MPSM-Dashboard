<?php declare(strict_types=1);
// /cards/card_get_device.php

require __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

$payload = ['id' => 'PUT_DEVICE_ID_HERE'];

$data = call_api($config, 'POST', 'Device/GetDevice', $payload);
?>
<div class="card">
  <h3>Device Info</h3>
  <pre><?php print_r($data); ?></pre>
</div>
