<?php declare(strict_types=1);
// /api/get_available_supplies.php

$path           = 'Device/GetAvailableSupplies';
$requiredFields = ['deviceId'];
$useCache       = true;

require __DIR__ . '/../includes/api_bootstrap.php';
