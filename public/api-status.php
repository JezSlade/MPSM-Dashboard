<?php
// public/api-status.php
// -------------------------
// HEAD request stub for API connectivity.
// Always returns 200 OK for now.
// -------------------------

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
    http_response_code(200);
    exit;
}
header('Content-Type: text/plain');
echo 'API OK';
http_response_code(200);
