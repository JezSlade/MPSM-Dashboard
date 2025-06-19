<?php declare(strict_types=1);
// /cards/card_dashboard_device_actions.php

$path             = 'Dashboard/GetDeviceActionsDashboard';
$payload          = [];
$requiredFields   = [];
$cardTitle        = 'Dashboard Device Actions';
$columns          = ['ExternalIdentifier'=>'Equipment ID','ActionCount'=>'Actions'];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
