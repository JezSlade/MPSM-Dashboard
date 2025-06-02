<?php
// get_device_details.php
header('Content-Type: application/json');
require_once __DIR__ . '/core/bootstrap.php';
require_login();
require_permission('view_device');

$did = $_GET['deviceId'] ?? '';
if (!$did) {
    http_response_code(400);
    echo json_encode(['error'=>'Missing deviceId']);
    exit;
}

try {
    $data = fetch_mps_api('Device/GetDetailedInformations',['identifier'=>$did]);
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
