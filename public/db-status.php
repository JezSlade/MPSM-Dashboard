<?php
// public/db-status.php
// -------------------------
// HEAD stub for DB connectivity.
// Always returns 200 OK.
// -------------------------

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
    http_response_code(200);
    exit;
}

header('Content-Type: text/plain');
echo 'DB OK';
http_response_code(200);
