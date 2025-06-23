<?php declare(strict_types=1);
// /api/get_device_pings.php

$path           = 'Device/GetDevicePings';
$requiredFields = ['deviceId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
