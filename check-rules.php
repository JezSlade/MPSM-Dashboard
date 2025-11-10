<?php
// Quick script to check notification rules status
header('Content-Type: text/plain');

$pdo = new PDO(
    'mysql:host=localhost;dbname=resolut7_mpsm;charset=utf8mb4',
    'resolut7_mpsm_agent',
    '!C@S@lcd6McFceb8',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== NOTIFICATION RULES ===\n\n";
$stmt = $pdo->query("SELECT id, name, severity, is_active, alert_code_pattern, frequency_count, frequency_window_hours FROM mpsm_notification_rules ORDER BY id");
$rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rules as $rule) {
    echo "ID {$rule['id']}: {$rule['name']}\n";
    echo "  Severity: {$rule['severity']}\n";
    echo "  Active: " . ($rule['is_active'] ? 'YES' : 'NO') . "\n";
    echo "  Alert Pattern: " . ($rule['alert_code_pattern'] ?: 'any') . "\n";
    echo "  Frequency: " . ($rule['frequency_count'] ? "{$rule['frequency_count']} in {$rule['frequency_window_hours']}h" : 'none') . "\n";
    echo "\n";
}

echo "\n=== DASHBOARD NOTIFICATIONS ===\n\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM mpsm_dashboard_notifications");
$count = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total notifications: {$count['total']}\n\n";

echo "\n=== PANEL MESSAGES (Top Alert Codes) ===\n\n";
$stmt = $pdo->query("
    SELECT maintenance_alert_code, COUNT(*) as count
    FROM mpsm_panel_messages
    WHERE maintenance_alert_code IS NOT NULL
    GROUP BY maintenance_alert_code
    ORDER BY count DESC
    LIMIT 10
");
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($alerts as $alert) {
    echo "Code {$alert['maintenance_alert_code']}: {$alert['count']} messages\n";
}
