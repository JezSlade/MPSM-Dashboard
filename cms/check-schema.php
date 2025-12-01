<?php
require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== DASHBOARD_NOTIFICATIONS SCHEMA ===\n\n";

$stmt = $pdo->query("DESCRIBE " . DB_PREFIX . "dashboard_notifications");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo "{$col['Field']} ({$col['Type']}) - {$col['Null']} - {$col['Key']} - {$col['Default']}\n";
}

echo "\n=== NOTIFICATION_RULES SCHEMA ===\n\n";

$stmt = $pdo->query("DESCRIBE " . DB_PREFIX . "notification_rules");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo "{$col['Field']} ({$col['Type']}) - {$col['Null']} - {$col['Key']} - {$col['Default']}\n";
}
