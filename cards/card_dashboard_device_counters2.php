<?php declare(strict_types=1);
// /cards/card_dashboard_device_counters2.php

$path             = 'Dashboard/GetDeviceCounters2';
$payload          = [];
$requiredFields   = [];
$cardTitle        = 'Dashboard Device Counters 2';
$columns          = ['ExternalIdentifier'=>'Equipment ID','Counter2'=>'Counter2'];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
