<?php declare(strict_types=1);
// /api/get_device_consumables.php

$method         = 'POST';
$path           = 'Device/GetDeviceConsumables';
$useCache       = true;
$requiredFields = ['deviceId'];

require __DIR__ . '/../includes/api_bootstrap.php';
