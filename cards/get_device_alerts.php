<?php declare(strict_types=1);
// /api/get_device_alerts.php

$path           = 'Device/GetDeviceAlerts';
$requiredFields = ['deviceId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
