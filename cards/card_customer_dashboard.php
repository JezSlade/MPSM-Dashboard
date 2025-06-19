<?php declare(strict_types=1);
// /cards/card_customer_dashboard.php

$path             = 'CustomerDashboard/Get';
$payload          = ['customerId'=>''];
$requiredFields   = ['customerId'];
$cardTitle        = 'Customer Dashboard';
$columns          = ['ReturnValue'=>'Info'];
$enableSearch     = false;
$enablePagination = false;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
