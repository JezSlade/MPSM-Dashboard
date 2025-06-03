<?php
require_once __DIR__ . '/../../src/ApiClient.php';
require_once __DIR__ . '/../../src/Auth.php';
Auth::checkLogin();
$api = new ApiClient();
$customers = $api->getCustomers();
$results = array_map(function($c) {
    return [
        'CustomerCode' => $c['CustomerCode'] ?? '',
        'Name'         => $c['Name']         ?? ''
    ];
}, $customers);
header('Content-Type: application/json');
echo json_encode(['customers' => $results]);
