<?php
/**
 * Application Configuration
 * Single source of truth for all configuration
 *
 * Following Engineering Standards Rule 4: Configuration Must Use Constants
 * This file replaces scattered configuration across cms/config.php and mps-api/config.php
 */

require_once __DIR__ . '/env.php';
mpsm_load_env(dirname(__DIR__) . '/.env');

return [
    // Application Settings
    'app' => [
        'name' => mpsm_env('APP_NAME', 'MPS Monitor Dashboard'),
        'version' => mpsm_env('APP_VERSION', '3.0.0'),
        'env' => mpsm_env('APP_ENV', 'production'),
        'debug' => mpsm_env('APP_DEBUG') === 'true',
        'timezone' => mpsm_env('TIMEZONE', 'America/New_York'),
    ],

    // Database Configuration
    'database' => [
        'driver' => 'mysql',
        'host' => mpsm_env('DB_HOST', 'localhost'),
        'port' => (int) mpsm_env('DB_PORT', 3306),
        'database' => mpsm_env('DB_NAME', ''),
        'username' => mpsm_env('DB_USER', ''),
        'password' => mpsm_env('DB_PASS', ''),
        'charset' => mpsm_env('DB_CHARSET', 'utf8mb4'),
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => mpsm_env('DB_PREFIX', 'mpsm_'),
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],

    // MPS API Configuration (Vendor API)
    'mps_api' => [
        'base_url' => mpsm_env(['MPS_API_BASE', 'MPS_BASE_URL', 'API_BASE_URL'], 'https://api.abassetmanagement.com/api3/'),
        'token_url' => mpsm_env(['MPS_API_TOKEN_URL', 'MPS_TOKEN_URL', 'TOKEN_URL'], 'https://api.abassetmanagement.com/api3/token'),
        'client_id' => mpsm_env(['MPS_CLIENT_ID', 'CLIENT_ID'], ''),
        'client_secret' => mpsm_env(['MPS_CLIENT_SECRET', 'CLIENT_SECRET'], ''),
        'username' => mpsm_env(['MPS_USERNAME', 'USERNAME'], ''),
        'password' => mpsm_env(['MPS_PASSWORD', 'PASSWORD'], ''),
        'grant_type' => 'password',
        'scope' => mpsm_env(['MPS_SCOPE', 'SCOPE'], ''),
        'timeout' => 30,
        'retries' => 3,
    ],

    // Cache Configuration
    'cache' => [
        'default' => mpsm_env('CACHE_DRIVER', 'database'),
        'ttl' => [
            'default' => 3600,        // 1 hour
            'devices_list' => 300,    // 5 minutes
            'device_item' => 3600,    // 1 hour
            'drilldown' => 3600,      // 1 hour
            'panel_messages' => 600,  // 10 minutes
            'system_health' => 60,    // 1 minute
        ],
        'drivers' => [
            'database' => [
                'table' => 'mpsm_cache',
            ],
            'file' => [
                'path' => __DIR__ . '/../storage/cache',
            ],
            'redis' => [
                'host' => mpsm_env('REDIS_HOST', '127.0.0.1'),
                'port' => (int) mpsm_env('REDIS_PORT', 6379),
                'password' => mpsm_env('REDIS_PASSWORD'),
                'database' => 0,
            ],
        ],
    ],

    // Session Configuration
    'session' => [
        'name' => 'MPSM_SESSION',
        'lifetime' => 604800, // 7 days for persistent logins
        'secure' => true,   // HTTPS only
        'httponly' => true, // Prevent JavaScript access
        'samesite' => 'Lax', // Cross-browser compatibility
        'strict_mode' => true,
    ],

    // Security Configuration
    'security' => [
        'key' => mpsm_env('APP_KEY', 'change-me'),
        'bcrypt_rounds' => 12,
        'password_min_length' => 8,
        'session_timeout' => 604800,
    ],

    // API Configuration
    'api' => [
        'version' => 'v1',
        'rate_limit' => [
            'enabled' => mpsm_env('API_RATE_LIMIT') === 'true',
            'requests_per_minute' => 60,
        ],
        'cors' => [
            'allowed_origins' => ['*'], // In production, specify exact origins
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        ],
    ],

    // Feature Flags
    'features' => [
        'device_crud' => mpsm_env('FEATURE_DEVICE_CRUD') !== 'false', // Default enabled
        'multi_tenancy' => mpsm_env('FEATURE_MULTI_TENANCY') === 'true',
        'websockets' => mpsm_env('FEATURE_WEBSOCKETS') === 'true',
        'job_queue' => mpsm_env('FEATURE_JOB_QUEUE') === 'true',
    ],

    // Default Values (Dealer/Customer)
    'defaults' => [
        'dealer_code' => mpsm_env(['DEFAULT_DEALER_CODE', 'DEALER_CODE'], ''),
        'dealer_id' => mpsm_env(['DEFAULT_DEALER_ID', 'DEALER_ID'], ''),
        'customer_code' => mpsm_env('DEFAULT_CUSTOMER_CODE', ''),
        'customer_id' => mpsm_env('DEFAULT_CUSTOMER_ID', ''),
        'customer_name' => mpsm_env('DEFAULT_CUSTOMER_NAME', ''),
    ],

    // Logging Configuration
    'logging' => [
        'enabled' => true,
        'level' => mpsm_env('LOG_LEVEL', 'info'), // debug, info, warning, error
        'path' => __DIR__ . '/../storage/logs',
        'channels' => [
            'application' => 'app.log',
            'api' => 'api.log',
            'cache' => 'cache-refresh.log',
            'panel' => 'panel-message.log',
            'security' => 'security.log',
            'audit' => 'audit.log',
        ],
    ],

    // Queue Configuration
    'queue' => [
        'default' => 'database',
        'connections' => [
            'database' => [
                'driver' => 'database',
                'table' => 'mpsm_jobs',
                'queue' => 'default',
                'retry_after' => 90,
            ],
        ],
        'failed' => [
            'table' => 'mpsm_failed_jobs',
        ],
    ],

    // Paths
    'paths' => [
        'root' => dirname(__DIR__),
        'app' => dirname(__DIR__) . '/src',
        'config' => __DIR__,
        'storage' => dirname(__DIR__) . '/storage',
        'cache' => dirname(__DIR__) . '/storage/cache',
        'logs' => dirname(__DIR__) . '/storage/logs',
        'public' => dirname(__DIR__) . '/cms',
    ],
];

/*
CHANGELOG
2025-11-25 Codex
- Extended session lifetime to 7 days (config + security timeout) to improve login persistence across mobile/desktop sessions.
*/
