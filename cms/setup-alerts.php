<?php
/**
 * Alert System Setup and Reprocessing Script
 * Web-accessible interface for inserting alert rules and reprocessing messages
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

define('MPS_ENGINE_ACCESS', true);
require __DIR__ . '/../mps-api/callbacks/command-center-engine.php';

// Require authentication
requireAuth();

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();
$action = $_GET['action'] ?? 'menu';

echo "=== MPSM Alert System Setup ===\n\n";

// Menu
if ($action === 'menu') {
    echo "Available actions:\n\n";
    echo "1. Insert Alert Rules:\n";
    echo "   https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=insert_rules\n\n";
    echo "2. Reprocess Panel Messages:\n";
    echo "   https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=reprocess\n\n";
    echo "3. View Status:\n";
    echo "   https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=status\n\n";
    exit;
}

// Status check
if ($action === 'status') {
    echo "=== System Status ===\n\n";

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
    $activeRules = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Active notification rules: {$activeRules}\n";

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications WHERE status = 'active'");
    $activeNotifications = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Active notifications: {$activeNotifications}\n";

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "panel_messages");
    $totalMessages = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Total panel messages: {$totalMessages}\n\n";

    if ($activeRules > 0) {
        echo "Rules:\n";
        $stmt = $pdo->query("SELECT name, alert_code_pattern, severity FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
        while ($rule = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$rule['name']} ({$rule['alert_code_pattern']}) - {$rule['severity']}\n";
        }
    }

    exit;
}

// Insert alert rules
if ($action === 'insert_rules') {
    echo "=== Inserting Common Sense Alert Rules ===\n\n";

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
                        :notif_message, :user_id, NOW())";

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
            ':notif_message' => $rule['notification_message'],
            ':user_id' => $_SESSION['user_id'] ?? 1
        ]);

        echo "OK: Inserted rule '{$rule['name']}' (ID: {$pdo->lastInsertId()})\n";
        $inserted++;
    }

    echo "\n=== SUMMARY ===\n";
    echo "Inserted: {$inserted}\n";
    echo "Skipped: {$skipped}\n";
    echo "Total: " . ($inserted + $skipped) . "\n\n";

    if ($inserted > 0) {
        echo "Next step: Reprocess panel messages\n";
        echo "https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=reprocess\n";
    }

    exit;
}

// Reprocess panel messages
if ($action === 'reprocess') {
    echo "=== Reprocessing Panel Messages ===\n\n";

    // Get count of panel messages
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "panel_messages");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalMessages = $result['total'];

    echo "Total panel messages: {$totalMessages}\n";

    // Get active rules count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "notification_rules WHERE enabled = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $activeRules = $result['total'];

    echo "Active notification rules: {$activeRules}\n\n";

    if ($activeRules === 0) {
        echo "ERROR: No active notification rules found.\n";
        echo "Insert rules first: https://mpsm.resolutionsbydesign.us/cms/setup-alerts.php?action=insert_rules\n";
        exit(1);
    }

    // Get processing limit from query param (default 500)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 500;

    // Fetch panel messages (most recent first)
    $sql = "SELECT id, device_serial, maintenance_alert_code,
                   customer_code, customer_description, panel_configuration,
                   received_at
            FROM " . DB_PREFIX . "panel_messages
            ORDER BY received_at DESC
            LIMIT {$limit}";

    $stmt = $pdo->query($sql);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Processing last " . count($messages) . " panel messages...\n\n";

    $processed = 0;
    $notificationsCreated = 0;
    $errors = 0;

    foreach ($messages as $message) {
        try {
            $messageData = [
                'device_serial' => $message['device_serial'],
                'maintenance_alert_code' => $message['maintenance_alert_code'],
                'customer_code' => $message['customer_code'],
                'customer_description' => $message['customer_description'],
                'panel_configuration' => $message['panel_configuration']
            ];

            // Count notifications before processing
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
            $beforeCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Process message through rules engine
            processNotificationRules($pdo, (int)$message['id'], $messageData);

            // Count notifications after processing
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "dashboard_notifications");
            $afterCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $created = $afterCount - $beforeCount;
            $notificationsCreated += $created;

            $processed++;

            if ($created > 0) {
                echo "✓ Message {$message['id']}: {$message['device_serial']} / {$message['maintenance_alert_code']} → {$created} notification(s)\n";
            }

        } catch (Exception $e) {
            $errors++;
            echo "✗ Message {$message['id']}: ERROR - {$e->getMessage()}\n";
        }
    }

    echo "\n=== Reprocessing Complete ===\n";
    echo "Messages processed: {$processed}\n";
    echo "Notifications created: {$notificationsCreated}\n";
    echo "Errors: {$errors}\n\n";

    // Show current notification counts
    $stmt = $pdo->query("
        SELECT severity, COUNT(*) as count
        FROM " . DB_PREFIX . "dashboard_notifications
        WHERE status = 'active'
        GROUP BY severity
        ORDER BY FIELD(severity, 'critical', 'high', 'warning', 'info')
    ");
    $severityCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($severityCounts)) {
        echo "Active Notifications by Severity:\n";
        foreach ($severityCounts as $row) {
            echo "  - {$row['severity']}: {$row['count']}\n";
        }
    } else {
        echo "No active notifications found.\n";
    }

    echo "\nView results:\n";
    echo "https://mpsm.resolutionsbydesign.us/cms/command-center.php?tab=notifications\n";

    exit;
}

echo "Invalid action. Use action=menu, action=insert_rules, action=reprocess, or action=status\n";
