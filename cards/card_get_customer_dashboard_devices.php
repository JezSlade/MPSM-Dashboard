<?php declare(strict_types=1);
// /cards/card_get_customer_dashboard_devices.php

$path             = 'CustomerDashboard/Devices';
$payload          = ['Code' => ''];  // will be injected from URL or default
$cardTitle        = 'Customer Dashboard Devices';
$columns          = [
    'ExternalIdentifier' => 'Equipment ID',
    'Model'              => 'Model'
];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
