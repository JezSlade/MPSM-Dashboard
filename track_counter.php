<?php
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
