<?php
/**
 * Get Panel Callback Debug Logs API
 * Retrieves all callback attempts with full details for diagnostics
 */

require '../config.php';
require '../functions.php';

define('MPS_ENGINE_ACCESS', true);
require_once dirname(__DIR__, 2) . '/mps-api/callbacks/panel-message-common.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$limit = min(max($limit, 1), 500); // Between 1 and 500

$status = $_GET['status'] ?? null; // Filter by status: SUCCESS, ERROR, PROCESSING
$source = $_GET['source'] ?? null; // Filter by source (unique_source field)

try {
    $pdo = getDatabase();
    $table = DB_PREFIX . 'panel_callback_debug';
    ensurePanelCallbackDebugTable($pdo);

    $whereClauses = [];
    $params = [];

    if ($status) {
        $whereClauses[] = 'status = :status';
        $params[':status'] = $status;
    }

    if ($source) {
        $whereClauses[] = 'unique_source = :source';
        $params[':source'] = $source;
    }

    $where = count($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

    $sql = "SELECT
                id, timestamp, ip_address, http_method, content_type,
                user_agent, unique_source, forwarded_for, headers, raw_body,
                status, message, http_code, completed_at
            FROM {$table}
            {$where}
            ORDER BY timestamp DESC
            LIMIT {$limit}";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $logs = [];
    $sourceSummary = [];

    foreach ($rows as $row) {
        $decodedHeaders = $row['headers'] ? json_decode($row['headers'], true) : [];
        $forwardedFor = $row['forwarded_for'];

        if (!$forwardedFor && is_array($decodedHeaders)) {
            $forwardedFor = $decodedHeaders['X-FORWARDED-FOR'] ?? $decodedHeaders['X_FORWARDED_FOR'] ?? null;
        }

        $uniqueSource = $row['unique_source'];
        if (!$uniqueSource) {
            $sourceParts = [$row['ip_address']];
            if ($forwardedFor) {
                $sourceParts[] = "via {$forwardedFor}";
            }
            if (!empty($row['user_agent'])) {
                $sourceParts[] = $row['user_agent'];
            }
            $uniqueSource = implode(' | ', array_filter($sourceParts));
        }

        $sourceKey = strtolower($uniqueSource);
        if (!isset($sourceSummary[$sourceKey])) {
            $sourceSummary[$sourceKey] = [
                'unique_source' => $uniqueSource,
                'ip_address' => $row['ip_address'],
                'forwarded_for' => $forwardedFor,
                'user_agent' => $row['user_agent'],
                'count' => 0
            ];
        }
        $sourceSummary[$sourceKey]['count']++;

        $logs[] = [
            'id' => (int)$row['id'],
            'timestamp' => $row['timestamp'],
            'ip_address' => $row['ip_address'],
            'http_method' => $row['http_method'],
            'content_type' => $row['content_type'],
            'user_agent' => $row['user_agent'],
            'headers' => is_array($decodedHeaders) ? $decodedHeaders : null,
            'raw_body' => $row['raw_body'],
            'parsed_body' => $row['raw_body'] ? @json_decode($row['raw_body'], true) : null,
            'status' => $row['status'],
            'message' => $row['message'],
            'http_code' => $row['http_code'] ? (int)$row['http_code'] : null,
            'forwarded_for' => $forwardedFor,
            'unique_source' => $uniqueSource,
            'completed_at' => $row['completed_at']
        ];
    }

    // Get summary stats
    $statsSql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as error_count,
                    SUM(CASE WHEN status = 'PROCESSING' THEN 1 ELSE 0 END) as processing_count,
                    MAX(timestamp) as last_request,
                    MAX(COALESCE(completed_at, timestamp)) as last_completed
                 FROM {$table}";

    $statsStmt = $pdo->query($statsSql);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Sort sources by count desc
    usort($sourceSummary, static function ($a, $b) {
        return $b['count'] <=> $a['count'];
    });

    jsonSuccess([
        'logs' => $logs,
        'stats' => [
            'total' => (int)$stats['total'],
            'success_count' => (int)$stats['success_count'],
            'error_count' => (int)$stats['error_count'],
            'processing_count' => (int)$stats['processing_count'],
            'last_request' => $stats['last_request'],
            'last_completed' => $stats['last_completed']
        ],
        'sources' => $sourceSummary
    ]);

} catch (Exception $e) {
    jsonError('Failed to fetch debug logs: ' . $e->getMessage(), 500);
}
