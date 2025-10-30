<?php declare(strict_types=1);
// /api/get_device_consumables.php

$path           = 'Device/GetDeviceConsumables';
$requiredFields = ['deviceId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
