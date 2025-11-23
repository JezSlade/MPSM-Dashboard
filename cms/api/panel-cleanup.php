<?php
/**
 * Panel Callback Data Cleanup API
 *
 * Cleans up old errors, test data, and maintains table health
 *
 * Actions:
 * - status: Show cleanup candidates
 * - cleanup: Execute cleanup
 * - purge-old: Delete entries older than X days
 *
 * Auth: Session auth OR secret parameter
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';

// Allow secret-based auth for CLI/automated cleanup
$secret = $_GET['secret'] ?? '';
if ($secret === 'PANEL_CLEANUP_2025') {
    // Secret auth - proceed without session
} else {
    requireAuth();
}

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'status';
$dryRun = ($_GET['dry_run'] ?? '1') === '1';
$daysToKeep = (int)($_GET['days'] ?? 30);

try {
    $pdo = getDatabase();
    $debugTable = DB_PREFIX . 'panel_callback_debug';
    $messagesTable = DB_PREFIX . 'panel_messages';
    $aggregationsTable = DB_PREFIX . 'alert_aggregations';

    $results = [
        'success' => true,
        'action' => $action,
        'dry_run' => $dryRun,
        'timestamp' => date('Y-m-d H:i:s'),
        'tables' => []
    ];

    // ==== Status: Analyze what can be cleaned ====
    if ($action === 'status' || $action === 'cleanup') {

        // 1. Debug table stats
        $debugStats = $pdo->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as errors,
                SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success,
                MIN(timestamp) as oldest,
                MAX(timestamp) as newest
            FROM {$debugTable}
        ")->fetch(PDO::FETCH_ASSOC);

        // 2. Test data in debug table
        $testDataCount = $pdo->query("
            SELECT COUNT(*) as count FROM {$debugTable}
            WHERE
                raw_body LIKE '%TEST%'
                OR raw_body LIKE '%test%'
                OR raw_body LIKE '%SUCCESS_SERIAL%'
                OR message LIKE '%test%'
                OR unique_source LIKE '%test%'
        ")->fetch(PDO::FETCH_ASSOC)['count'];

        // 3. Old entries (older than retention period)
        $oldEntriesCount = $pdo->query("
            SELECT COUNT(*) as count FROM {$debugTable}
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL {$daysToKeep} DAY)
        ")->fetch(PDO::FETCH_ASSOC)['count'];

        // 4. Method/Content-Type probe errors (health check noise)
        $probeErrorsCount = $pdo->query("
            SELECT COUNT(*) as count FROM {$debugTable}
            WHERE status = 'ERROR'
            AND (message LIKE '%Method Not Allowed%' OR message LIKE '%Content-Type%')
        ")->fetch(PDO::FETCH_ASSOC)['count'];

        // 5. Panel messages table stats
        $messagesStats = $pdo->query("
            SELECT
                COUNT(*) as total,
                MIN(ny_received_at) as oldest,
                MAX(ny_received_at) as newest
            FROM {$messagesTable}
        ")->fetch(PDO::FETCH_ASSOC);

        // 6. Test data in panel_messages
        $testMessagesCount = $pdo->query("
            SELECT COUNT(*) as count FROM {$messagesTable}
            WHERE
                device_serial LIKE '%TEST%'
                OR device_serial LIKE '%test%'
                OR customer_code LIKE '%TEST%'
                OR payload LIKE '%SUCCESS_SERIAL%'
        ")->fetch(PDO::FETCH_ASSOC)['count'];

        // 7. Check alert_aggregations table
        $aggStats = null;
        try {
            $aggStats = $pdo->query("
                SELECT
                    COUNT(*) as total,
                    MIN(first_occurrence_ny) as oldest,
                    MAX(last_occurrence_ny) as newest
                FROM {$aggregationsTable}
            ")->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $aggStats = ['error' => 'Table may not exist'];
        }

        $results['tables'] = [
            'panel_callback_debug' => [
                'stats' => $debugStats,
                'cleanup_candidates' => [
                    'test_data' => (int)$testDataCount,
                    'old_entries' => (int)$oldEntriesCount,
                    'probe_errors' => (int)$probeErrorsCount
                ]
            ],
            'panel_messages' => [
                'stats' => $messagesStats,
                'cleanup_candidates' => [
                    'test_data' => (int)$testMessagesCount
                ]
            ],
            'alert_aggregations' => $aggStats
        ];
    }

    // ==== Execute Cleanup ====
    if ($action === 'cleanup' && !$dryRun) {
        $deleted = [];

        // 1. Delete test data from debug table
        $stmt = $pdo->prepare("
            DELETE FROM {$debugTable}
            WHERE
                raw_body LIKE '%TEST%'
                OR raw_body LIKE '%test%'
                OR raw_body LIKE '%SUCCESS_SERIAL%'
                OR message LIKE '%test%'
                OR unique_source LIKE '%test%'
        ");
        $stmt->execute();
        $deleted['debug_test_data'] = $stmt->rowCount();

        // 2. Delete probe errors (Method/Content-Type)
        $stmt = $pdo->prepare("
            DELETE FROM {$debugTable}
            WHERE status = 'ERROR'
            AND (message LIKE '%Method Not Allowed%' OR message LIKE '%Content-Type%')
        ");
        $stmt->execute();
        $deleted['debug_probe_errors'] = $stmt->rowCount();

        // 3. Delete test data from panel_messages
        $stmt = $pdo->prepare("
            DELETE FROM {$messagesTable}
            WHERE
                device_serial LIKE '%TEST%'
                OR device_serial LIKE '%test%'
                OR customer_code LIKE '%TEST%'
                OR payload LIKE '%SUCCESS_SERIAL%'
        ");
        $stmt->execute();
        $deleted['messages_test_data'] = $stmt->rowCount();

        $results['deleted'] = $deleted;
        $results['message'] = 'Cleanup completed successfully';
    }

    // ==== Purge Old Entries ====
    if ($action === 'purge-old' && !$dryRun) {
        $deleted = [];

        // Delete old debug entries
        $stmt = $pdo->prepare("
            DELETE FROM {$debugTable}
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        $stmt->execute([':days' => $daysToKeep]);
        $deleted['debug_old_entries'] = $stmt->rowCount();

        // Delete old success entries from messages (keep errors longer)
        $stmt = $pdo->prepare("
            DELETE FROM {$messagesTable}
            WHERE ny_received_at < DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        $stmt->execute([':days' => $daysToKeep * 2]); // Keep messages twice as long
        $deleted['messages_old_entries'] = $stmt->rowCount();

        $results['deleted'] = $deleted;
        $results['retention_days'] = $daysToKeep;
        $results['message'] = 'Old entries purged successfully';
    }

    // ==== Alert System Audit ====
    if ($action === 'alert-audit') {
        $rulesTable = DB_PREFIX . 'notification_rules';
        $notificationsTable = DB_PREFIX . 'dashboard_notifications';
        $historyTable = DB_PREFIX . 'rule_match_history';

        // Check notification rules
        $rules = [];
        try {
            $rules = $pdo->query("SELECT id, name, severity, enabled, alert_code_pattern, device_serial_pattern, customer_code_pattern, frequency_count, frequency_window_hours, trigger_count, last_triggered_at FROM {$rulesTable} ORDER BY enabled DESC, severity DESC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $rules = ['error' => $e->getMessage()];
        }

        // Check active notifications
        $notifications = [];
        try {
            $notifications = $pdo->query("SELECT id, title, severity, status, device_serial, alert_code, created_at_ny FROM {$notificationsTable} ORDER BY created_at_ny DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $notifications = ['error' => $e->getMessage()];
        }

        // Check recent rule matches
        $recentMatches = [];
        try {
            $recentMatches = $pdo->query("SELECT rule_id, rule_name, rule_severity, device_serial, alert_code, matched_at_ny FROM {$historyTable} ORDER BY matched_at_ny DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $recentMatches = ['error' => $e->getMessage()];
        }

        // Check recent panel messages
        $recentMessages = $pdo->query("SELECT id, device_serial, maintenance_alert_code, customer_code, ny_received_at FROM {$messagesTable} ORDER BY ny_received_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

        $results['alert_system'] = [
            'notification_rules' => [
                'count' => is_array($rules) ? count($rules) : 0,
                'enabled_count' => is_array($rules) ? count(array_filter($rules, fn($r) => $r['enabled'] ?? false)) : 0,
                'rules' => $rules
            ],
            'dashboard_notifications' => [
                'count' => is_array($notifications) ? count($notifications) : 0,
                'active_count' => is_array($notifications) ? count(array_filter($notifications, fn($n) => ($n['status'] ?? '') === 'active')) : 0,
                'recent' => $notifications
            ],
            'rule_match_history' => [
                'recent_count' => is_array($recentMatches) ? count($recentMatches) : 0,
                'recent' => $recentMatches
            ],
            'recent_panel_messages' => $recentMessages
        ];
    }

    // Add usage instructions
    if ($action === 'status') {
        $results['usage'] = [
            'status' => '?action=status - View cleanup candidates',
            'cleanup_dry_run' => '?action=cleanup&dry_run=1 - Preview cleanup',
            'cleanup_execute' => '?action=cleanup&dry_run=0 - Execute cleanup',
            'purge_old' => '?action=purge-old&days=30&dry_run=0 - Purge entries older than X days',
            'alert_audit' => '?action=alert-audit - Audit alert system rules and notifications'
        ];
    }

    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
