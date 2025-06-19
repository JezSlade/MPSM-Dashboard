<?php declare(strict_types=1);
// /cards/card_device_fields.php

$path             = 'Device/GetDeviceFields';
$payload          = ['deviceId'=>''];
$requiredFields   = ['deviceId'];
$cardTitle        = 'Device Fields';
$columns          = ['FieldName'=>'Name','FieldValue'=>'Value'];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
