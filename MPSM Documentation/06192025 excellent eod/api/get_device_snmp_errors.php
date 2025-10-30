<?php declare(strict_types=1);
// /api/get_device_snmp_errors.php

$method         = 'POST';
$path           = 'Device/GetDeviceSnmpErrors';
$useCache       = true;
$requiredFields = ['deviceId'];

require __DIR__ . '/../includes/api_bootstrap.php';
