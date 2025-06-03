<?php
// modules/DeviceList/DeviceList.php
require_once __DIR__ . '/../../src/ApiClient.php';
require_once __DIR__ . '/../../src/Auth.php';

Auth::checkLogin();
$api = new ApiClient();

$customer = $_GET['customer'] ?? '';
$page     = intval($_GET['page'] ?? 1);
$rows     = intval($_GET['rows'] ?? 10);

$res = $api->getDeviceList($customer, $page, $rows);
header('Content-Type: application/json');
echo json_encode([
    'devices' => $res['devices'],
    'total'   => $res['total']
]);
