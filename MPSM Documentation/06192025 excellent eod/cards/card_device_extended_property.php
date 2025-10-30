<?php declare(strict_types=1);
// /api/get_device_extended_property.php

$path           = 'Device/GetDeviceExtendedProperty';
$requiredFields = ['id', 'name'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
