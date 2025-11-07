<?php
/**
 * API Gateway v1
 * Modern REST API entry point
 *
 * This file routes all /api/v1/* requests to appropriate controllers
 * using the Router class and middleware pipeline.
 *
 * Features:
 * - Clean REST architecture
 * - Automatic routing
 * - Middleware support
 * - JSON responses
 * - Error handling
 */

// Bootstrap application
require_once __DIR__ . '/../../../bootstrap.php';

// Load router and controllers (autoloaded via bootstrap.php)
$router = new Router();

// ============================================================================
// MIDDLEWARE
// ============================================================================

// CORS - Allow cross-origin requests
$router->middleware([CorsMiddleware::class, 'handle']);

// ============================================================================
// PUBLIC ROUTES (No authentication required)
// ============================================================================

// Health check
$router->get('/api/v1/health', function() {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'status' => 'healthy',
        'version' => config('app.version'),
        'timestamp' => date('c'),
    ]);
});

// ============================================================================
// PROTECTED ROUTES (Authentication required)
// ============================================================================

// Device routes
$router->get('/api/v1/devices', function() {
    AuthMiddleware::handle();
    $controller = new DeviceController();
    $controller->index();
});

$router->get('/api/v1/devices/stats', function() {
    AuthMiddleware::handle();
    $controller = new DeviceController();
    $controller->stats();
});

$router->post('/api/v1/devices/search', function() {
    AuthMiddleware::handle();
    $controller = new DeviceController();
    $controller->search();
});

$router->get('/api/v1/devices/:serial', function($serial) {
    AuthMiddleware::handle();
    $controller = new DeviceController();
    $controller->show($serial);
});

$router->get('/api/v1/devices/:serial/drilldown', function($serial) {
    AuthMiddleware::handle();
    $controller = new DeviceController();
    $controller->drilldown($serial);
});

$router->post('/api/v1/devices/cache', function() {
    AuthMiddleware::handle();
    $controller = new DeviceController();
    $controller->cache();
});

// Panel message routes
$router->get('/api/v1/panel-messages', function() {
    AuthMiddleware::handle();
    $controller = new PanelMessageController();
    $controller->index();
});

$router->get('/api/v1/panel-messages/stats', function() {
    AuthMiddleware::handle();
    $controller = new PanelMessageController();
    $controller->stats();
});

$router->get('/api/v1/panel-messages/device/:serial', function($serial) {
    AuthMiddleware::handle();
    $controller = new PanelMessageController();
    $controller->device($serial);
});

$router->post('/api/v1/panel-messages', function() {
    // No auth required for webhook callbacks from MPS Monitor
    $controller = new PanelMessageController();
    $controller->store();
});

// ============================================================================
// DISPATCH
// ============================================================================

try {
    $router->dispatch();
} catch (Exception $e) {
    // Global error handler
    error_log("API Error: " . $e->getMessage());

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error_code' => 'INTERNAL_ERROR',
    ], JSON_PRETTY_PRINT);
}
