<?php
// modules/BlankModule/BlankModule.php
require_once __DIR__ . '/../../src/Auth.php';
Auth::checkLogin();

header('Content-Type: application/json');
echo json_encode(['ok' => true, 'message' => 'Blank module alive']);
