<?php declare(strict_types=1);
// /cards/card_device_alerts.php

$path             = 'Device/GetDeviceAlerts';
$payload          = ['deviceId'=>''];
$requiredFields   = ['deviceId'];
$cardTitle        = 'Device Alerts';
$columns          = ['AlertType'=>'Type','AlertCount'=>'Count'];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
