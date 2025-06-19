<?php declare(strict_types=1);
// /cards/card_available_supplies.php

$path             = 'Device/GetAvailableSupplies';
$payload          = ['deviceId'=>''];
$requiredFields   = ['deviceId'];
$cardTitle        = 'Available Supplies';
$columns          = ['Supply'=>'Supply','QtyAvailable'=>'Available'];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
