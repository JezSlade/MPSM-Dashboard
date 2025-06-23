<?php declare(strict_types=1);
// /api/get_available_supplies.php

$method         = 'POST';
$path           = 'Device/GetAvailableSupplies';
$useCache       = true;
$requiredFields = ['deviceId'];

require __DIR__ . '/../includes/api_bootstrap.php';
