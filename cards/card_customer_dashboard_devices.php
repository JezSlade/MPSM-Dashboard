<?php declare(strict_types=1);
// /api/get_customer_dashboard_devices.php

$path           = 'CustomerDashboard/Devices';
$requiredFields = ['customerId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
