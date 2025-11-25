<?php
/**
 * Get Panel Message Alert Count for Customer
 * Returns count of recent panel messages filtered by customer code
 */

require '../config.php';
require '../functions.php';

requireAuth();

$customerCode = $_GET['customerCode'] ?? '';
$hours = isset($_GET['hours']) ? (int)$_GET['hours'] : 24; // Default: last 24 hours
$hours = max(1, min($hours, 168)); // Clamp between 1 hour and 7 days

if (empty($customerCode)) {
    jsonError('customerCode required', 400);
}

try {
    $pdo = getDatabase();
    $table = DB_PREFIX . 'panel_messages';

    // Calculate cutoff timestamp in PHP (MySQL doesn't support binding inside INTERVAL)
    $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

    $sql = "SELECT COUNT(*) as count
            FROM {$table}
            WHERE customer_code = :customer_code
            AND received_at >= :cutoff";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':customer_code', $customerCode, PDO::PARAM_STR);
    $stmt->bindValue(':cutoff', $cutoff, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = (int)($row['count'] ?? 0);

    jsonSuccess([
        'count' => $count,
        'customer_code' => $customerCode,
        'hours' => $hours
    ]);
} catch (Exception $e) {
    jsonError($e->getMessage());
}

/*
CHANGELOG
2025-11-25 Codex
- Ensured alert count API remains customer-filtered and added changelog for auditability.
*/
