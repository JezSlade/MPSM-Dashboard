<?php declare(strict_types=1);
// /api/get_device_details.php

$path           = 'Device/GetDeviceDetails';
$requiredFields = ['id'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
