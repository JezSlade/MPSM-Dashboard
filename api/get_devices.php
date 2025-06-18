<?php
require_once __DIR__ . '/../includes/api_functions.php';

// Read input: allow caller to pass ?customerCode=XYZ
$in = json_decode(file_get_contents('php://input'), true) ?: $_GET;
$customerCode = trim($in['customercode'] ?? '');

// If none given, pull the *first* customer
if ($customerCode === '') {
    $list = get_customers();
    $customerCode = $list[0]['CustomerCode'] ?? '';
    if ($customerCode === '') {
        http_response_code(400);
        echo json_encode(['error'=>'No customerCode available'], JSON_PRETTY_PRINT);
        exit;
    }
}

$devices = get_devices($customerCode);
echo json_encode([
  'Result'      => $devices,
  'TotalRows'   => count($devices),
  'IsValid'     => true,
  'Errors'      => [],
  'ReturnValue' => ''
], JSON_PRETTY_PRINT);
