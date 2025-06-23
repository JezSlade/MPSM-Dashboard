<?php declare(strict_types=1);
// /api/get_customer_dashboard.php

$method         = 'POST';
$path           = 'CustomerDashboard/Get';
$useCache       = true;
$requiredFields = ['customerId'];

require __DIR__ . '/../includes/api_bootstrap.php';
