<?php declare(strict_types=1);
// /cards/card_device_management.php

$path             = 'Device/GetDeviceManagement';
$payload          = ['deviceId'=>''];
$requiredFields   = ['deviceId'];
$cardTitle        = 'Device Management';
$columns          = ['Manager'=>'Manager','Contact'=>'Contact'];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
