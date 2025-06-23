<?php declare(strict_types=1);
// /api/get_device_alerts.php

$method         = 'POST';
$path           = 'Device/GetDeviceAlerts';
$useCache       = true;
$requiredFields = ['deviceId'];

require __DIR__ . '/../includes/api_bootstrap.php';
