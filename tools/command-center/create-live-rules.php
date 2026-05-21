<?php
/**
 * Analyze live panel messages and create sample notification rules
 * Based on actual callback data from MPSM
 */

$repoRoot = dirname(__DIR__, 2);
require $repoRoot . '/cms/config.php';
require $repoRoot . '/cms/functions.php';

define('MPS_ENGINE_ACCESS', true);
require $repoRoot . '/mps-api/callbacks/command-center-schema.php';

$pdo = getDatabase();
ensureCommandCenterTables($pdo);

echo "=== Analyzing Live Panel Message Data ===\n\n";

// Get unique alert codes from last 100 messages
$sql = "SELECT DISTINCT maintenance_alert_code, COUNT(*) as count
        FROM " . DB_PREFIX . "panel_messages
        WHERE maintenance_alert_code IS NOT NULL
        GROUP BY maintenance_alert_code
        ORDER BY count DESC
        LIMIT 10";

$stmt = $pdo->query($sql);
$alertCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Top Alert Codes:\n";
foreach ($alertCodes as $alert) {
    echo "  - {$alert['maintenance_alert_code']} ({$alert['count']} occurrences)\n";
}
echo "\n";

// Get unique devices
$sql = "SELECT DISTINCT device_serial, customer_code, customer_description, COUNT(*) as count
        FROM " . DB_PREFIX . "panel_messages
        WHERE device_serial IS NOT NULL
        GROUP BY device_serial, customer_code, customer_description
        ORDER BY count DESC
        LIMIT 5";

$stmt = $pdo->query($sql);
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Top Devices:\n";
foreach ($devices as $device) {
    $customer = $device['customer_description'] ?: $device['customer_code'] ?: 'Unknown';
    echo "  - {$device['device_serial']} @ {$customer} ({$device['count']} messages)\n";
}
echo "\n";

// Get unique customers
$sql = "SELECT DISTINCT customer_code, customer_description, COUNT(*) as count
        FROM " . DB_PREFIX . "panel_messages
        WHERE customer_code IS NOT NULL
        GROUP BY customer_code, customer_description
        ORDER BY count DESC
        LIMIT 5";

$stmt = $pdo->query($sql);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Top Customers:\n";
foreach ($customers as $customer) {
    $name = $customer['customer_description'] ?: $customer['customer_code'];
    echo "  - {$customer['customer_code']}: {$name} ({$customer['count']} messages)\n";
}
echo "\n";

echo "=== Creating Sample Notification Rules ===\n\n";

function insertRule(PDO $pdo, array $rule): void
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

    $ruleId = $pdo->lastInsertId();
    echo "  ✓ Created rule ID {$ruleId}: {$rule['name']}\n";
}

// Rule 1: Monitor all alerts (catch-all)
echo "Creating Rule 1: Monitor All Panel Messages...\n";
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

insertRule($pdo, $rule1);

// Rule 2: Specific alert code (if we have data)
if (!empty($alertCodes)) {
    $topAlert = $alertCodes[0]['maintenance_alert_code'];
    echo "Creating Rule 2: Monitor '{$topAlert}' alerts...\n";

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

    insertRule($pdo, $rule2);
}

// Rule 3: Frequency-based for specific device (if we have data)
if (!empty($devices)) {
    $topDevice = $devices[0]['device_serial'];
    echo "Creating Rule 3: High Frequency Alerts for device '{$topDevice}'...\n";

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

    insertRule($pdo, $rule3);
}

// Rule 4: Customer-specific monitoring (if we have data)
if (!empty($customers)) {
    $topCustomer = $customers[0]['customer_code'];
    $customerName = $customers[0]['customer_description'] ?: $topCustomer;
    echo "Creating Rule 4: Monitor customer '{$customerName}'...\n";

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

    insertRule($pdo, $rule4);
}

// Rule 5: Frequency-based system-wide
echo "Creating Rule 5: System-Wide High Frequency Monitor...\n";
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

insertRule($pdo, $rule5);

echo "\n=== Rules Created Successfully ===\n";
echo "Visit Command Center to view and manage rules:\n";
echo "https://mpsm.resolutionsbydesign.us/cms/command-center.php\n\n";
