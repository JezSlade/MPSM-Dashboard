<?php declare(strict_types=1);
// /cards/card_dashboard_overview.php

$path             = 'Dashboard/Get';
$payload          = [];
$requiredFields   = [];
$cardTitle        = 'Dashboard Overview';
$columns          = ['TotalDevices'=>'Devices','TotalSupplies'=>'Supplies'];
$enableSearch     = false;
$enablePagination = false;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
