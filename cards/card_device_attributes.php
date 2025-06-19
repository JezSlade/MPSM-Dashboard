<?php declare(strict_types=1);
// /api/get_device_attributes.php

$path           = 'Device/GetDeviceAttributes';
$requiredFields = ['deviceId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
