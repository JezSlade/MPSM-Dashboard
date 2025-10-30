<?php declare(strict_types=1);
// /api/get_device_fields.php

$path           = 'Device/GetDeviceFields';
$requiredFields = ['deviceId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
