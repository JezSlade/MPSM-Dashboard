<?php
// get_token.php
header('Content-Type: application/json');
require_once __DIR__ . '/core/bootstrap.php';
require_login();
try {
    $t = get_mps_token();
    echo json_encode(['access_token'=>$t]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
