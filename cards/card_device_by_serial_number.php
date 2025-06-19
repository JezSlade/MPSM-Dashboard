<?php declare(strict_types=1);
// /cards/card_device_by_serial_number.php

$path             = 'Device/GetDeviceBySerialNumber';
$payload          = ['serialNumber'=>''];
$requiredFields   = ['serialNumber'];
$cardTitle        = 'Device By Serial Number';
$columns          = ['ExternalIdentifier'=>'Equipment ID','SerialNumber'=>'SN'];
$enableSearch     = false;
$enablePagination = false;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
