<?php declare(strict_types=1);
// /cards/card_get_device_counters.php

$path = 'Device/GetDeviceCounters';
$requiredFields = ['id'];
$payload = ['id' => ''];
$cardTitle = 'Get Device Counters';
$columns = ['ExternalIdentifier'=>'Equipment ID','Count'=>'Count'];
$enableSearch = false;
$enablePagination = true;
$pageSize = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
