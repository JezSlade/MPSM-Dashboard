<?php declare(strict_types=1);
// /cards/card_get_device_extended_property.php

require __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

$payload = [
  'id'   => 'PUT_DEVICE_ID_HERE',
  'name' => 'PUT_PROPERTY_NAME'
];

$data = call_api($config, 'POST', 'Device/GetDeviceExtendedProperty', $payload);
?>
<div class="card">
  <h3>Extended Property</h3>
  <pre><?php print_r($data); ?></pre>
</div>
