<?php declare(strict_types=1);
// /cards/card_get_customers.php

$path             = 'Customer/GetCustomers';
$payload          = [];
$requiredFields   = [];
$cardTitle        = 'Customers';
$columns          = ['CustomerCode'=>'Code','Description'=>'Description'];
$enableSearch     = true;
$enablePagination = false;
$pageSize         = 15;

require __DIR__ . '/../includes/card_bootstrap.php';
