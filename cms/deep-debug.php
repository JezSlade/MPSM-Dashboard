<?php
/**
 * DEEP DEBUG: Test rule matching step-by-step
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

define('MPS_ENGINE_ACCESS', true);
require __DIR__ . '/../mps-api/callbacks/command-center-engine.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== DEEP DEBUG: RULE MATCHING ===\n\n";

// Get a device with repeated 808 alerts
$stmt = $pdo->query("
    SELECT device_serial, maintenance_alert_code, COUNT(*) as count,
           MIN(received_at) as first_seen, MAX(received_at) as last_seen
    FROM " . DB_PREFIX . "panel_messages
    WHERE received_at >= DATE_SUB(NOW(), INTERVAL 48 HOUR)
    AND maintenance_alert_code = '808'
    GROUP BY device_serial, maintenance_alert_code
    HAVING count >= 3
    ORDER BY count DESC
    LIMIT 1
");

$test = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$test) {
    echo "❌ No device with 3+ 808 alerts in last 48h\n";
    exit;
}

echo "Test Device: {$test['device_serial']}\n";
echo "Alert Code: {$test['maintenance_alert_code']}\n";
echo "Count: {$test['count']}\n";
echo "First: {$test['first_seen']}\n";
echo "Last: {$test['last_seen']}\n\n";

// Get the rule
$stmt = $pdo->query("SELECT * FROM " . DB_PREFIX . "notification_rules WHERE name = 'Repeated JAM Alerts' AND enabled = 1");
$rule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rule) {
    echo "❌ Rule 'Repeated JAM Alerts' not found or not enabled\n";
    exit;
}

echo "=== RULE DETAILS ===\n";
echo "Pattern: {$rule['alert_code_pattern']}\n";
echo "Frequency: {$rule['frequency_count']} in {$rule['frequency_window_hours']}h\n";
echo "Type: {$rule['frequency_type']}\n\n";

// Test 1: Pattern Matching
echo "=== TEST 1: PATTERN MATCHING ===\n";
$matches = matchesPattern($test['maintenance_alert_code'], $rule['alert_code_pattern']);
echo "matchesPattern('{$test['maintenance_alert_code']}', '{$rule['alert_code_pattern']}'): " . ($matches ? 'TRUE ✓' : 'FALSE ✗') . "\n\n";

if (!$matches) {
    echo "❌ PATTERN DOESN'T MATCH - This is the problem!\n";
    echo "Alert code: '{$test['maintenance_alert_code']}'\n";
    echo "Pattern: '{$rule['alert_code_pattern']}'\n";
    exit;
}

// Test 2: Frequency Check
echo "=== TEST 2: FREQUENCY CHECK ===\n";

$countSql = "SELECT COUNT(*) as count
            FROM " . DB_PREFIX . "panel_messages
            WHERE maintenance_alert_code = :alert
            AND device_serial = :device
            AND received_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)";

$countStmt = $pdo->prepare($countSql);
$countStmt->execute([
    ':alert' => $test['maintenance_alert_code'],
    ':device' => $test['device_serial'],
    ':hours' => $rule['frequency_window_hours']
]);

$frequency = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
$threshold = $rule['frequency_count'];

echo "Messages in last {$rule['frequency_window_hours']}h: {$frequency}\n";
echo "Threshold: {$threshold}\n";
echo "Meets threshold: " . ($frequency >= $threshold ? 'TRUE ✓' : 'FALSE ✗') . "\n\n";

if ($frequency < $threshold) {
    echo "❌ FREQUENCY TOO LOW\n";
    exit;
}

// Test 3: Check for existing notification
echo "=== TEST 3: DEDUPLICATION CHECK ===\n";

$dedupSql = "SELECT id FROM " . DB_PREFIX . "dashboard_notifications
            WHERE rule_id = :rule_id
            AND device_serial = :device
            AND alert_code = :alert
            AND status = 'active'";

$dedupStmt = $pdo->prepare($dedupSql);
$dedupStmt->execute([
    ':rule_id' => $rule['id'],
    ':device' => $test['device_serial'],
    ':alert' => $test['maintenance_alert_code']
]);

$existing = $dedupStmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "Existing notification: ID {$existing['id']}\n";
    echo "⚠️  DUPLICATE - Notification already exists\n\n";
} else {
    echo "No existing notification found ✓\n\n";
}

// Test 4: Try creating notification manually
echo "=== TEST 4: CREATE NOTIFICATION ===\n";

// Get message details
$msgStmt = $pdo->prepare("SELECT * FROM " . DB_PREFIX . "panel_messages WHERE device_serial = :device AND maintenance_alert_code = :alert LIMIT 1");
$msgStmt->execute([':device' => $test['device_serial'], ':alert' => $test['maintenance_alert_code']]);
$message = $msgStmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    echo "❌ Could not get message details\n";
    exit;
}

$messageData = [
    'device_serial' => $message['device_serial'],
    'maintenance_alert_code' => $message['maintenance_alert_code'],
    'customer_code' => $message['customer_code'],
    'customer_description' => $message['customer_description'],
    'panel_configuration' => $message['panel_configuration']
];

echo "Message ID: {$message['id']}\n";
echo "Customer: {$message['customer_code']}\n";
echo "Panel: {$message['panel_configuration']}\n\n";

try {
    processNotificationRules($pdo, $message['id'], $messageData);
    echo "✓ processNotificationRules() executed\n\n";

    // Check if notification was created
    $checkStmt = $pdo->prepare("SELECT id FROM " . DB_PREFIX . "dashboard_notifications WHERE rule_id = :rule_id AND device_serial = :device AND alert_code = :alert ORDER BY created_at DESC LIMIT 1");
    $checkStmt->execute([
        ':rule_id' => $rule['id'],
        ':device' => $test['device_serial'],
        ':alert' => $test['maintenance_alert_code']
    ]);

    $newNotif = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($newNotif) {
        echo "✅ SUCCESS! Notification created: ID {$newNotif['id']}\n";
    } else {
        echo "❌ FAILED: No notification was created\n";
        echo "This means the rule logic is blocking creation.\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
