<?php
/**
 * Diagnostic endpoint to investigate why a customer has no alerts
 * Checks: rules configuration, panel messages, and alert aggregations
 */

require '../config.php';
require '../functions.php';

requireAuth();

header('Content-Type: application/json');

$pdo = getDatabase();
$customerCode = $_GET['customerCode'] ?? 'W9OPXL0YDK';

try {
    $result = [
        'customer_code' => $customerCode,
        'timestamp' => date('Y-m-d H:i:s'),
    ];

    // 1. Check notification rules and their customer patterns
    $rulesTable = DB_PREFIX . 'notification_rules';
    $rulesStmt = $pdo->query("
        SELECT id, name, severity, enabled,
               alert_code_pattern, device_serial_pattern, customer_code_pattern,
               frequency_count, frequency_window_hours, frequency_type,
               trigger_count, last_triggered_at
        FROM {$rulesTable}
        ORDER BY enabled DESC, severity DESC
    ");
    $rules = $rulesStmt->fetchAll(PDO::FETCH_ASSOC);

    $result['notification_rules'] = [
        'total_rules' => count($rules),
        'rules' => $rules
    ];

    // Analyze which rules would apply to this customer
    $applicableRules = [];
    foreach ($rules as $rule) {
        $pattern = $rule['customer_code_pattern'];
        $wouldMatch = false;
        $reason = '';

        if (empty($pattern)) {
            $wouldMatch = true;
            $reason = 'No customer filter (matches all)';
        } else {
            // Convert SQL LIKE pattern to regex
            $regex = str_replace('%', '.*', $pattern);
            $regex = str_replace('_', '.', $regex);
            $regex = '/^' . $regex . '$/i';

            if (preg_match($regex, $customerCode)) {
                $wouldMatch = true;
                $reason = "Pattern '{$pattern}' matches '{$customerCode}'";
            } else {
                $reason = "Pattern '{$pattern}' does NOT match '{$customerCode}'";
            }
        }

        $applicableRules[] = [
            'rule_id' => $rule['id'],
            'rule_name' => $rule['name'],
            'enabled' => (bool)$rule['enabled'],
            'customer_code_pattern' => $pattern,
            'would_match_customer' => $wouldMatch,
            'reason' => $reason
        ];
    }
    $result['rule_applicability'] = $applicableRules;

    // 2. Check panel_messages for this customer
    $pmTable = DB_PREFIX . 'panel_messages';

    // Total messages for this customer
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM {$pmTable} WHERE customer_code = :customer");
    $stmt->execute([':customer' => $customerCode]);
    $totalMessages = (int)$stmt->fetchColumn();

    // Messages with alert codes for this customer
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM {$pmTable}
        WHERE customer_code = :customer
          AND maintenance_alert_code IS NOT NULL
          AND maintenance_alert_code != ''
    ");
    $stmt->execute([':customer' => $customerCode]);
    $alertMessages = (int)$stmt->fetchColumn();

    // Get distinct alert codes for this customer
    $stmt = $pdo->prepare("
        SELECT maintenance_alert_code, COUNT(*) as count
        FROM {$pmTable}
        WHERE customer_code = :customer
          AND maintenance_alert_code IS NOT NULL
          AND maintenance_alert_code != ''
        GROUP BY maintenance_alert_code
        ORDER BY count DESC
        LIMIT 20
    ");
    $stmt->execute([':customer' => $customerCode]);
    $alertCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent messages
    $stmt = $pdo->prepare("
        SELECT id, device_serial, maintenance_alert_code, maintenance_alert_id,
               customer_code, ny_received_at
        FROM {$pmTable}
        WHERE customer_code = :customer
          AND maintenance_alert_code IS NOT NULL
        ORDER BY ny_received_at DESC
        LIMIT 10
    ");
    $stmt->execute([':customer' => $customerCode]);
    $recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result['panel_messages'] = [
        'total_for_customer' => $totalMessages,
        'with_alert_codes' => $alertMessages,
        'distinct_alert_codes' => $alertCodes,
        'recent_alert_messages' => $recentMessages
    ];

    // 3. Check alert_aggregations for this customer
    $aggTable = DB_PREFIX . 'alert_aggregations';
    $stmt = $pdo->prepare("
        SELECT device_serial, alert_code, occurrence_count,
               count_1h, count_24h, count_7d, count_30d,
               first_occurrence_ny, last_occurrence_ny
        FROM {$aggTable}
        WHERE customer_code = :customer
        ORDER BY last_occurrence_ny DESC
        LIMIT 20
    ");
    $stmt->execute([':customer' => $customerCode]);
    $aggregations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result['alert_aggregations'] = [
        'count' => count($aggregations),
        'data' => $aggregations
    ];

    // 4. Check rule_match_history for this customer
    $histTable = DB_PREFIX . 'rule_match_history';
    $stmt = $pdo->prepare("
        SELECT rule_id, rule_name, rule_severity, device_serial, alert_code, matched_at_ny
        FROM {$histTable}
        WHERE customer_code = :customer
        ORDER BY matched_at_ny DESC
        LIMIT 20
    ");
    $stmt->execute([':customer' => $customerCode]);
    $matchHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result['rule_match_history'] = [
        'count' => count($matchHistory),
        'data' => $matchHistory
    ];

    // 5. Check dashboard_notifications for this customer
    $notifTable = DB_PREFIX . 'dashboard_notifications';
    $stmt = $pdo->prepare("
        SELECT id, title, severity, status, device_serial, alert_code, customer_code,
               trigger_count, created_at_ny
        FROM {$notifTable}
        WHERE customer_code = :customer OR customer_code IS NULL OR customer_code = ''
        ORDER BY created_at_ny DESC
        LIMIT 20
    ");
    $stmt->execute([':customer' => $customerCode]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result['dashboard_notifications'] = [
        'count' => count($notifications),
        'data' => $notifications
    ];

    // 6. Summary and diagnosis
    $diagnosis = [];

    if ($totalMessages === 0) {
        $diagnosis[] = "ISSUE: No panel messages found for customer '{$customerCode}'";
    }

    if ($alertMessages === 0 && $totalMessages > 0) {
        $diagnosis[] = "ISSUE: Customer has messages but NONE have alert codes - no alerts to trigger rules";
    }

    $enabledRulesCount = count(array_filter($rules, fn($r) => $r['enabled']));
    if ($enabledRulesCount === 0) {
        $diagnosis[] = "ISSUE: No enabled notification rules exist";
    }

    $applicableEnabledRules = array_filter($applicableRules, fn($r) => $r['enabled'] && $r['would_match_customer']);
    if (count($applicableEnabledRules) === 0 && $enabledRulesCount > 0) {
        $diagnosis[] = "ISSUE: Rules exist but NONE match customer '{$customerCode}' - check customer_code_pattern on rules";
    }

    if (count($matchHistory) === 0 && $alertMessages > 0 && count($applicableEnabledRules) > 0) {
        $diagnosis[] = "ISSUE: Customer has alerts and matching rules but no rule matches recorded - check alert_code_pattern";
    }

    if (empty($diagnosis)) {
        $diagnosis[] = "No obvious issues detected - system appears configured correctly";
    }

    $result['diagnosis'] = $diagnosis;

    echo json_encode($result, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
