<?php declare(strict_types=1);
// /api/get_device.php

$method         = 'POST';
$path           = 'Device/GetDevice';
$useCache       = true;
$requiredFields = ['id'];

require __DIR__ . '/../includes/api_bootstrap.php';
