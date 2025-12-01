<?php
// Check current system status
header('Content-Type: text/plain');

$pdo = new PDO(
    'mysql:host=localhost;dbname=resolut7_mpsm;charset=utf8mb4',
    'resolut7_mpsm_agent',
    '!C@S@lcd6McFceb8',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== SYSTEM STATUS ===\n\n";

// Check rules
$stmt = $pdo->query("SELECT COUNT(*) as total FROM mpsm_notification_rules WHERE enabled = 1");
$rules = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Active notification rules: {$rules['total']}\n";

if ($rules['total'] > 0) {
    echo "\nRules:\n";
    $stmt = $pdo->query("SELECT id, name, alert_code_pattern, frequency_count, frequency_window_hours FROM mpsm_notification_rules WHERE enabled = 1");
    while ($rule = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$rule['id']}: {$rule['name']} ({$rule['alert_code_pattern']}) - {$rule['frequency_count']} in {$rule['frequency_window_hours']}h\n";
    }
}

// Check notifications
$stmt = $pdo->query("SELECT COUNT(*) as total FROM mpsm_dashboard_notifications WHERE status = 'active'");
$notifs = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nActive dashboard notifications: {$notifs['total']}\n";

// Check panel messages
$stmt = $pdo->query("SELECT COUNT(*) as total FROM mpsm_panel_messages");
$messages = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total panel messages: {$messages['total']}\n";

// Check top alert codes
echo "\nTop 10 Alert Codes:\n";
$stmt = $pdo->query("
    SELECT maintenance_alert_code, COUNT(*) as count
    FROM mpsm_panel_messages
    WHERE maintenance_alert_code IS NOT NULL
    GROUP BY maintenance_alert_code
    ORDER BY count DESC
    LIMIT 10
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['maintenance_alert_code']}: {$row['count']} occurrences\n";
}

// Check recent panel messages
echo "\nRecent Panel Messages (last 24h):\n";
$stmt = $pdo->query("
    SELECT COUNT(*) as count
    FROM mpsm_panel_messages
    WHERE received_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
");
$recent = $stmt->fetch(PDO::FETCH_ASSOC);
echo "  Messages in last 24h: {$recent['count']}\n";
