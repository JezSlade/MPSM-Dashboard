<?php
/**
 * Database Configuration
 *
 * MySQL database credentials for MPSM Dashboard
 * This file should be kept secure and not committed to public repositories
 */

return [
    'host' => 'localhost',
    'database' => 'resolut7_mpsm',
    'username' => 'resolut7_mpsm_agent',
    'password' => '!C@S@lcd6McFceb8',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => 'mpsm_',

    // Connection options
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
