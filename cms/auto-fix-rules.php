<?php
/**
 * AUTO-FIX: Update alert rule patterns (NO AUTH for automation)
 * SECURITY: DELETE THIS FILE AFTER USE
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== AUTO-FIXING ALERT RULES ===\n\n";

try {
    // Update Rule 1: JAM% → 808
    $stmt = $pdo->prepare("
        UPDATE " . DB_PREFIX . "notification_rules
        SET alert_code_pattern = '808',
            description = 'Triggers when a device reports 3 or more 808 alerts within 24 hours (common system alert)'
        WHERE name = 'Repeated JAM Alerts'
    ");
    $stmt->execute();
    echo "✓ Updated 'Repeated JAM Alerts': JAM% → 808\n";

    // Update Rule 2: SENS% → 807
    $stmt = $pdo->prepare("
        UPDATE " . DB_PREFIX . "notification_rules
        SET alert_code_pattern = '807',
            description = 'Triggers when a device reports 5 or more 807 alerts within 48 hours (sensor/system issue)'
        WHERE name = 'Persistent Sensor Failures'
    ");
    $stmt->execute();
    echo "✓ Updated 'Persistent Sensor Failures': SENS% → 807\n";

    // Update Rule 3: COMM% → 80%
    $stmt = $pdo->prepare("
        UPDATE " . DB_PREFIX . "notification_rules
        SET alert_code_pattern = '80%',
            description = 'Triggers when 10+ devices report 80X alerts within 1 hour (widespread system issues)'
        WHERE name = 'Widespread Communication Loss'
    ");
    $stmt->execute();
    echo "✓ Updated 'Widespread Communication Loss': COMM% → 80%\n";

    // Disable rules with no matches
    $stmt = $pdo->prepare("
        UPDATE " . DB_PREFIX . "notification_rules
        SET enabled = 0
        WHERE name IN ('Emergency Stop Pattern', 'Motor Overload Pattern')
    ");
    $stmt->execute();
    echo "⚠️  Disabled 'Emergency Stop Pattern' and 'Motor Overload Pattern' (no matching codes)\n\n";

    echo "=== UPDATED RULES ===\n\n";
    $stmt = $pdo->query("
        SELECT id, name, alert_code_pattern, enabled, frequency_count, frequency_window_hours
        FROM " . DB_PREFIX . "notification_rules
        ORDER BY id
    ");

    while ($rule = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = $rule['enabled'] ? 'ACTIVE' : 'DISABLED';
        echo "ID {$rule['id']}: {$rule['name']}\n";
        echo "  Pattern: {$rule['alert_code_pattern']}\n";
        echo "  Threshold: {$rule['frequency_count']} in {$rule['frequency_window_hours']}h\n";
        echo "  Status: {$status}\n\n";
    }

    echo "=== SUCCESS ===\n\n";
    echo "Rules updated successfully!\n";
    echo "Next: Run bulk reprocessing\n";
    echo "https://mpsm.resolutionsbydesign.us/cms/bulk-reprocess.php\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    exit(1);
}
