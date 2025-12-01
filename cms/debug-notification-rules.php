<?php
/**
 * Debug: Why are no notifications being created?
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

define('MPS_ENGINE_ACCESS', true);
require __DIR__ . '/../mps-api/callbacks/command-center-engine.php';

requireAuth();

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== NOTIFICATION RULES DEBUG ===\n\n";

// Check rules
$stmt = $pdo->query("SELECT * FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
$rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Active Rules: " . count($rules) . "\n\n";

foreach ($rules as $rule) {
    echo "Rule ID {$rule['id']}: {$rule['name']}\n";
    echo "  Pattern: {$rule['alert_code_pattern']}\n";
    echo "  Frequency: {$rule['frequency_count']} in {$rule['frequency_window_hours']}h\n";
    echo "  Type: {$rule['frequency_type']}\n\n";

    // Test pattern matching
    $pattern = $rule['alert_code_pattern'];
    echo "  Testing pattern '{$pattern}':\n";

    // Get some sample alerts
    $testStmt = $pdo->query("
        SELECT DISTINCT maintenance_alert_code, COUNT(*) as count
        FROM " . DB_PREFIX . "panel_messages
        WHERE maintenance_alert_code IS NOT NULL
        GROUP BY maintenance_alert_code
        ORDER BY count DESC
        LIMIT 20
    ");

    $matches = 0;
    while ($alert = $testStmt->fetch(PDO::FETCH_ASSOC)) {
        if (matchesPattern($alert['maintenance_alert_code'], $pattern)) {
            $matches++;
            if ($matches <= 5) {
                echo "    ✓ Matches: {$alert['maintenance_alert_code']} ({$alert['count']} occurrences)\n";
            }
        }
    }

    if ($matches > 5) {
        echo "    ... and " . ($matches - 5) . " more matches\n";
    }

    if ($matches === 0) {
        echo "    ✗ NO MATCHES FOUND\n";
    }

    echo "\n";

    // Check frequency for matched alerts
    if ($matches > 0) {
        echo "  Checking frequency thresholds:\n";

        // Get a sample matched alert
        $testStmt = $pdo->query("
            SELECT DISTINCT maintenance_alert_code, device_serial
            FROM " . DB_PREFIX . "panel_messages
            WHERE maintenance_alert_code IS NOT NULL
            LIMIT 10
        ");

        $checked = 0;
        while ($sample = $testStmt->fetch(PDO::FETCH_ASSOC)) {
            if (!matchesPattern($sample['maintenance_alert_code'], $pattern)) {
                continue;
            }

            $checked++;
            if ($checked > 3) break;

            // Count occurrences
            $countSql = "SELECT COUNT(*) as count FROM " . DB_PREFIX . "panel_messages
                        WHERE maintenance_alert_code = :alert
                        AND device_serial = :device
                        AND received_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)";

            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute([
                ':alert' => $sample['maintenance_alert_code'],
                ':device' => $sample['device_serial'],
                ':hours' => $rule['frequency_window_hours']
            ]);

            $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
            $threshold = $rule['frequency_count'];
            $meets = $count >= $threshold ? '✓' : '✗';

            echo "    {$meets} {$sample['device_serial']} / {$sample['maintenance_alert_code']}: {$count} (need {$threshold})\n";
        }

        echo "\n";
    }
}

// Check what's in dashboard_notifications
echo "=== DASHBOARD NOTIFICATIONS ===\n\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total notifications in database: {$total}\n";

if ($total > 0) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications WHERE status = 'active'");
    $active = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Active notifications: {$active}\n";

    $stmt = $pdo->query("SELECT * FROM " . DB_PREFIX . "dashboard_notifications ORDER BY created_at DESC LIMIT 5");
    echo "\nLatest 5 notifications:\n";
    while ($notif = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$notif['id']}: {$notif['device_serial']} / {$notif['alert_code']} - {$notif['severity']} - {$notif['status']}\n";
    }
}

// Check panel messages
echo "\n=== PANEL MESSAGES ===\n\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "panel_messages");
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total panel messages: {$total}\n";

// Check date range
$stmt = $pdo->query("
    SELECT MIN(received_at) as oldest, MAX(received_at) as newest
    FROM " . DB_PREFIX . "panel_messages
");
$dates = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Date range: {$dates['oldest']} to {$dates['newest']}\n";

// Check recent messages
$stmt = $pdo->query("
    SELECT COUNT(*) as count
    FROM " . DB_PREFIX . "panel_messages
    WHERE received_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
");
$recent = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Messages in last 24h: {$recent}\n";

echo "\n=== TOP ALERT CODES (by frequency) ===\n\n";
$stmt = $pdo->query("
    SELECT maintenance_alert_code, COUNT(*) as total_count,
           COUNT(DISTINCT device_serial) as device_count
    FROM " . DB_PREFIX . "panel_messages
    WHERE maintenance_alert_code IS NOT NULL
    GROUP BY maintenance_alert_code
    ORDER BY total_count DESC
    LIMIT 10
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['maintenance_alert_code']}: {$row['total_count']} occurrences across {$row['device_count']} devices\n";
}

// Check if any device has repeated alerts
echo "\n=== DEVICES WITH REPEATED ALERTS (last 48h) ===\n\n";
$stmt = $pdo->query("
    SELECT device_serial, maintenance_alert_code, COUNT(*) as count
    FROM " . DB_PREFIX . "panel_messages
    WHERE received_at >= DATE_SUB(NOW(), INTERVAL 48 HOUR)
    AND maintenance_alert_code IS NOT NULL
    GROUP BY device_serial, maintenance_alert_code
    HAVING count >= 2
    ORDER BY count DESC
    LIMIT 10
");

$repeated = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($repeated)) {
    echo "NO devices have repeated alerts in last 48 hours!\n";
    echo "This explains why no notifications were created.\n";
} else {
    foreach ($repeated as $row) {
        echo "{$row['device_serial']} / {$row['maintenance_alert_code']}: {$row['count']} times\n";
    }
}
