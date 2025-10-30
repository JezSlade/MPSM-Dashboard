<?php declare(strict_types=1);
// /api/get_device_events.php

$method         = 'POST';
$path           = 'Device/GetDeviceEvents';
$useCache       = true;
$requiredFields = ['deviceId'];

require __DIR__ . '/../includes/api_bootstrap.php';
