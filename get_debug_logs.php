<?php
// get_debug_logs.php
header('Content-Type: application/json');
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/core/permissions.php';

require_permission('view_debug');

$limit = intval($_GET['limit'] ?? get_setting('debug_widget_row_limit',200));
$pdo   = get_db();
$stmt  = $pdo->prepare("
  SELECT created_at, level, message, context
    FROM debug_logs
  ORDER BY id DESC
  LIMIT ?
");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->execute();
echo json_encode($stmt->fetchAll());
