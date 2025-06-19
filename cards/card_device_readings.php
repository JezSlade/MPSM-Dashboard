<?php declare(strict_types=1);
// /cards/card_device_readings.php

$path             = 'Device/GetDeviceReadings';
$payload          = ['deviceId'=>''];
$requiredFields   = ['deviceId'];
$cardTitle        = 'Device Readings';
$columns          = ['ReadingTime'=>'Time','ReadingValue'=>'Value'];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
