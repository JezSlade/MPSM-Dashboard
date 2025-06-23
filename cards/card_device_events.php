<?php declare(strict_types=1);
// /api/get_device_events.php

$path           = 'Device/GetDeviceEvents';
$requiredFields = ['deviceId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
