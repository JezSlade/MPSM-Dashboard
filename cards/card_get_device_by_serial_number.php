<?php declare(strict_types=1);
// /cards/card_get_device_by_serial_number.php

require __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

$payload = ['serialNumber' => 'PUT_SERIAL_NUMBER_HERE'];

$data = call_api($config, 'POST', 'Device/GetDeviceBySerialNumber', $payload);
?>
<div class="card">
  <h3>Find Device by SN</h3>
  <pre><?php print_r($data); ?></pre>
</div>
