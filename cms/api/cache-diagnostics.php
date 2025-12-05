<?php
/**
 * Cache diagnostics: report existence and row counts for live/staging cache tables.
 * Auth bypass via secret=DEALER_API_2025; otherwise require session auth.
 */
require '../config.php';
require '../functions.php';

$secret = $_GET['secret'] ?? '';
$bypassSecret = 'DEALER_API_2025';

if ($secret !== $bypassSecret) {
    requireAuth();
}

header('Content-Type: application/json');

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    $tables = [
        "{$prefix}cache_devices",
        "{$prefix}cache_devices_staging",
        "{$prefix}cache_device_drilldown",
        "{$prefix}cache_device_drilldown_staging",
    ];

    $results = [];
    foreach ($tables as $table) {
        $exists = tableExists($pdo, $table);
        $count = $exists ? (int)$pdo->query("SELECT COUNT(*) AS c FROM {$table}")->fetch(PDO::FETCH_ASSOC)['c'] : null;
        $results[$table] = [
            'exists' => $exists,
            'count' => $count,
        ];
    }

    echo json_encode([
        'success' => true,
        'tables' => $results,
        'timestamp' => date('c'),
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
    return $stmt && $stmt->rowCount() > 0;
}
