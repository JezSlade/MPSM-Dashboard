<?php declare(strict_types=1);
// /cards/card_get_customers.php

require __DIR__ . '/../includes/api_functions.php';
$config  = parse_env_file(__DIR__ . '/../.env');

// TODO: set any POST body fields here
$payload = [];

$data = call_api($config, 'POST', 'Customer/GetCustomers', $payload);
?>
<div class="card">
  <h3>Customers</h3>
  <ul>
    <?php foreach ($data['Result'] ?? [] as $cust): ?>
      <li><?= htmlspecialchars($cust['Name'] ?? $cust['CustomerCode']) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
