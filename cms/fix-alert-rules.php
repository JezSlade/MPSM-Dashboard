<?php
/**
 * FIX: Replace descriptive patterns with actual numeric alert codes
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

requireAuth();

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== FIXING ALERT RULES ===\n\n";

// Top alert codes from diagnostic:
// 808, 807, 1, 8, 13, 801, 3, 903, 9, 811

// Update rules to use actual numeric patterns
$fixes = [
    [
        'name' => 'Repeated JAM Alerts',
        'old_pattern' => 'JAM%',
        'new_pattern' => '808',  // Most common alert
        'new_description' => 'Triggers when a device reports 3 or more 808 alerts within 24 hours (808 = Paper Jam or similar)'
    ],
    [
        'name' => 'Persistent Sensor Failures',
        'old_pattern' => 'SENS%',
        'new_pattern' => '807',  // Second most common
        'new_description' => 'Triggers when a device reports 5 or more 807 alerts within 48 hours (807 = Sensor/System issue)'
    ],
    [
        'name' => 'Widespread Communication Loss',
        'old_pattern' => 'COMM%',
        'new_pattern' => '80%',  // Matches 801, 807, 808 (communication/system alerts)
        'new_description' => 'Triggers when 10+ devices report 80X alerts within 1 hour (communication/system issues)'
    ]
];

foreach ($fixes as $fix) {
    $stmt = $pdo->prepare("
        UPDATE " . DB_PREFIX . "notification_rules
        SET alert_code_pattern = :new_pattern,
            description = :new_desc
        WHERE name = :name
    ");

    $result = $stmt->execute([
        ':new_pattern' => $fix['new_pattern'],
        ':new_desc' => $fix['new_description'],
        ':name' => $fix['name']
    ]);

    if ($result) {
        echo "✓ Updated '{$fix['name']}'\n";
        echo "  {$fix['old_pattern']} → {$fix['new_pattern']}\n\n";
    }
}

// Disable rules that don't match any alerts
$disable = ['Emergency Stop Pattern', 'Motor Overload Pattern'];
foreach ($disable as $name) {
    $stmt = $pdo->prepare("UPDATE " . DB_PREFIX . "notification_rules SET enabled = 0 WHERE name = :name");
    $stmt->execute([':name' => $name]);
    echo "⚠️  Disabled '{$name}' (no matching alert codes)\n";
}

echo "\n=== UPDATED RULES ===\n\n";
$stmt = $pdo->query("SELECT id, name, alert_code_pattern, enabled FROM " . DB_PREFIX . "notification_rules ORDER BY id");
while ($rule = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $status = $rule['enabled'] ? 'ACTIVE' : 'DISABLED';
    echo "{$rule['id']}: {$rule['name']} ({$rule['alert_code_pattern']}) - {$status}\n";
}

echo "\n=== NEXT STEP ===\n";
echo "Reprocess messages: https://mpsm.resolutionsbydesign.us/cms/bulk-reprocess.php\n";
