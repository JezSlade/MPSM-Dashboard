<?php
require 'cms/config.php';
$pdo = getDatabase();
$stmt = $pdo->query('SELECT * FROM mpsm_panel_messages ORDER BY id DESC LIMIT 10');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results, JSON_PRETTY_PRINT);
