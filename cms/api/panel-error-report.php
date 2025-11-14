<?php
/**
 * Panel Callback Error Report
 * Comprehensive analysis of all error types in the debug log
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';

requireAuth();

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDatabase();
    $table = DB_PREFIX . 'panel_callback_debug';

    // Overall statistics
    $statsQuery = "
        SELECT
            COUNT(*) as total_entries,
            SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as total_errors,
            SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as total_success,
            SUM(CASE WHEN status = 'PROCESSING' THEN 1 ELSE 0 END) as total_processing,
            MIN(timestamp) as first_entry,
            MAX(timestamp) as last_entry
        FROM {$table}
    ";
    $stats = $pdo->query($statsQuery)->fetch(PDO::FETCH_ASSOC);

    // Error breakdown by message and HTTP code
    $errorBreakdownQuery = "
        SELECT
            message,
            http_code,
            COUNT(*) as count,
            MIN(timestamp) as first_seen,
            MAX(timestamp) as last_seen,
            COUNT(DISTINCT unique_source) as unique_sources,
            COUNT(DISTINCT ip_address) as unique_ips
        FROM {$table}
        WHERE status = 'ERROR'
        GROUP BY message, http_code
        ORDER BY count DESC
    ";
    $errorBreakdown = $pdo->query($errorBreakdownQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Get sample errors for each error type (top 3-5 examples)
    $sampleErrorsQuery = "
        SELECT
            id,
            timestamp,
            ip_address,
            unique_source,
            http_method,
            content_type,
            message,
            http_code,
            SUBSTRING(raw_body, 1, 500) as body_preview,
            LENGTH(raw_body) as body_length
        FROM {$table}
        WHERE status = 'ERROR'
        ORDER BY timestamp DESC
        LIMIT 15
    ";
    $sampleErrors = $pdo->query($sampleErrorsQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Identify test/junk data patterns
    $testDataQuery = "
        SELECT
            id,
            timestamp,
            message,
            status,
            SUBSTRING(raw_body, 1, 200) as body_preview,
            unique_source,
            ip_address
        FROM {$table}
        WHERE
            raw_body LIKE '%TEST%'
            OR raw_body LIKE '%test%'
            OR raw_body LIKE '%SUCCESS_SERIAL%'
            OR message LIKE '%test%'
            OR unique_source LIKE '%test%'
        ORDER BY timestamp DESC
        LIMIT 50
    ";
    $testData = $pdo->query($testDataQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Source analysis (who's sending what)
    $sourceAnalysisQuery = "
        SELECT
            unique_source,
            ip_address,
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success_count,
            SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as error_count,
            GROUP_CONCAT(DISTINCT message SEPARATOR ' | ') as error_messages,
            MIN(timestamp) as first_seen,
            MAX(timestamp) as last_seen
        FROM {$table}
        GROUP BY unique_source, ip_address
        ORDER BY error_count DESC, total_requests DESC
        LIMIT 20
    ";
    $sourceAnalysis = $pdo->query($sourceAnalysisQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Get JSON parsing errors specifically
    $jsonErrorsQuery = "
        SELECT
            id,
            timestamp,
            message,
            SUBSTRING(raw_body, 1, 300) as body_preview,
            unique_source,
            ip_address
        FROM {$table}
        WHERE
            status = 'ERROR'
            AND (
                message LIKE '%Invalid JSON%'
                OR message LIKE '%json%'
                OR message LIKE '%JSON%'
            )
        ORDER BY timestamp DESC
        LIMIT 20
    ";
    $jsonErrors = $pdo->query($jsonErrorsQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Check for invalid JSON log file
    $logPath = dirname(__DIR__, 2) . '/mps-api/logs/panel-message-invalid-json.log';
    $invalidJsonLog = null;
    if (file_exists($logPath)) {
        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $invalidJsonLog = [
            'file_exists' => true,
            'line_count' => count($lines),
            'file_size' => filesize($logPath),
            'last_modified' => date('Y-m-d H:i:s', filemtime($logPath)),
            'recent_entries' => array_slice(array_map('json_decode', array_slice($lines, -10)), 0, 10)
        ];
    } else {
        $invalidJsonLog = ['file_exists' => false];
    }

    // Generate cleanup SQL
    $cleanupSQL = [];
    if (!empty($testData)) {
        $testIds = array_column($testData, 'id');
        $cleanupSQL = [
            'description' => 'SQL to delete identified test/junk data',
            'test_entries_found' => count($testIds),
            'sql' => sprintf(
                "DELETE FROM %s WHERE id IN (%s);",
                $table,
                implode(', ', $testIds)
            )
        ];
    }

    // Calculate error rate
    $errorRate = $stats['total_entries'] > 0
        ? round(($stats['total_errors'] / $stats['total_entries']) * 100, 2)
        : 0;

    // Response
    $response = [
        'success' => true,
        'generated_at' => date('Y-m-d H:i:s'),
        'summary' => [
            'total_entries' => (int)$stats['total_entries'],
            'total_errors' => (int)$stats['total_errors'],
            'total_success' => (int)$stats['total_success'],
            'total_processing' => (int)$stats['total_processing'],
            'error_rate_percent' => $errorRate,
            'first_entry' => $stats['first_entry'],
            'last_entry' => $stats['last_entry']
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
        'cleanup_recommendations' => $cleanupSQL,
        'analysis' => analyzeErrors($errorBreakdown, $sampleErrors, $testData)
    ];

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}

function analyzeErrors(array $errorBreakdown, array $sampleErrors, array $testData): array {
    $analysis = [
        'most_common_error' => null,
        'error_categories' => [],
        'recommendations' => []
    ];

    // Find most common error
    if (!empty($errorBreakdown)) {
        $analysis['most_common_error'] = [
            'message' => $errorBreakdown[0]['message'],
            'count' => $errorBreakdown[0]['count'],
            'http_code' => $errorBreakdown[0]['http_code']
        ];
    }

    // Categorize errors
    foreach ($errorBreakdown as $error) {
        $message = strtolower($error['message']);

        if (strpos($message, 'json') !== false) {
            $analysis['error_categories']['json_parsing'][] = $error;
        } elseif (strpos($message, 'unauthorized') !== false || strpos($message, 'secret') !== false) {
            $analysis['error_categories']['authentication'][] = $error;
        } elseif (strpos($message, 'method') !== false) {
            $analysis['error_categories']['http_method'][] = $error;
        } elseif (strpos($message, 'content-type') !== false) {
            $analysis['error_categories']['content_type'][] = $error;
        } elseif (strpos($message, 'empty') !== false) {
            $analysis['error_categories']['empty_payload'][] = $error;
        } elseif (strpos($message, 'database') !== false) {
            $analysis['error_categories']['database'][] = $error;
        } else {
            $analysis['error_categories']['other'][] = $error;
        }
    }

    // Generate recommendations
    if (!empty($analysis['error_categories']['json_parsing'])) {
        $analysis['recommendations'][] = 'JSON parsing errors detected. Check payload sanitizer and encoding issues.';
    }

    if (!empty($analysis['error_categories']['authentication'])) {
        $analysis['recommendations'][] = 'Authentication errors detected. Verify callback secret configuration.';
    }

    if (!empty($testData)) {
        $analysis['recommendations'][] = sprintf(
            '%d test/junk entries found. Consider cleaning up test data.',
            count($testData)
        );
    }

    if (!empty($analysis['error_categories']['empty_payload'])) {
        $analysis['recommendations'][] = 'Empty payload errors detected. Check sender configuration.';
    }

    return $analysis;
}
