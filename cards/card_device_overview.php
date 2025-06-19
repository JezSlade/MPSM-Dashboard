<?php declare(strict_types=1);
// /cards/card_device_overview.php

$path             = 'Device/GetDeviceOverview';
$payload          = ['id'=>''];
$requiredFields   = ['id'];
$cardTitle        = 'Device Overview';
$columns          = ['OverviewKey'=>'Key','OverviewValue'=>'Value'];
$enableSearch     = false;
$enablePagination = false;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
