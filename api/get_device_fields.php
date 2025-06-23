<?php declare(strict_types=1);
// /api/get_device_fields.php

$method         = 'POST';
$path           = 'Device/GetDeviceFields';
$useCache       = true;
$requiredFields = ['deviceId'];

require __DIR__ . '/../includes/api_bootstrap.php';
