<?php declare(strict_types=1);
// /api/get_device_readings.php

$path           = 'Device/GetDeviceReadings';
$requiredFields = ['deviceId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
