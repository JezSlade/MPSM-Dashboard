<?php declare(strict_types=1);
// /api/get_device_counters.php

$path           = 'Device/GetDeviceCounters';
$requiredFields = ['id'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
