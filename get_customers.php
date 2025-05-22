<?php
// get_customers.php
header('Content-Type: application/json');
require_once __DIR__ . '/core/bootstrap.php';
require_login();
require_permission('view_customers');

try {
    $data = fetch_mps_api('Customer/GetCustomers');
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
