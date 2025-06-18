<?php declare(strict_types=1);
// /cards/card_get_dashboard_device_actions_dashboard.php

require __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

$payload = [];

$data = call_api($config, 'POST', 'Dashboard/GetDeviceActionsDashboard', $payload);
?>
<div class="card">
  <h3>Dashboard Device Actions</h3>
  <pre><?php print_r($data); ?></pre>
</div>
