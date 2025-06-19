<?php declare(strict_types=1);
// /api/get_token.php

require __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');
$token  = get_token($config);

header('Content-Type: application/json');
echo json_encode(['access_token' => $token]);
