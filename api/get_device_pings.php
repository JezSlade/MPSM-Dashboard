<?php declare(strict_types=1);
// /api/get_device_pings.php

$method         = 'POST';
$path           = 'Device/GetDevicePings';
$useCache       = true;
$requiredFields = ['deviceId'];

require __DIR__ . '/../includes/api_bootstrap.php';
