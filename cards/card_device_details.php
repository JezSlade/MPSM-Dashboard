<?php declare(strict_types=1);
// /cards/card_device_details.php

$path             = 'Device/GetDeviceDetails';
$payload          = ['id'=>''];
$requiredFields   = ['id'];
$cardTitle        = 'Device Details';
$columns          = ['DetailKey'=>'Key','DetailValue'=>'Value'];
$enableSearch     = false;
$enablePagination = false;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
