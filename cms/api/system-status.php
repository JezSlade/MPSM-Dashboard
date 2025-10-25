<?php
/**
 * System Status API
 * Diagnostic endpoint to check engine, database, and cache status
 */

header('Content-Type: application/json');

$status = [
    'timestamp' => date('c'),
    'engine' => ['status' => 'unknown', 'error' => null],
    'database' => ['status' => 'unknown', 'error' => null],
    'cache' => ['status' => 'unknown', 'error' => null]
];

// Check MPS Engine
try {
    require_once __DIR__ . '/../../mps-api/index.php';
    $engine = MPSMonitorEngine::getInstance();
    $engineStatus = $engine->getStatus();
    $status['engine'] = [
        'status' => 'connected',
        'auth_mode' => $engineStatus['auth_mode'] ?? 'unknown',
        'has_token' => $engineStatus['has_access_token'] ?? false
    ];
} catch (Exception $e) {
    $status['engine'] = [
        'status' => 'error',
        'error' => $e->getMessage()
    ];
}

// Check Database
try {
    if (file_exists(__DIR__ . '/../classes/Database.php')) {
        require_once __DIR__ . '/../classes/Database.php';
        $db = Database::getInstance();
        $db->query('SELECT 1');
        $status['database'] = ['status' => 'connected'];
    } else {
        $status['database'] = ['status' => 'error', 'error' => 'Database.php not found'];
    }
} catch (Exception $e) {
    $status['database'] = [
        'status' => 'error',
        'error' => $e->getMessage()
    ];
}

// Check Cache
try {
    if (file_exists(__DIR__ . '/../../mps-api/cache-integration.php')) {
        require_once __DIR__ . '/../../mps-api/cache-integration.php';
        $cacheStatus = CacheIntegration::getStats();
        $status['cache'] = [
            'status' => 'enabled',
            'stats' => $cacheStatus
        ];
    } else {
        $status['cache'] = ['status' => 'disabled', 'error' => 'cache-integration.php not found'];
    }
} catch (Exception $e) {
    $status['cache'] = [
        'status' => 'error',
        'error' => $e->getMessage()
    ];
}

echo json_encode($status, JSON_PRETTY_PRINT);
