<?php declare(strict_types=1);
// /cards/card_get_device_overview.php

$path = 'Device/GetDeviceOverview';
$requiredFields = ['id'];
$payload = ['id' => ''];
$cardTitle = 'Get Device Overview';
$columns = ['ExternalIdentifier'=>'Equipment ID','Count'=>'Count'];
$enableSearch = false;
$enablePagination = true;
$pageSize = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
