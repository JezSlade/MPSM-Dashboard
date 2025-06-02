<?php
// get_devices.php
header('Content-Type: application/json');
require_once __DIR__ . '/core/bootstrap.php';
require_login();
require_permission('view_devices');

$cid = $_GET['customerId'] ?? '';
if (!$cid) {
    http_response_code(400);
    echo json_encode(['error'=>'Missing customerId']);
    exit;
}

try {
    $data = fetch_mps_api('CustomerDashboard/Devices',['customerCode'=>$cid]);
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
