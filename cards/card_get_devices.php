<?php declare(strict_types=1);
// /cards/card_get_devices.php

$path = 'Device/GetDevices';
$payload = ['CustomerCode' => ''];
$cardTitle = 'Devices';
$columns = ['ExternalIdentifier'=>'Equipment ID','Model'=>'Model'];
$enableSearch = true;
$enablePagination = true;
$pageSize = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
