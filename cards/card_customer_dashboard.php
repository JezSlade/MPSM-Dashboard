<?php declare(strict_types=1);
// /api/get_customer_dashboard.php

$path           = 'CustomerDashboard/Get';
$requiredFields = ['customerId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
