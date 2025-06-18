<?php declare(strict_types=1);
// /cards/card_get_customer_dashboard.php

$path             = 'CustomerDashboard/Get';
$payload          = ['Code' => ''];  // will be injected from URL or default
$cardTitle        = 'Customer Dashboard';
$columns          = ['ReturnValue' => 'Info']; // adjust as needed
$enableSearch     = false;
$enablePagination = false;

require __DIR__ . '/../includes/card_bootstrap.php';
