<?php
declare(strict_types=1);
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Bootstrap environment
require_once __DIR__ . '/config/env.php';
Env::load(__DIR__ . '/.env');

// Ensure DB connection
$pdo = require __DIR__ . '/config/db.php';

// Ensure guest login before loading permissions
require_once __DIR__ . '/config/permissions.php';
ensureGuestSession($pdo);

// Load router and modules registry
$routes = require __DIR__ . '/config/router.php';
$moduleDefs = require __DIR__ . '/config/modules.php';

// Determine requested module
$moduleKey = $_GET['module'] ?? 'Dashboard';
if (!isset($routes[$moduleKey])) {
    header('Location: index.php?module=Dashboard');
    exit;
}
$controllerClass = $routes[$moduleKey];
$controller = new $controllerClass($pdo);

try {
    $controller->authorize();
    $controller->handle();
} catch (Core\Exceptions\NotAuthorizedException $e) {
    http_response_code(403);
    echo '<h1>403 Forbidden</h1><p>You do not have access to that module.</p>';
}
?>