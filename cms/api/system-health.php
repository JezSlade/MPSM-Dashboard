<?php
/**
 * System Health Check API
 * Returns comprehensive health status for database, cache, and authentication
 */

session_start();

// Require authentication
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/MySQLCache.php';

$health = [
    'timestamp' => date('c'),
    'database' => [
        'connected' => false,
        'type' => 'MySQL',
        'host' => DB_HOST,
        'name' => DB_NAME,
        'error' => null
    ],
    'cache' => [
        'enabled' => false,
        'type' => 'MySQL',
        'entries' => 0,
        'hitRate' => 0,
        'error' => null
    ],
    'auth' => [
        'configured' => true,
        'sessionActive' => isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true,
        'username' => $_SESSION['username'] ?? 'unknown'
    ],
    'uptime' => 0,
    'version' => '1.0.0',
    'environment' => 'production'
];

// Test database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Test query
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        $health['database']['connected'] = true;

        // Get database stats
        $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $health['database']['table_count'] = (int)$result['table_count'];
    }
} catch (PDOException $e) {
    $health['database']['error'] = $e->getMessage();
}

// Test cache
try {
    $cache = new MySQLCache($pdo);
    $health['cache']['enabled'] = true;

    // Get cache stats
    $stats = $cache->getStats();
    $health['cache']['entries'] = $stats['active_entries'];
    $health['cache']['hitRate'] = round($stats['hit_rate'], 1);
    $health['cache']['size_mb'] = round($stats['size_mb'], 2);
    $health['cache']['total_hits'] = $stats['total_hits'];
    $health['cache']['total_misses'] = $stats['total_misses'];

} catch (Exception $e) {
    $health['cache']['error'] = $e->getMessage();
}

// Calculate uptime (time since file was created)
if (file_exists(__FILE__)) {
    $health['uptime'] = time() - filemtime(__FILE__);
}

echo json_encode($health, JSON_PRETTY_PRINT);
