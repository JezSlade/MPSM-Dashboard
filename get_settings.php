<?php
// get_settings.php
header('Content-Type: application/json');
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/core/permissions.php';

require_permission('view_debug');

$pdo = get_db();
$stmt = $pdo->query("SELECT `key`,`value` FROM global_settings");
$rows = $stmt->fetchAll();
$out = [];
foreach ($rows as $r) {
    $out[$r['key']] = $r['value'];
}
echo json_encode($out);
