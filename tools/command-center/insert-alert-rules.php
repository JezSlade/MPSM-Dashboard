<?php
/**
 * Insert Common Sense Alert Rules
 * Run once to populate 5 standard alert rules for recurring device issues
 * Usage: php tools/command-center/insert-alert-rules.php
 */

// Direct database connection (CLI script)
$repoRoot = dirname(__DIR__, 2);
require_once $repoRoot . '/config/env.php';
mpsm_load_env($repoRoot . '/.env');

$pdo = new PDO(
    sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        mpsm_env('DB_HOST', 'localhost'),
        mpsm_env('DB_NAME', ''),
        mpsm_env('DB_CHARSET', 'utf8mb4')
    ),
    mpsm_env('DB_USER', ''),
    mpsm_env('DB_PASS', ''),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

define('DB_PREFIX', 'mpsm_');

$rules = [
    [
        'name' => 'Repeated JAM Alerts',
        'description' => 'Triggers when a device reports 3 or more JAM alerts within 24 hours, indicating potential mechanical issues requiring maintenance.',
        'severity' => 'high',
        'enabled' => 1,
        'alert_code_pattern' => 'JAM%',
        'frequency_count' => 3,
        'frequency_window_hours' => 24,
        'frequency_type' => 'same_device',
        'show_dashboard' => 1,
        'auto_dismiss_hours' => 48,
        'notification_title' => 'Recurring JAM Issue Detected',
        'notification_message' => 'Device experiencing repeated jam alerts. Inspect for mechanical blockages, paper fragments, or sensor malfunctions.'
    ],
    [
        'name' => 'Emergency Stop Pattern',
        'description' => 'Triggers when 2 or more emergency stop alerts occur at the same customer location within 12 hours, suggesting safety concerns or operator errors.',
        'severity' => 'critical',
        'enabled' => 1,
        'alert_code_pattern' => 'E-%',
        'frequency_count' => 2,
        'frequency_window_hours' => 12,
        'frequency_type' => 'same_customer',
        'show_dashboard' => 1,
        'auto_dismiss_hours' => 24,
        'notification_title' => 'Multiple Emergency Stops',
        'notification_message' => 'Multiple emergency stop events detected at this location. Review safety protocols and operator training. Verify emergency stop button function.'
    ],
    [
        'name' => 'Persistent Sensor Failures',
        'description' => 'Triggers when a device reports 5+ sensor errors within 48 hours, indicating sensor hardware failure or wiring issues.',
        'severity' => 'high',
        'enabled' => 1,
        'alert_code_pattern' => 'SENS%',
        'frequency_count' => 5,
        'frequency_window_hours' => 48,
        'frequency_type' => 'same_device',
        'show_dashboard' => 1,
        'auto_dismiss_hours' => 72,
        'notification_title' => 'Sensor System Malfunction',
        'notification_message' => 'Device reporting persistent sensor errors. Check sensor connections, wiring integrity, and consider sensor replacement.'
    ],
    [
        'name' => 'Widespread Communication Loss',
        'description' => 'Triggers when 10+ devices report communication loss within 1 hour, suggesting network infrastructure issues rather than individual device failures.',
        'severity' => 'critical',
        'enabled' => 1,
        'alert_code_pattern' => 'COMM%',
        'frequency_count' => 10,
        'frequency_window_hours' => 1,
        'frequency_type' => 'same_alert',
        'show_dashboard' => 1,
        'auto_dismiss_hours' => 6,
        'notification_title' => 'Network Infrastructure Issue',
        'notification_message' => 'Multiple devices experiencing communication loss simultaneously. Check network infrastructure, router status, and ISP connection.'
    ],
    [
        'name' => 'Motor Overload Pattern',
        'description' => 'Triggers when a device reports 4+ motor overload alerts within 24 hours, indicating mechanical binding, excessive load, or motor failure.',
        'severity' => 'high',
        'enabled' => 1,
        'alert_code_pattern' => 'MOT%',
        'frequency_count' => 4,
        'frequency_window_hours' => 24,
        'frequency_type' => 'same_device',
        'show_dashboard' => 1,
        'auto_dismiss_hours' => 48,
        'notification_title' => 'Motor Stress Detected',
        'notification_message' => 'Device experiencing repeated motor overload conditions. Inspect for mechanical binding, excessive resistance, belt tension, or motor bearing wear.'
    ]
];

$table = DB_PREFIX . 'notification_rules';
$inserted = 0;
$skipped = 0;

foreach ($rules as $rule) {
    // Check if rule already exists
    $checkSql = "SELECT id FROM {$table} WHERE name = :name";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':name' => $rule['name']]);

    if ($checkStmt->fetch()) {
        echo "SKIP: Rule '{$rule['name']}' already exists\n";
        $skipped++;
        continue;
    }

    // Insert rule
    $sql = "INSERT INTO {$table}
            (name, description, severity, enabled, alert_code_pattern,
             device_serial_pattern, customer_code_pattern, frequency_count,
             frequency_window_hours, frequency_type, show_dashboard,
             send_email, email_recipients, auto_dismiss_hours,
             notification_title, notification_message, created_by, created_at)
            VALUES (:name, :description, :severity, :enabled, :alert_pattern,
                    NULL, NULL, :freq_count,
                    :freq_window, :freq_type, :show_dash, 0,
                    NULL, :auto_dismiss, :notif_title,
                    :notif_message, 1, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $rule['name'],
        ':description' => $rule['description'],
        ':severity' => $rule['severity'],
        ':enabled' => $rule['enabled'],
        ':alert_pattern' => $rule['alert_code_pattern'],
        ':freq_count' => $rule['frequency_count'],
        ':freq_window' => $rule['frequency_window_hours'],
        ':freq_type' => $rule['frequency_type'],
        ':show_dash' => $rule['show_dashboard'],
        ':auto_dismiss' => $rule['auto_dismiss_hours'],
        ':notif_title' => $rule['notification_title'],
        ':notif_message' => $rule['notification_message']
    ]);

    echo "OK: Inserted rule '{$rule['name']}' (ID: {$pdo->lastInsertId()})\n";
    $inserted++;
}

echo "\n=== SUMMARY ===\n";
echo "Inserted: {$inserted}\n";
echo "Skipped: {$skipped}\n";
echo "Total: " . ($inserted + $skipped) . "\n";

if ($inserted > 0) {
    echo "\n=== NEXT STEPS ===\n";
    echo "1. View rules: https://mpsm.resolutionsbydesign.us/cms/command-center.php?tab=rules\n";
    echo "2. Edit/disable rules as needed via Command Center\n";
    echo "3. Monitor notifications: https://mpsm.resolutionsbydesign.us/cms/command-center.php?tab=notifications\n";
}

/*
CHANGELOG
2025-11-28 Codex
- Created script to insert 5 common-sense alert rules for recurring device issues
- Rules target: JAM%, E-%, SENS%, COMM%, MOT% alert patterns
- Frequency thresholds calibrated for actionable alerts (not too noisy)
- Auto-dismiss configured to prevent notification buildup
*/
