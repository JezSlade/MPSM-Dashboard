<?php
declare(strict_types=1);

// ────────────────────────────────────────────────────────────────────────────
// Autoloader: maps namespaces to folders (simple PSR-4 style)
spl_autoload_register(function($class) {
    // If the class uses the "Modules\" prefix
    $prefixes = [
        'Modules\\' => __DIR__ . '/modules/',
        'Core\\'    => __DIR__ . '/core/',
        'Models\\'  => __DIR__ . '/models/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strpos($class, $prefix) === 0) {
            // Remove the prefix to get the relative path inside its folder
            $relative = substr($class, strlen($prefix));
            // Convert namespace separators to directory separators, append .php
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

            if (file_exists($file)) {
                require $file;
            }
            return;
        }
    }
    // (If no matching prefix, do nothing—class might be in a different location)
});
// ────────────────────────────────────────────────────────────────────────────

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// ────────────────────────────────────────────────────────────────────────────
// Step A: Load environment and DB

require_once __DIR__ . '/config/env.php';
Env::load(__DIR__ . '/.env');

$pdo = require __DIR__ . '/config/db.php';

// ────────────────────────────────────────────────────────────────────────────
// Step B: Permissions + “guest” auto-login

require_once __DIR__ . '/config/permissions.php';
ensureGuestSession($pdo);

// ────────────────────────────────────────────────────────────────────────────
// Step C: Routing

//  1) Load the router map (moduleKey → Controller class)
$routes = require __DIR__ . '/config/router.php';

//  2) Decide which module was requested (default = "Dashboard")
$moduleKey = $_GET['module'] ?? 'Dashboard';

//  3) If not in the $routes map, redirect to default:
if (! isset($routes[$moduleKey]) ) {
    header('Location: index.php?module=Dashboard');
    exit;
}

//  4) Instantiate the controller
$controllerClass = $routes[$moduleKey];
$controller = new $controllerClass($pdo);

//  5) Authorization + Handle
try {
    $controller->authorize();
    $controller->handle();
} catch (Core\Exceptions\NotAuthorizedException $e) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>403 Forbidden</title></head><body>';
    echo '<h1>403 Forbidden</h1>';
    echo '<p>You do not have access to the <strong>' 
         . htmlspecialchars($moduleKey) 
         . '</strong> module.</p>';
    echo '</body></html>';
}
