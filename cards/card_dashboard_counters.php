<?php declare(strict_types=1);
// /cards/card_dashboard_counters.php

$path             = 'Dashboard/GetCounters';
$payload          = [];
$requiredFields   = [];
$cardTitle        = 'Dashboard Counters';
$columns          = ['DeviceCount'=>'Device Count','SupplyCount'=>'Supply Count'];
$enableSearch     = false;
$enablePagination = false;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
