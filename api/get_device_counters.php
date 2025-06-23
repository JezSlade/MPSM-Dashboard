<?php declare(strict_types=1);
// /api/get_device_counters.php

$method         = 'POST';
$path           = 'Device/GetDeviceCounters';
$useCache       = true;
$requiredFields = ['deviceId'];

require __DIR__ . '/../includes/api_bootstrap.php';
