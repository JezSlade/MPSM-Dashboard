<?php
// =============================================
// Debugging control. ALWAYS Keep THIS BLOCK AT THE TOP
// =============================================
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// =============================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/ErrorHandler.php';
require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/ApiClient.php';

ErrorHandler::register();

header('Content-Type: application/json');

try {
    $db = new Database();
    $apiClient = new ApiClient();
    
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $endpoint = str_replace('/api/', '', $path);
    
    switch ($endpoint) {
        case 'widgets':
            echo json_encode($db->query("SELECT * FROM widgets")->fetchAll());
            break;
            
        case 'devices':
            echo json_encode($apiClient->getDevices());
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}