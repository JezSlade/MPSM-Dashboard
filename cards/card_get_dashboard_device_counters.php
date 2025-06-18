<?php declare(strict_types=1);
// /cards/card_get_dashboard_device_counters.php

require __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

$payload = [];

$data = call_api($config, 'POST', 'Dashboard/GetDeviceCounters', $payload);
?>
<div class="card">
  <h3>Dashboard Device Counters</h3>
  <pre><?php print_r($data); ?></pre>
</div>
