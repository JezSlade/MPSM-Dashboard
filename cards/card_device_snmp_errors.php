<?php declare(strict_types=1);
// /api/get_device_snmp_errors.php

$path           = 'Device/GetDeviceSnmpErrors';
$requiredFields = ['deviceId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
