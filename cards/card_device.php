<?php declare(strict_types=1);
// /cards/card_device.php

$path             = 'Device/GetDevice';
$payload          = ['id'=>''];
$requiredFields   = ['id'];
$cardTitle        = 'Device';
$columns          = ['ExternalIdentifier'=>'Equipment ID','Model'=>'Model'];
$enableSearch     = false;
$enablePagination = false;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
