<?php declare(strict_types=1);
// /api/get_device_management.php

$method         = 'POST';
$path           = 'Device/GetDeviceManagement';
$useCache       = true;
$requiredFields = ['deviceId'];

require __DIR__ . '/../includes/api_bootstrap.php';
