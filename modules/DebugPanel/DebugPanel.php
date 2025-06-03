<?php
require_once __DIR__ . '/../../src/DebugLogger.php';
require_once __DIR__ . '/../../src/Auth.php';
Auth::checkLogin();
$logfile = __DIR__ . '/../../storage/debug.log';
if (file_exists($logfile)) {
    echo htmlspecialchars(file_get_contents($logfile));
} else {
    echo "No logs found.";
}
