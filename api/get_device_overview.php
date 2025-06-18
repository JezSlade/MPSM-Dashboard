<?php declare(strict_types=1);
// /api/get_device_overview.php

$method         = 'POST';
$path           = 'Device/GetDeviceOverview';
$useCache       = true;
$requiredFields = ['id'];

require __DIR__ . '/../includes/api_bootstrap.php';
