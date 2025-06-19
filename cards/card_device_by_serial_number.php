<?php declare(strict_types=1);
// /api/get_device_by_serial_number.php

$path           = 'Device/GetDeviceBySerialNumber';
$requiredFields = ['serialNumber'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
