<?php
/**
 * Diagnostic endpoint to check notification state
 * Temporary file for debugging - can be removed after issue resolved
 */

require '../config.php';
require '../functions.php';

requireAuth();

header('Content-Type: application/json');

$pdo = getDatabase();
$table = DB_PREFIX . 'dashboard_notifications';

try {
    // Get all notifications (not just active)
    $all = $pdo->query("
        SELECT id, title, severity, status, device_serial, alert_code, customer_code,
               trigger_count, created_at_ny, expires_at_ny
        FROM {$table}
        ORDER BY created_at_ny DESC
        LIMIT 50
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Count by status
    $statusCounts = $pdo->query("
        SELECT status, COUNT(*) as count
        FROM {$table}
        GROUP BY status
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Get unique customer codes in notifications
    $customerCodes = $pdo->query("
        SELECT DISTINCT customer_code, COUNT(*) as count
        FROM {$table}
        WHERE status = 'active'
        GROUP BY customer_code
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Current customer code from request
    $requestedCustomer = $_GET['customerCode'] ?? 'NOT PROVIDED';

    // Test the exact query that getNotifications uses
    $testQuery = "
        SELECT COUNT(*) as count
        FROM {$table} dn
        WHERE dn.status = 'active'
    ";
    $activeCount = (int)$pdo->query($testQuery)->fetchColumn();

    // Test with customer filter
    $filteredCount = 0;
    if (isset($_GET['customerCode']) && $_GET['customerCode']) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM {$table} dn
            WHERE dn.status = 'active'
              AND (
                LOWER(TRIM(dn.customer_code)) = LOWER(TRIM(:customer_code))
                OR dn.customer_code IS NULL
                OR dn.customer_code = ''
              )
        ");
        $stmt->execute([':customer_code' => $_GET['customerCode']]);
        $filteredCount = (int)$stmt->fetchColumn();
    }

    echo json_encode([
        'success' => true,
        'diagnostic' => [
            'total_notifications' => count($all),
            'status_breakdown' => $statusCounts,
            'active_count_no_filter' => $activeCount,
            'active_count_with_customer_filter' => $filteredCount,
            'requested_customer_code' => $requestedCustomer,
            'customer_codes_in_active_notifications' => $customerCodes,
        ],
        'recent_notifications' => $all
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
