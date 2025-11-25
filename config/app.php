<?php
/**
 * Application Configuration
 * Single source of truth for all configuration
 *
 * Following Engineering Standards Rule 4: Configuration Must Use Constants
 * This file replaces scattered configuration across cms/config.php and mps-api/config.php
 */

return [
    // Application Settings
    'app' => [
        'name' => 'MPS Monitor Dashboard',
        'version' => '3.0.0',
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => getenv('APP_DEBUG') === 'true',
        'timezone' => 'America/New_York',
    ],

    // Database Configuration
    'database' => [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: 3306,
        'database' => getenv('DB_NAME') ?: 'resolut7_mpsm',
        'username' => getenv('DB_USER') ?: 'resolut7_mpsm_agent',
        'password' => getenv('DB_PASS') ?: '!C@S@lcd6McFceb8',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => 'mpsm_',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],

    // MPS API Configuration (Vendor API)
    'mps_api' => [
        'base_url' => getenv('MPS_API_BASE') ?: 'https://api.abassetmanagement.com/api3/',
        'token_url' => getenv('MPS_API_TOKEN_URL') ?: 'https://api.abassetmanagement.com/api3/token',
        'client_id' => getenv('MPS_CLIENT_ID') ?: 'G0bYZyS9bjOjx6oRv-MQ6vGF3VkVTvZy5hzhVEOWQs8',
        'client_secret' => getenv('MPS_CLIENT_SECRET') ?: 'wFFXo9TQvvuCGVBb0_MMNZkZP5YuTPJqe_eRRdHCPQo',
        'username' => getenv('MPS_USERNAME') ?: 'rbd.connect@resolutionsbydesign.com',
        'password' => getenv('MPS_PASSWORD') ?: 'connect.RBD24!',
        'grant_type' => 'password',
        'scope' => getenv('MPS_SCOPE') ?: 'rbd.connect@resolutionsbydesign.com MpsMonitorApiAll',
        'timeout' => 30,
        'retries' => 3,
    ],

    // Cache Configuration
    'cache' => [
        'default' => getenv('CACHE_DRIVER') ?: 'database',
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
                'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
                'port' => getenv('REDIS_PORT') ?: 6379,
                'password' => getenv('REDIS_PASSWORD'),
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
        'key' => getenv('APP_KEY') ?: 'mpsm_dashboard_2025',
        'bcrypt_rounds' => 12,
        'password_min_length' => 8,
        'session_timeout' => 604800,
    ],

    // API Configuration
    'api' => [
        'version' => 'v1',
        'rate_limit' => [
            'enabled' => getenv('API_RATE_LIMIT') === 'true',
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
        'device_crud' => getenv('FEATURE_DEVICE_CRUD') !== 'false', // Default enabled
        'multi_tenancy' => getenv('FEATURE_MULTI_TENANCY') === 'true',
        'websockets' => getenv('FEATURE_WEBSOCKETS') === 'true',
        'job_queue' => getenv('FEATURE_JOB_QUEUE') === 'true',
    ],

    // Default Values (Dealer/Customer)
    'defaults' => [
        'dealer_code' => getenv('DEFAULT_DEALER_CODE') ?: 'NY06AGDWUQ',
        'dealer_id' => getenv('DEFAULT_DEALER_ID') ?: 'SZ13qRwU5GtFLj0i_CbEgQ2',
        'customer_code' => getenv('DEFAULT_CUSTOMER_CODE') ?: 'W9OPXL0YDK',
        'customer_id' => getenv('DEFAULT_CUSTOMER_ID') ?: '0xUi5WEYLzOCrZ8ILowOvA2',
        'customer_name' => getenv('DEFAULT_CUSTOMER_NAME') ?: 'CAPE FEAR VALLEY MED CTR.',
    ],

    // Logging Configuration
    'logging' => [
        'enabled' => true,
        'level' => getenv('LOG_LEVEL') ?: 'info', // debug, info, warning, error
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
