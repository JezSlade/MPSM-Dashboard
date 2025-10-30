<?php declare(strict_types=1);
// /api/get_device_readings.php

$method         = 'POST';
$path           = 'Device/GetDeviceReadings';
$useCache       = true;
$requiredFields = ['deviceId'];

require __DIR__ . '/../includes/api_bootstrap.php';
