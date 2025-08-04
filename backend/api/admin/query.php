<?php
/**
 * API: /api/admin/query.php
 * Allows query access to curated_data SQLite table.
 */

$db = new PDO('sqlite:' . __DIR__ . '/../../db/curated.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$source = $_GET['source'] ?? '';
$limit = (int) ($_GET['limit'] ?? 100);
$search = $_GET['q'] ?? '';

$sql = "SELECT id, source, payload, created_at FROM curated_data WHERE 1=1";
$params = [];

if ($source) {
    $sql .= " AND source = :source";
    $params[':source'] = $source;
}

if ($search) {
    $sql .= " AND payload LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

$sql .= " ORDER BY created_at DESC LIMIT :limit";
$params[':limit'] = $limit;

$stmt = $db->prepare($sql);
foreach ($params as $key => &$val) {
    $stmt->bindParam($key, $val);
}
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($results);
