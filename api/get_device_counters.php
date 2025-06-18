<?php declare(strict_types=1);
// /api/get_device_counters.php

$method         = 'POST';
$path           = 'Device/GetDeviceCounters';
$useCache       = true;
$requiredFields = ['Id', 'CustomerCode'];

require __DIR__ . '/../includes/api_bootstrap.php';
