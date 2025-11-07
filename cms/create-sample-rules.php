<?php
/**
 * Analyze live panel messages and create sample notification rules
 * Based on actual callback data from MPSM
 *
 * Access via: https://mpsm.resolutionsbydesign.us/cms/create-sample-rules.php
 */

require 'config.php';
require 'functions.php';

requireAuth();

define('MPS_ENGINE_ACCESS', true);
require_once __DIR__ . '/../mps-api/callbacks/command-center-schema.php';

$pdo = getDatabase();
ensureCommandCenterTables($pdo);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Sample Rules - MPSM Dashboard</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
        h1 { color: #4ec9b0; }
        h2 { color: #569cd6; margin-top: 30px; }
        .success { color: #4ec9b0; }
        .info { color: #9cdcfe; }
        .warning { color: #ce9178; }
        .data { background: #2d2d30; padding: 10px; margin: 10px 0; border-left: 3px solid #007acc; }
        .rule { background: #252526; padding: 15px; margin: 10px 0; border-left: 3px solid #4ec9b0; }
        a { color: #569cd6; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<h1>📊 Analyze Live Data & Create Sample Rules</h1>

<?php

echo "<h2>Step 1: Analyzing Live Panel Message Data</h2>\n";

// Get unique alert codes from last 100 messages
$sql = "SELECT DISTINCT maintenance_alert_code, COUNT(*) as count
        FROM " . DB_PREFIX . "panel_messages
        WHERE maintenance_alert_code IS NOT NULL
        GROUP BY maintenance_alert_code
        ORDER BY count DESC
        LIMIT 10";

$stmt = $pdo->query($sql);
$alertCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<div class='data'><strong class='info'>Top Alert Codes:</strong><br>\n";
foreach ($alertCodes as $alert) {
    echo "  • <span class='warning'>{$alert['maintenance_alert_code']}</span> ({$alert['count']} occurrences)<br>\n";
}
echo "</div>\n";

// Get unique devices
$sql = "SELECT DISTINCT device_serial, customer_code, customer_description, COUNT(*) as count
        FROM " . DB_PREFIX . "panel_messages
        WHERE device_serial IS NOT NULL
        GROUP BY device_serial, customer_code, customer_description
        ORDER BY count DESC
        LIMIT 5";

$stmt = $pdo->query($sql);
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<div class='data'><strong class='info'>Top Devices:</strong><br>\n";
foreach ($devices as $device) {
    $customer = htmlspecialchars($device['customer_description'] ?: $device['customer_code'] ?: 'Unknown');
    echo "  • <span class='warning'>{$device['device_serial']}</span> @ {$customer} ({$device['count']} messages)<br>\n";
}
echo "</div>\n";

// Get unique customers
$sql = "SELECT DISTINCT customer_code, customer_description, COUNT(*) as count
        FROM " . DB_PREFIX . "panel_messages
        WHERE customer_code IS NOT NULL
        GROUP BY customer_code, customer_description
        ORDER BY count DESC
        LIMIT 5";

$stmt = $pdo->query($sql);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<div class='data'><strong class='info'>Top Customers:</strong><br>\n";
foreach ($customers as $customer) {
    $name = htmlspecialchars($customer['customer_description'] ?: $customer['customer_code']);
    echo "  • <span class='warning'>{$customer['customer_code']}</span>: {$name} ({$customer['count']} messages)<br>\n";
}
echo "</div>\n";

echo "<h2>Step 2: Creating Sample Notification Rules</h2>\n";

$rulesCreated = 0;

// Rule 1: Monitor all alerts (catch-all)
echo "<div class='rule'>\n";
echo "<strong class='success'>Rule 1: Monitor All Panel Messages</strong><br>\n";
$rule1 = [
    'name' => 'All Panel Messages Monitor',
    'description' => 'Catch all incoming panel messages for real-time monitoring',
    'severity' => 'warning',
    'alert_code_pattern' => '%',
    'notification_title' => 'Panel Alert - {device} has {alert}',
    'notification_message' => 'Device {device} triggered alert {alert}. Customer: {customer}',
    'show_dashboard' => 1,
    'auto_dismiss_hours' => 24,
    'enabled' => 1
];
$ruleId = insertRule($pdo, $rule1);
echo "Pattern: <code>alert_code = '%'</code> (matches all alerts)<br>\n";
echo "Severity: <span class='warning'>WARNING</span><br>\n";
echo "✓ Created rule ID {$ruleId}<br>\n";
echo "</div>\n";
$rulesCreated++;

// Rule 2: Specific alert code (if we have data)
if (!empty($alertCodes)) {
    $topAlert = $alertCodes[0]['maintenance_alert_code'];
    echo "<div class='rule'>\n";
    echo "<strong class='success'>Rule 2: Monitor '{$topAlert}' Alerts</strong><br>\n";

    $rule2 = [
        'name' => "Alert: {$topAlert}",
        'description' => "Monitor {$topAlert} alerts (most common alert in system)",
        'severity' => 'high',
        'alert_code_pattern' => $topAlert,
        'notification_title' => 'High Priority - {alert} on {device}',
        'notification_message' => '{device} has triggered {alert}. Customer: {customer}',
        'show_dashboard' => 1,
        'auto_dismiss_hours' => 12,
        'enabled' => 1
    ];

    $ruleId = insertRule($pdo, $rule2);
    echo "Pattern: <code>alert_code = '{$topAlert}'</code> (exact match)<br>\n";
    echo "Severity: <span style='color:#f39c12'>HIGH</span><br>\n";
    echo "✓ Created rule ID {$ruleId}<br>\n";
    echo "</div>\n";
    $rulesCreated++;
}

// Rule 3: Frequency-based for specific device (if we have data)
if (!empty($devices)) {
    $topDevice = $devices[0]['device_serial'];
    echo "<div class='rule'>\n";
    echo "<strong class='success'>Rule 3: High Frequency Alerts for '{$topDevice}'</strong><br>\n";

    $rule3 = [
        'name' => "High Frequency - {$topDevice}",
        'description' => "Alert when {$topDevice} triggers multiple times in short period",
        'severity' => 'critical',
        'device_serial_pattern' => $topDevice,
        'frequency_count' => 3,
        'frequency_window_hours' => 1,
        'frequency_type' => 'same_device',
        'notification_title' => 'CRITICAL - {device} Frequent Alerts',
        'notification_message' => '{device} has triggered {count} alerts in the past {window}',
        'show_dashboard' => 1,
        'auto_dismiss_hours' => 6,
        'enabled' => 1
    ];

    $ruleId = insertRule($pdo, $rule3);
    echo "Pattern: <code>device_serial = '{$topDevice}'</code><br>\n";
    echo "Threshold: <code>3 times in 1 hour</code><br>\n";
    echo "Severity: <span style='color:#e74c3c'>CRITICAL</span><br>\n";
    echo "✓ Created rule ID {$ruleId}<br>\n";
    echo "</div>\n";
    $rulesCreated++;
}

// Rule 4: Customer-specific monitoring (if we have data)
if (!empty($customers)) {
    $topCustomer = $customers[0]['customer_code'];
    $customerName = $customers[0]['customer_description'] ?: $topCustomer;
    echo "<div class='rule'>\n";
    echo "<strong class='success'>Rule 4: Monitor Customer '{$customerName}'</strong><br>\n";

    $rule4 = [
        'name' => "Customer Monitor - {$customerName}",
        'description' => "Monitor all alerts for customer {$customerName}",
        'severity' => 'info',
        'customer_code_pattern' => $topCustomer,
        'notification_title' => 'Customer Alert - {customer}',
        'notification_message' => '{customer}: Device {device} has {alert}',
        'show_dashboard' => 1,
        'auto_dismiss_hours' => 48,
        'enabled' => 1
    ];

    $ruleId = insertRule($pdo, $rule4);
    echo "Pattern: <code>customer_code = '{$topCustomer}'</code><br>\n";
    echo "Severity: <span style='color:#3498db'>INFO</span><br>\n";
    echo "✓ Created rule ID {$ruleId}<br>\n";
    echo "</div>\n";
    $rulesCreated++;
}

// Rule 5: Frequency-based system-wide
echo "<div class='rule'>\n";
echo "<strong class='success'>Rule 5: System-Wide High Frequency Monitor</strong><br>\n";
$rule5 = [
    'name' => 'System High Frequency Monitor',
    'description' => 'Alert when same alert code triggers 5+ times across any devices in 24 hours',
    'severity' => 'high',
    'frequency_count' => 5,
    'frequency_window_hours' => 24,
    'frequency_type' => 'same_alert',
    'notification_title' => 'System Alert - {alert} Frequency Spike',
    'notification_message' => 'Alert {alert} has occurred {count} times in the past {window} across multiple devices',
    'show_dashboard' => 1,
    'auto_dismiss_hours' => 12,
    'enabled' => 0  // Start disabled, user can enable if needed
];

$ruleId = insertRule($pdo, $rule5);
echo "Pattern: <code>any alert</code><br>\n";
echo "Threshold: <code>5 times in 24 hours (same alert, any device)</code><br>\n";
echo "Severity: <span style='color:#f39c12'>HIGH</span><br>\n";
echo "Status: <span style='color:#7f8c8d'>DISABLED</span> (enable manually if needed)<br>\n";
echo "✓ Created rule ID {$ruleId}<br>\n";
echo "</div>\n";
$rulesCreated++;

echo "<h2 class='success'>✓ Successfully Created {$rulesCreated} Notification Rules!</h2>\n";
echo "<p>Next steps:</p>\n";
echo "<ul>\n";
echo "<li><a href='command-center.php'>Open Command Center</a> to view and manage rules</li>\n";
echo "<li><a href='index.php'>Open Dashboard</a> to see hero notifications</li>\n";
echo "<li>Wait for next panel message callback to trigger notifications</li>\n";
echo "</ul>\n";

function insertRule(PDO $pdo, array $rule): int
{
    $table = DB_PREFIX . 'notification_rules';
    $sql = "INSERT INTO {$table}
            (name, description, severity, enabled, alert_code_pattern,
             device_serial_pattern, customer_code_pattern, frequency_count,
             frequency_window_hours, frequency_type, show_dashboard,
             auto_dismiss_hours, notification_title, notification_message)
            VALUES (:name, :description, :severity, :enabled, :alert_pattern,
                    :device_pattern, :customer_pattern, :freq_count,
                    :freq_window, :freq_type, :show_dash,
                    :auto_dismiss, :notif_title, :notif_message)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $rule['name'],
        ':description' => $rule['description'] ?? null,
        ':severity' => $rule['severity'] ?? 'warning',
        ':enabled' => $rule['enabled'] ?? 1,
        ':alert_pattern' => $rule['alert_code_pattern'] ?? null,
        ':device_pattern' => $rule['device_serial_pattern'] ?? null,
        ':customer_pattern' => $rule['customer_code_pattern'] ?? null,
        ':freq_count' => $rule['frequency_count'] ?? null,
        ':freq_window' => $rule['frequency_window_hours'] ?? null,
        ':freq_type' => $rule['frequency_type'] ?? 'same_device',
        ':show_dash' => $rule['show_dashboard'] ?? 1,
        ':auto_dismiss' => $rule['auto_dismiss_hours'] ?? null,
        ':notif_title' => $rule['notification_title'] ?? null,
        ':notif_message' => $rule['notification_message'] ?? null
    ]);

    return (int)$pdo->lastInsertId();
}
?>
</body>
</html>
