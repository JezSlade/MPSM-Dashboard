<?php
/**
 * Get Panel Callback Debug Logs API
 * Retrieves all callback attempts with full details for diagnostics
 */

require '../config.php';
require '../functions.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$limit = min(max($limit, 1), 500); // Between 1 and 500

$status = $_GET['status'] ?? null; // Filter by status: SUCCESS, ERROR, PROCESSING

try {
    $pdo = getDatabase();
    $table = DB_PREFIX . 'panel_callback_debug';

    // Ensure table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            timestamp DATETIME NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            http_method VARCHAR(10) NOT NULL,
            content_type VARCHAR(255) NULL,
            user_agent VARCHAR(500) NULL,
            headers JSON NULL,
            raw_body TEXT NULL,
            status VARCHAR(20) NOT NULL,
            message VARCHAR(500) NULL,
            http_code INT NULL,
            INDEX idx_timestamp (timestamp),
            INDEX idx_ip_address (ip_address),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $where = '';
    $params = [];

    if ($status) {
        $where = 'WHERE status = :status';
        $params[':status'] = $status;
    }

    $sql = "SELECT
                id, timestamp, ip_address, http_method, content_type,
                user_agent, headers, raw_body, status, message, http_code
            FROM {$table}
            {$where}
            ORDER BY timestamp DESC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);

    if ($status) {
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $logs = [];
    foreach ($rows as $row) {
        $logs[] = [
            'id' => (int)$row['id'],
            'timestamp' => $row['timestamp'],
            'ip_address' => $row['ip_address'],
            'http_method' => $row['http_method'],
            'content_type' => $row['content_type'],
            'user_agent' => $row['user_agent'],
            'headers' => $row['headers'] ? json_decode($row['headers'], true) : null,
            'raw_body' => $row['raw_body'],
            'parsed_body' => $row['raw_body'] ? @json_decode($row['raw_body'], true) : null,
            'status' => $row['status'],
            'message' => $row['message'],
            'http_code' => $row['http_code'] ? (int)$row['http_code'] : null
        ];
    }

    // Get summary stats
    $statsSql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as error_count,
                    SUM(CASE WHEN status = 'PROCESSING' THEN 1 ELSE 0 END) as processing_count,
                    MAX(timestamp) as last_request
                 FROM {$table}";

    $statsStmt = $pdo->query($statsSql);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    jsonSuccess([
        'logs' => $logs,
        'stats' => [
            'total' => (int)$stats['total'],
            'success_count' => (int)$stats['success_count'],
            'error_count' => (int)$stats['error_count'],
            'processing_count' => (int)$stats['processing_count'],
            'last_request' => $stats['last_request']
        ]
    ]);

} catch (Exception $e) {
    jsonError('Failed to fetch debug logs: ' . $e->getMessage(), 500);
}
