<?php

// PHP Debugging Lines - START
// Enable all error reporting for development purposes.
// This helps in identifying and debugging issues quickly.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// PHP Debugging Lines - END

$logPath = __DIR__ . '/logs/uuid_visits.log';
if (!file_exists($logPath)) {
    echo json_encode(['unique' => 0, 'total' => 0]);
    exit;
}
$lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$uuids = [];
$total = 0;
foreach ($lines as $line) {
    [$uuid] = explode('|', $line);
    $uuids[$uuid] = true;
    $total++;
}
echo json_encode(['unique' => count($uuids), 'total' => $total]);
