<?php declare(strict_types=1);
// /cards/card_device_consumables.php

$path             = 'Device/GetDeviceConsumables';
$payload          = ['deviceId'=>''];
$requiredFields   = ['deviceId'];
$cardTitle        = 'Device Consumables';
$columns          = ['SupplyName'=>'Supply','Remaining'=>'Remaining'];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
