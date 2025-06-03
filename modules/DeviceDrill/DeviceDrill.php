<?php
require_once __DIR__ . '/../../src/ApiClient.php';
require_once __DIR__ . '/../../src/Auth.php';
Auth::checkLogin();
$api = new ApiClient();
$deviceId = $_GET['deviceId'] ?? '';
$details  = $api->getDeviceDetails($deviceId);
header('Content-Type: application/json');
echo json_encode(['details' => $details]);
