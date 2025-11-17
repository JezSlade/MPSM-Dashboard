<?php
declare(strict_types=1);

/**
 * Secure Panel Error Export Endpoint
 *
 * Provides a JSON snapshot of panel callback debugging data without requiring a CMS session.
 * Access is gated by the DEPLOY_SECRET so only trusted automation can fetch the data.
 *
 * Usage: /cms/api/panel-error-export.php?secret=YOUR_DEPLOY_SECRET
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';

header('Content-Type: application/json; charset=utf-8');

$expectedSecret = defined('DEPLOY_SECRET') ? DEPLOY_SECRET : getenv('DEPLOY_SECRET');
$expectedSecret = $expectedSecret ?: ('mpsm_deploy_' . md5((defined('SECURE_KEY') ? SECURE_KEY : 'mpsm_dashboard_2025') . 'deployment'));
$providedSecret = $_GET['secret'] ?? $_POST['secret'] ?? ($_SERVER['HTTP_X_DEPLOY_SECRET'] ?? null);

if (!$expectedSecret || !hash_equals((string)$expectedSecret, (string)$providedSecret)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Forbidden',
        'message' => 'Valid secret is required'
    ]);
    exit;
}

try {
    $pdo = getDatabase();
    $table = DB_PREFIX . 'panel_callback_debug';

    $stats = fetchSingleRow($pdo, "
        SELECT
            COUNT(*) AS total_entries,
            SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) AS total_errors,
            SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) AS total_success,
            SUM(CASE WHEN status = 'PROCESSING' THEN 1 ELSE 0 END) AS total_processing,
            MIN(timestamp) AS first_entry,
            MAX(timestamp) AS last_entry
        FROM {$table}
    ");

    $errorBreakdown = fetchAllRows($pdo, "
        SELECT
            message,
            http_code,
            COUNT(*) AS count,
            MIN(timestamp) AS first_seen,
            MAX(timestamp) AS last_seen,
            COUNT(DISTINCT unique_source) AS unique_sources,
            COUNT(DISTINCT ip_address) AS unique_ips
        FROM {$table}
        WHERE status = 'ERROR'
        GROUP BY message, http_code
        ORDER BY count DESC
    ");

    $sampleErrors = fetchAllRows($pdo, "
        SELECT
            id,
            timestamp,
            ip_address,
            unique_source,
            http_method,
            content_type,
            message,
            http_code,
            SUBSTRING(raw_body, 1, 500) AS body_preview,
            LENGTH(raw_body) AS body_length
        FROM {$table}
        WHERE status = 'ERROR'
        ORDER BY timestamp DESC
        LIMIT 25
    ");

    $testData = fetchAllRows($pdo, "
        SELECT
            id,
            timestamp,
            message,
            status,
            SUBSTRING(raw_body, 1, 200) AS body_preview,
            unique_source,
            ip_address
        FROM {$table}
        WHERE
            raw_body LIKE '%TEST%' OR
            raw_body LIKE '%test%' OR
            raw_body LIKE '%SUCCESS_SERIAL%' OR
            message LIKE '%test%' OR
            unique_source LIKE '%test%'
        ORDER BY timestamp DESC
        LIMIT 100
    ");

    $sourceAnalysis = fetchAllRows($pdo, "
        SELECT
            unique_source,
            ip_address,
            COUNT(*) AS total_requests,
            SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) AS success_count,
            SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) AS error_count,
            GROUP_CONCAT(DISTINCT message SEPARATOR ' | ') AS error_messages,
            MIN(timestamp) AS first_seen,
            MAX(timestamp) AS last_seen
        FROM {$table}
        GROUP BY unique_source, ip_address
        ORDER BY error_count DESC, total_requests DESC
        LIMIT 25
    ");

    $jsonErrors = fetchAllRows($pdo, "
        SELECT
            id,
            timestamp,
            message,
            SUBSTRING(raw_body, 1, 300) AS body_preview,
            unique_source,
            ip_address
        FROM {$table}
        WHERE
            status = 'ERROR'
            AND (
                message LIKE '%Invalid JSON%' OR
                message LIKE '%json%' OR
                message LIKE '%JSON%'
            )
        ORDER BY timestamp DESC
        LIMIT 25
    ");

    $invalidJsonLog = loadInvalidJsonLog(dirname(__DIR__, 2) . '/mps-api/logs/panel-message-invalid-json.log');

    $errorRate = ((int)$stats['total_entries'] ?? 0) > 0
        ? round(((int)$stats['total_errors'] / (int)$stats['total_entries']) * 100, 2)
        : 0.0;

    echo json_encode([
        'success' => true,
        'generated_at' => date('Y-m-d H:i:s'),
        'summary' => [
            'total_entries' => (int)($stats['total_entries'] ?? 0),
            'total_errors' => (int)($stats['total_errors'] ?? 0),
            'total_success' => (int)($stats['total_success'] ?? 0),
            'total_processing' => (int)($stats['total_processing'] ?? 0),
            'error_rate_percent' => $errorRate,
            'first_entry' => $stats['first_entry'] ?? null,
            'last_entry' => $stats['last_entry'] ?? null,
        ],
        'error_types' => $errorBreakdown,
        'sample_errors' => $sampleErrors,
        'json_specific_errors' => $jsonErrors,
        'test_data_detected' => [
            'count' => count($testData),
            'entries' => $testData
        ],
        'source_analysis' => $sourceAnalysis,
        'invalid_json_log_file' => $invalidJsonLog,
        'cleanup_recommendations' => buildCleanupRecommendation($table, $testData),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $exception->getMessage(),
        'trace' => $exception->getTraceAsString()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/**
 * Fetch a single row from the database.
 */
function fetchSingleRow(PDO $pdo, string $sql): array
{
    $stmt = $pdo->query($sql);
    return $stmt ? (array)$stmt->fetch(PDO::FETCH_ASSOC) : [];
}

/**
 * Fetch all rows for the supplied SQL query.
 */
function fetchAllRows(PDO $pdo, string $sql): array
{
    $stmt = $pdo->query($sql);
    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

/**
 * Build invalid JSON log metadata if the file exists.
 */
function loadInvalidJsonLog(string $logPath): array
{
    if (!file_exists($logPath)) {
        return ['file_exists' => false];
    }

    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $recent = array_slice($lines, -10);

    return [
        'file_exists' => true,
        'line_count' => count($lines),
        'file_size' => filesize($logPath),
        'last_modified' => date('Y-m-d H:i:s', filemtime($logPath)),
        'recent_entries' => array_map(static function ($line) {
            $decoded = json_decode($line, true);
            return $decoded ?: ['raw' => $line];
        }, $recent)
    ];
}

/**
 * Build cleanup SQL recommendation for test entries.
 */
function buildCleanupRecommendation(string $table, array $testData): array
{
    if (empty($testData)) {
        return [
            'description' => 'No test/junk entries detected',
            'test_entries_found' => 0,
            'sql' => null
        ];
    }

    $ids = array_column($testData, 'id');

    return [
        'description' => 'SQL to delete identified test/junk data',
        'test_entries_found' => count($ids),
        'sql' => sprintf(
            'DELETE FROM %s WHERE id IN (%s);',
            $table,
            implode(', ', array_map('intval', $ids))
        )
    ];
}

/*
CHANGELOG
2025-11-16 Codex
- Added a DEPLOY_SECRET-gated panel-error export endpoint so automation can read callback diagnostics without CMS sessions.
*/
