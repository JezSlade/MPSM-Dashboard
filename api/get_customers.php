<?php
// --- DEBUG ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

header('Content-Type: application/json');

$cachePath = __DIR__ . '/../cache/data.json';

if (!file_exists($cachePath)) {
  http_response_code(500);
  echo json_encode(["error" => "Cache file not found"]);
  exit;
}

$cache = json_decode(file_get_contents($cachePath), true);
echo json_encode($cache['customers']);