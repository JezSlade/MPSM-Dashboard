<?php declare(strict_types=1);
// /cards/card_device_pings.php

$path             = 'Device/GetDevicePings';
$payload          = ['deviceId'=>''];
$requiredFields   = ['deviceId'];
$cardTitle        = 'Device Pings';
$columns          = ['PingTime'=>'Time','Status'=>'Status'];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
