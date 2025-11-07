<?php
/**
 * Application Bootstrap
 * Initializes the application and registers services in the container
 *
 * This file is the single entry point for service configuration.
 * It loads configuration, registers services, and sets up the environment.
 */

// Load core files
require_once __DIR__ . '/src/ServiceContainer.php';

// Set timezone
date_default_timezone_set(config('app.timezone', 'America/New_York'));

// Error reporting (based on environment)
if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

// Get service container instance
$container = ServiceContainer::getInstance();

// ============================================================================
// REGISTER CORE SERVICES
// ============================================================================

/**
 * Database (PDO)
 * Singleton connection to MySQL database
 */
$container->register(PDO::class, function() {
    $config = config('database');

    $dsn = sprintf(
        '%s:host=%s;port=%d;dbname=%s;charset=%s',
        $config['driver'],
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );

    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        $config['options']
    );

    return $pdo;
}, true);

/**
 * Cache Interface
 * Default cache driver (database)
 */
$container->register(CacheInterface::class, function($container) {
    $driver = config('cache.default', 'database');

    switch ($driver) {
        case 'database':
            require_once __DIR__ . '/src/Cache/DatabaseCache.php';
            return new DatabaseCache($container->get(PDO::class));

        case 'file':
            require_once __DIR__ . '/src/Cache/FileCache.php';
            return new FileCache(config('cache.drivers.file.path'));

        case 'redis':
            require_once __DIR__ . '/src/Cache/RedisCache.php';
            return new RedisCache(config('cache.drivers.redis'));

        default:
            throw new RuntimeException("Unknown cache driver: {$driver}");
    }
}, true);

/**
 * Engine Interface
 * MPS API integration (vendor API proxy)
 */
$container->register(EngineInterface::class, function($container) {
    require_once __DIR__ . '/src/Engine/MPSEngine.php';
    return new MPSEngine(
        config('mps_api'),
        $container->get(CacheInterface::class)
    );
}, true);

// ============================================================================
// REGISTER REPOSITORIES
// ============================================================================

/**
 * Device Repository
 * Handles device data access and caching
 */
$container->register(DeviceRepository::class, function($container) {
    return new DeviceRepository(
        $container->get(PDO::class),
        $container->get(CacheInterface::class)
    );
}, true);

/**
 * Panel Message Repository
 * Handles panel message webhook data
 */
$container->register(PanelMessageRepository::class, function($container) {
    return new PanelMessageRepository(
        $container->get(PDO::class),
        $container->get(CacheInterface::class)
    );
}, true);

/**
 * User Repository
 * Handles user authentication and preferences
 */
$container->register(UserRepository::class, function($container) {
    return new UserRepository(
        $container->get(PDO::class),
        $container->get(CacheInterface::class)
    );
}, true);

// ============================================================================
// LOAD BACKWARDS COMPATIBILITY LAYER
// ============================================================================

/**
 * Load legacy cms/config.php constants
 * This maintains backwards compatibility with existing code
 * that uses constants like DB_HOST, DB_NAME, etc.
 */
if (!defined('DB_HOST')) {
    define('DB_HOST', config('database.host'));
    define('DB_NAME', config('database.database'));
    define('DB_USER', config('database.username'));
    define('DB_PASS', config('database.password'));
    define('DB_CHARSET', config('database.charset'));
    define('DB_PREFIX', config('database.prefix'));

    define('MPS_API_BASE', config('mps_api.base_url'));
    define('MPS_API_TOKEN_URL', config('mps_api.token_url'));
    define('MPS_CLIENT_ID', config('mps_api.client_id'));
    define('MPS_CLIENT_SECRET', config('mps_api.client_secret'));
    define('MPS_USERNAME', config('mps_api.username'));
    define('MPS_PASSWORD', config('mps_api.password'));
    define('MPS_GRANT_TYPE', config('mps_api.grant_type'));
    define('MPS_SCOPE', config('mps_api.scope'));

    define('DEFAULT_DEALER_CODE', config('defaults.dealer_code'));
    define('DEFAULT_DEALER_ID', config('defaults.dealer_id'));
    define('DEFAULT_CUSTOMER_CODE', config('defaults.customer_code'));
    define('DEFAULT_CUSTOMER_ID', config('defaults.customer_id'));
    define('DEFAULT_CUSTOMER_NAME', config('defaults.customer_name'));

    define('APP_NAME', config('app.name'));
    define('APP_VERSION', config('app.version'));
    define('SESSION_TIMEOUT', config('session.lifetime'));
    define('FEATURE_DEVICE_CRUD', feature('device_crud'));

    define('SECURE_KEY', config('security.key'));
}

// ============================================================================
// SESSION CONFIGURATION
// ============================================================================

/**
 * Configure session settings
 * Only configure if session hasn't started yet
 */
if (session_status() === PHP_SESSION_NONE) {
    $sessionConfig = config('session');

    // Detect HTTPS
    $isHTTPS = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || $_SERVER['SERVER_PORT'] == 443
               || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => $sessionConfig['lifetime'],
        'path' => '/',
        'domain' => '',
        'secure' => $isHTTPS,
        'httponly' => $sessionConfig['httponly'],
        'samesite' => $sessionConfig['samesite'],
    ]);

    session_name($sessionConfig['name']);

    ini_set('session.use_strict_mode', $sessionConfig['strict_mode'] ? '1' : '0');
    ini_set('session.cookie_httponly', $sessionConfig['httponly'] ? '1' : '0');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', $sessionConfig['samesite']);
}

// ============================================================================
// AUTOLOADER (Simple PSR-4 style)
// ============================================================================

spl_autoload_register(function ($class) {
    // Convert namespace to file path
    // Example: App\Repositories\DeviceRepository -> src/Repositories/DeviceRepository.php

    $prefix = '';
    $baseDir = __DIR__ . '/src/';

    // Remove namespace prefix if present
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $class = substr($class, $len);
    }

    // Convert class name to file path
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';

    // If file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// ============================================================================
// ENVIRONMENT SETUP COMPLETE
// ============================================================================

// Application is now bootstrapped and ready to use
// Services can be accessed via app(ClassName::class)
// Configuration can be accessed via config('key.subkey')
