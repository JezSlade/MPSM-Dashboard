<?php
/**
 * Debug Rule Matching - Show why rules aren't matching panel messages
 */

require dirname(__DIR__, 2) . '/cms/config.php';
require dirname(__DIR__, 2) . '/cms/functions.php';

$pdo = getDatabase();

echo "=== DEBUG: Rule Matching Analysis ===\n\n";

// Get one recent panel message
$stmt = $pdo->query("SELECT * FROM " . DB_PREFIX . "panel_messages ORDER BY received_at DESC LIMIT 1");
$message = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Sample Panel Message:\n";
echo "  ID: {$message['id']}\n";
echo "  Device: {$message['device_serial']}\n";
echo "  Alert Code: {$message['maintenance_alert_code']}\n";
echo "  Customer Code: {$message['customer_code']}\n";
echo "  Customer Desc: {$message['customer_description']}\n";
echo "  Panel Config: {$message['panel_configuration']}\n";
echo "\n";

// Get all active rules
$stmt = $pdo->query("SELECT * FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
$rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Active Rules:\n";
foreach ($rules as $rule) {
    echo "\nRule ID {$rule['id']}: {$rule['name']}\n";
    echo "  Alert Pattern: " . ($rule['alert_code_pattern'] ?: 'NULL') . "\n";
    echo "  Device Pattern: " . ($rule['device_serial_pattern'] ?: 'NULL') . "\n";
    echo "  Customer Pattern: " . ($rule['customer_code_pattern'] ?: 'NULL') . "\n";
    echo "  Frequency: " . ($rule['frequency_count'] ?: 'NULL') . " in " . ($rule['frequency_window_hours'] ?: 'NULL') . "h\n";
    echo "  Frequency Type: " . ($rule['frequency_type'] ?: 'NULL') . "\n";

    // Test if this rule would match
    $matches = false;
    $reason = '';

    // Check alert code pattern
    if ($rule['alert_code_pattern']) {
        if ($rule['alert_code_pattern'] === '%') {
            $matches = true;
            $reason = "Alert pattern '%' matches all";
        } elseif ($message['maintenance_alert_code'] === $rule['alert_code_pattern']) {
            $matches = true;
            $reason = "Alert code exact match: {$message['maintenance_alert_code']}";
        } else {
            $reason = "Alert code mismatch: {$message['maintenance_alert_code']} ≠ {$rule['alert_code_pattern']}";
        }
    }

    // Check device pattern
    if ($rule['device_serial_pattern']) {
        if ($message['device_serial'] === $rule['device_serial_pattern']) {
            $matches = true;
            $reason = "Device serial match: {$message['device_serial']}";
        } else {
            $matches = false;
            $reason = "Device mismatch: {$message['device_serial']} ≠ {$rule['device_serial_pattern']}";
        }
    }

    // Check customer pattern
    if ($rule['customer_code_pattern']) {
        if ($message['customer_code'] === $rule['customer_code_pattern']) {
            $matches = true;
            $reason = "Customer code match: {$message['customer_code']}";
        } else {
            $matches = false;
            $reason = "Customer mismatch: {$message['customer_code']} ≠ {$rule['customer_code_pattern']}";
        }
    }

    echo "  Match: " . ($matches ? "✓ YES" : "✗ NO") . " - {$reason}\n";
}

echo "\n";
