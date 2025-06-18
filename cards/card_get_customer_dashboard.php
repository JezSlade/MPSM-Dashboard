<?php declare(strict_types=1);
// /cards/card_get_customer_dashboard.php

require __DIR__ . '/../includes/api_functions.php';
$config  = parse_env_file(__DIR__ . '/../.env');

$payload = [
  'customerId' => $config['DEALER_ID'],
];

$data = call_api($config, 'POST', 'CustomerDashboard/Get', $payload);
?>
<div class="card">
  <h3>Customer Dashboard</h3>
  <pre><?php print_r($data); ?></pre>
</div>
