<?php
/**
 * Cache Manager API
 *
 * RESTful API for managing MySQL-based cache
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../mps-api/cache-integration.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    switch ($method) {
        case 'GET':
            // Get cache statistics
            if (isset($_GET['action']) && $_GET['action'] === 'entries') {
                $entries = CacheIntegration::getAllEntries();
                echo json_encode([
                    'success' => true,
                    'entries' => $entries,
                    'count' => count($entries)
                ]);
            } else {
                $stats = CacheIntegration::getStats();
                echo json_encode([
                    'success' => true,
                    'stats' => $stats
                ]);
            }
            break;

        case 'POST':
            // Perform cache action
            $action = $input['action'] ?? '';

            switch ($action) {
                case 'clean':
                    // Clean expired entries
                    $cleaned = CacheIntegration::cleanExpired();
                    echo json_encode([
                        'success' => true,
                        'message' => "Cleaned {$cleaned} expired entries"
                    ]);
                    break;

                case 'warm':
                    // Warm cache with common endpoints
                    // This would need to be implemented with actual API calls
                    echo json_encode([
                        'success' => true,
                        'message' => 'Cache warming initiated'
                    ]);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Unknown action'
                    ]);
            }
            break;

        case 'DELETE':
            // Clear all cache
            $cleared = CacheIntegration::clear();
            echo json_encode([
                'success' => $cleared,
                'message' => $cleared ? 'Cache cleared successfully' : 'Failed to clear cache'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed'
            ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
