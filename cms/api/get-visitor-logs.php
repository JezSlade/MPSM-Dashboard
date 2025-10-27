<?php
/**
 * Get Visitor Logs
 * Returns the last N visitor log entries with IP addresses and identifiers
 * Following Engineering Standards Rule 10: Keep Functions Short
 */

require '../config.php';
require '../functions.php';

requireAuth();

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

try {
    $pdo = getDatabase();

    $stmt = $pdo->prepare("
        SELECT
            id,
            user_id,
            username,
            ip_address,
            user_agent,
            page_url,
            visited_at
        FROM " . DB_PREFIX . "visitor_log
        ORDER BY visited_at DESC
        LIMIT ?
    ");

    $stmt->execute([$limit]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonSuccess([
        'count' => count($logs),
        'logs' => $logs
    ]);

} catch (Exception $e) {
    jsonError('Failed to retrieve visitor logs: ' . $e->getMessage());
}
