<?php declare(strict_types=1);
// /api/get_device_extended_property.php

$method         = 'POST';
$path           = 'Device/GetDeviceExtendedProperty';
$useCache       = true;
$requiredFields = ['id', 'name'];

require __DIR__ . '/../includes/api_bootstrap.php';
