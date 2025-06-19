<?php declare(strict_types=1);
// /cards/card_device_extended_property.php

$path             = 'Device/GetDeviceExtendedProperty';
$payload          = ['id'=>'','name'=>''];
$requiredFields   = ['id', 'name'];
$cardTitle        = 'Device Extended Property';
$columns          = ['PropertyName'=>'Name','PropertyValue'=>'Value'];
$enableSearch     = false;
$enablePagination = false;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
