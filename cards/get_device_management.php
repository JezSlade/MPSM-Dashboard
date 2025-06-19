<?php declare(strict_types=1);
// /api/get_device_management.php

$path           = 'Device/GetDeviceManagement';
$requiredFields = ['deviceId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
