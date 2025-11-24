<?php
/**
 * Direct database query to analyze panel callback debug errors
 */

require 'config.php';
require 'functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDatabase();
    $table = DB_PREFIX . 'panel_callback_debug';

    // Get total counts
    $statsQuery = "
        SELECT
            COUNT(*) as total_entries,
            SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as total_errors,
            SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as total_success,
            SUM(CASE WHEN status = 'PROCESSING' THEN 1 ELSE 0 END) as total_processing
        FROM {$table}
    ";

    $stats = $pdo->query($statsQuery)->fetch(PDO::FETCH_ASSOC);

    // Get error breakdown by message type
    $errorTypesQuery = "
        SELECT
            message,
            http_code,
            COUNT(*) as count,
            MAX(timestamp) as last_occurrence
        FROM {$table}
        WHERE status = 'ERROR'
        GROUP BY message, http_code
        ORDER BY count DESC
    ";

    $errorTypes = $pdo->query($errorTypesQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Get sample error payloads (first 5 distinct error messages)
    $sampleErrorsQuery = "
        SELECT
            id,
            timestamp,
            ip_address,
            unique_source,
            message,
            http_code,
            raw_body,
            content_type,
            http_method
        FROM {$table}
        WHERE status = 'ERROR'
        ORDER BY timestamp DESC
        LIMIT 10
    ";

    $sampleErrors = $pdo->query($sampleErrorsQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Identify test data patterns
    $testDataQuery = "
        SELECT
            id,
            timestamp,
            message,
            raw_body,
            unique_source
        FROM {$table}
        WHERE
            raw_body LIKE '%TEST%'
            OR raw_body LIKE '%SUCCESS_SERIAL%'
            OR raw_body LIKE '%test%'
            OR message LIKE '%test%'
        ORDER BY timestamp DESC
        LIMIT 20
    ";

    $testData = $pdo->query($testDataQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Get unique sources with error counts
    $sourcesQuery = "
        SELECT
            unique_source,
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = 'ERROR' THEN 1 ELSE 0 END) as error_count,
            SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success_count,
            MAX(timestamp) as last_seen
        FROM {$table}
        WHERE unique_source IS NOT NULL
        GROUP BY unique_source
        ORDER BY error_count DESC, total_requests DESC
    ";

    $sources = $pdo->query($sourcesQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Output comprehensive report
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_entries' => (int)$stats['total_entries'],
            'total_errors' => (int)$stats['total_errors'],
            'total_success' => (int)$stats['total_success'],
            'total_processing' => (int)$stats['total_processing'],
            'error_rate' => $stats['total_entries'] > 0
                ? round(($stats['total_errors'] / $stats['total_entries']) * 100, 2)
                : 0
        ],
        'error_types' => $errorTypes,
        'sample_errors' => $sampleErrors,
        'test_data_found' => $testData,
        'sources' => $sources,
        'cleanup_sql' => generateCleanupSQL($testData)
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

function generateCleanupSQL(array $testData): array {
    if (empty($testData)) {
        return ['message' => 'No test data found to clean up'];
    }

    $testIds = array_column($testData, 'id');
    $table = DB_PREFIX . 'panel_callback_debug';

    return [
        'description' => 'SQL to delete identified test data',
        'test_ids_found' => $testIds,
        'sql' => sprintf(
            "DELETE FROM %s WHERE id IN (%s);",
            $table,
            implode(', ', $testIds)
        ),
        'count' => count($testIds)
    ];
}
