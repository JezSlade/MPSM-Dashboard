<?php declare(strict_types=1);
// /api/get_device_by_serial_number.php

$method         = 'POST';
$path           = 'Device/GetDeviceBySerialNumber';
$useCache       = true;
$requiredFields = ['serialNumber'];

require __DIR__ . '/../includes/api_bootstrap.php';
