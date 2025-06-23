<?php declare(strict_types=1);
// /cards/card_get_device_details.php

$path = 'Device/GetDeviceDetails';
$requiredFields = ['id'];
$payload = ['id' => ''];
$cardTitle = 'Get Device Details';
$columns = ['ExternalIdentifier'=>'Equipment ID','Count'=>'Count'];
$enableSearch = false;
$enablePagination = true;
$pageSize = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
