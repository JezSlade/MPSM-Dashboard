<?php declare(strict_types=1);
// /cards/card_dashboard_supplies_counters.php

$path             = 'Dashboard/GetSuppliesCounters';
$payload          = [];
$requiredFields   = [];
$cardTitle        = 'Dashboard Supplies Counters';
$columns          = ['ExternalIdentifier'=>'Equipment ID','SuppliesRemaining'=>'Remaining'];
$enableSearch     = false;
$enablePagination = true;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
