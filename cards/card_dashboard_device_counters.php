<?php declare(strict_types=1);
// /cards/card_dashboard_device_counters.php

$path             = 'Dashboard/GetDeviceCounters';
$payload          = [];
$requiredFields   = [];
$cardTitle        = 'Dashboard Device Counters';
$columns          = ['ExternalIdentifier'=>'Equipment ID','Count'=>'Count'];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
