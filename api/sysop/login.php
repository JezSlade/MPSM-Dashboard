<?php
require_once(__DIR__ . '/../../core/bootstrap.php');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Username and password required.']);
    exit;
}

if ($data['username'] === SYSOP_USERNAME && password_verify($data['password'], SYSOP_PASSWORD_HASH)) {
    session_start();
    $_SESSION['sysop'] = true;
    echo json_encode(['success' => true]);
} else {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid credentials.']);
}
