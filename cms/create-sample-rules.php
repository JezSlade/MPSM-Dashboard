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

$creationMessage = null;
$creationError = null;
$ruleFormData = [
    'name' => '',
    'severity' => 'warning',
    'alert_code_pattern' => '',
    'device_serial_pattern' => '',
    'customer_code_pattern' => '',
    'frequency_count' => '',
    'frequency_window_hours' => '',
    'frequency_type' => 'same_device',
    'show_dashboard' => 1,
    'auto_dismiss_hours' => '',
    'notification_title' => 'Alert {alert} on {device}',
    'notification_message' => '{device} triggered {alert} for {customer}. Count: {count} in {window}.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rule_form_submit'])) {
    $allowedSeverities = ['info', 'warning', 'high', 'critical'];
    $allowedFrequencyTypes = ['same_device', 'same_alert', 'same_customer', 'any'];

    $ruleFormData['name'] = trim($_POST['name'] ?? '');
    $ruleFormData['severity'] = strtolower(trim($_POST['severity'] ?? 'warning'));
    $ruleFormData['alert_code_pattern'] = trim($_POST['alert_code_pattern'] ?? '');
    $ruleFormData['device_serial_pattern'] = trim($_POST['device_serial_pattern'] ?? '');
    $ruleFormData['customer_code_pattern'] = trim($_POST['customer_code_pattern'] ?? '');
    $ruleFormData['frequency_count'] = isset($_POST['frequency_count']) && $_POST['frequency_count'] !== '' ? (int)$_POST['frequency_count'] : '';
    $ruleFormData['frequency_window_hours'] = isset($_POST['frequency_window_hours']) && $_POST['frequency_window_hours'] !== '' ? (int)$_POST['frequency_window_hours'] : '';
    $ruleFormData['frequency_type'] = $_POST['frequency_type'] ?? 'same_device';
    $ruleFormData['show_dashboard'] = isset($_POST['show_dashboard']) ? 1 : 0;
    $ruleFormData['auto_dismiss_hours'] = isset($_POST['auto_dismiss_hours']) && $_POST['auto_dismiss_hours'] !== '' ? (int)$_POST['auto_dismiss_hours'] : '';
    $ruleFormData['notification_title'] = trim($_POST['notification_title'] ?? '');
    $ruleFormData['notification_message'] = trim($_POST['notification_message'] ?? '');

    if ($ruleFormData['name'] === '') {
        $creationError = 'Rule name is required.';
    } elseif (!in_array($ruleFormData['severity'], $allowedSeverities, true)) {
        $creationError = 'Invalid severity.';
    } elseif (!in_array($ruleFormData['frequency_type'], $allowedFrequencyTypes, true)) {
        $creationError = 'Invalid frequency type.';
    } else {
        try {
            $newRule = [
                'name' => $ruleFormData['name'],
                'description' => 'Created via Rule Builder',
                'severity' => $ruleFormData['severity'],
                'enabled' => 1,
                'alert_code_pattern' => $ruleFormData['alert_code_pattern'] !== '' ? $ruleFormData['alert_code_pattern'] : null,
                'device_serial_pattern' => $ruleFormData['device_serial_pattern'] !== '' ? $ruleFormData['device_serial_pattern'] : null,
                'customer_code_pattern' => $ruleFormData['customer_code_pattern'] !== '' ? $ruleFormData['customer_code_pattern'] : null,
                'frequency_count' => $ruleFormData['frequency_count'] !== '' ? $ruleFormData['frequency_count'] : null,
                'frequency_window_hours' => $ruleFormData['frequency_window_hours'] !== '' ? $ruleFormData['frequency_window_hours'] : null,
                'frequency_type' => $ruleFormData['frequency_type'],
                'show_dashboard' => $ruleFormData['show_dashboard'],
                'auto_dismiss_hours' => $ruleFormData['auto_dismiss_hours'] !== '' ? $ruleFormData['auto_dismiss_hours'] : null,
                'notification_title' => $ruleFormData['notification_title'] !== '' ? $ruleFormData['notification_title'] : null,
                'notification_message' => $ruleFormData['notification_message'] !== '' ? $ruleFormData['notification_message'] : null
            ];

            $createdId = insertRule($pdo, $newRule);
            $creationMessage = "Rule created successfully. ID: {$createdId}";
        } catch (Throwable $e) {
            $creationError = 'Failed to create rule: ' . $e->getMessage();
        }
    }
}
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
        .builder { background: #252526; padding: 16px; margin: 16px 0; border-left: 3px solid #9cdcfe; }
        .builder h3 { margin-top: 0; color: #9cdcfe; }
        .builder label { display: block; margin-top: 10px; color: #d4d4d4; }
        .builder input[type="text"],
        .builder input[type="number"],
        .builder select,
        .builder textarea { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #3c3c3c; background: #1e1e1e; color: #d4d4d4; }
        .builder textarea { min-height: 80px; }
        .builder .row { display: flex; gap: 12px; flex-wrap: wrap; }
        .builder .col { flex: 1; min-width: 220px; }
        .builder .actions { margin-top: 14px; display: flex; gap: 10px; flex-wrap: wrap; }
        .builder button { background: #0e639c; color: #fff; border: none; padding: 8px 12px; cursor: pointer; }
        .builder button.secondary { background: #3c3c3c; }
        .builder .hint { color: #9cdcfe; font-size: 0.9em; margin-top: 2px; }
        .builder .preview { background: #1e1e1e; padding: 10px; border: 1px dashed #3c3c3c; margin-top: 10px; }
        .builder .status { margin-top: 10px; padding: 10px; }
        .builder .status.success { border-left: 3px solid #4ec9b0; color: #4ec9b0; background: #1f2d2b; }
        .builder .status.error { border-left: 3px solid #ce9178; color: #ce9178; background: #2a1f1c; }
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
$alertDisplayNames = [];

if (!empty($alertCodes)) {
    $alertCodeValues = array_values(array_filter(array_unique(array_map(static function ($row) {
        return $row['maintenance_alert_code'] ?? null;
    }, $alertCodes))));

    if (!empty($alertCodeValues)) {
        $placeholders = implode(',', array_fill(0, count($alertCodeValues), '?'));

        // Primary: alert_definitions (user-maintained display names)
        try {
            $defsTable = DB_PREFIX . 'alert_definitions';
            $defsStmt = $pdo->prepare("SELECT alert_code, display_name, description FROM {$defsTable} WHERE alert_code IN ($placeholders) AND enabled = 1");
            $defsStmt->execute($alertCodeValues);
            foreach ($defsStmt->fetchAll(PDO::FETCH_ASSOC) as $def) {
                $code = (string)($def['alert_code'] ?? '');
                if ($code === '') {
                    continue;
                }
                $displayName = trim((string)($def['display_name'] ?? ''));
                $fallbackDescription = trim((string)($def['description'] ?? ''));
                $alertDisplayNames[$code] = $displayName !== '' ? $displayName : $fallbackDescription;
            }
        } catch (Throwable $e) {
            error_log('[create-sample-rules] Failed to load alert_definitions: ' . $e->getMessage());
        }

        // Fallback: legacy alert_code_descriptions
        $missingCodes = array_diff($alertCodeValues, array_keys($alertDisplayNames));
        if (!empty($missingCodes)) {
            try {
                $descTable = DB_PREFIX . 'alert_code_descriptions';
                $descPlaceholders = implode(',', array_fill(0, count($missingCodes), '?'));
                $descStmt = $pdo->prepare("SELECT alert_code, description FROM {$descTable} WHERE alert_code IN ($descPlaceholders) AND is_active = 1");
                $descStmt->execute(array_values($missingCodes));
                foreach ($descStmt->fetchAll(PDO::FETCH_ASSOC) as $desc) {
                    $code = (string)($desc['alert_code'] ?? '');
                    if ($code === '' || isset($alertDisplayNames[$code])) {
                        continue;
                    }
                    $label = trim((string)($desc['description'] ?? ''));
                    $alertDisplayNames[$code] = $label;
                }
            } catch (Throwable $e) {
                error_log('[create-sample-rules] Failed to load alert_code_descriptions: ' . $e->getMessage());
            }
        }

        // Last-resort: panel_configuration captured from payloads
        $missingCodes = array_diff($alertCodeValues, array_keys($alertDisplayNames));
        if (!empty($missingCodes)) {
            try {
                $messageTable = DB_PREFIX . 'panel_messages';
                $configPlaceholders = implode(',', array_fill(0, count($missingCodes), '?'));
                $configStmt = $pdo->prepare("
                    SELECT maintenance_alert_code, MAX(panel_configuration) AS panel_configuration
                    FROM {$messageTable}
                    WHERE maintenance_alert_code IN ($configPlaceholders)
                      AND panel_configuration IS NOT NULL
                      AND panel_configuration != ''
                    GROUP BY maintenance_alert_code
                ");
                $configStmt->execute(array_values($missingCodes));
                foreach ($configStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $code = (string)($row['maintenance_alert_code'] ?? '');
                    if ($code === '' || isset($alertDisplayNames[$code])) {
                        continue;
                    }
                    $label = trim((string)($row['panel_configuration'] ?? ''));
                    if ($label !== '') {
                        $alertDisplayNames[$code] = $label;
                    }
                }
            } catch (Throwable $e) {
                error_log('[create-sample-rules] Failed to load panel_configuration fallback: ' . $e->getMessage());
            }
        }
    }
}

echo "<div class='data'><strong class='info'>Top Alert Codes:</strong><br>\n";
foreach ($alertCodes as $alert) {
    $code = (string)($alert['maintenance_alert_code'] ?? '');
    $codeLabel = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
    $description = $alertDisplayNames[$code] ?? '';
    $descriptionLabel = $description !== ''
        ? htmlspecialchars($description, ENT_QUOTES, 'UTF-8')
        : 'No description mapped';
    $count = (int)$alert['count'];

    echo "  • <span class='warning'>{$codeLabel}</span> - {$descriptionLabel} ({$count} occurrences)<br>\n";
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
            VALUES (:name, :descr