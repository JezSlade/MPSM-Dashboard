<?php declare(strict_types=1);
// /cards/card_get_device.php

$path = 'Device/GetDevice';
$requiredFields = ['id'];
$payload = ['id' => ''];
$cardTitle = 'Get Device';
$columns = ['ExternalIdentifier'=>'Equipment ID','Count'=>'Count'];
$enableSearch = false;
$enablePagination = true;
$pageSize = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
