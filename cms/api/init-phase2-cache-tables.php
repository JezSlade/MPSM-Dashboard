<?php
/**
 * Initialize Phase 2 Cache Tables
 * Creates mpsm_cache_connectors and mpsm_cache_page_volume tables
 *
 * Run once to set up Phase 2 cache infrastructure
 */

require '../config.php';
require '../functions.php';

requireAuth();

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    echo "Phase 2 Cache Table Initialization\n";
    echo "=====================================\n\n";

    // Create connectors cache table
    echo "Creating {$prefix}cache_connectors table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$prefix}cache_connectors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_code VARCHAR(50) NOT NULL,
            total_win INT DEFAULT 0,
            total_embedded INT DEFAULT 0,
            last_day INT DEFAULT 0,
            sds_total_win INT DEFAULT 0,
            connector_data JSON,
            cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY idx_customer (customer_code),
            INDEX idx_cached (cached_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ {$prefix}cache_connectors created\n\n";

    // Create page volume cache table
    echo "Creating {$prefix}cache_page_volume table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$prefix}cache_page_volume (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_code VARCHAR(50) NOT NULL,
            monthly_mono_managed INT DEFAULT 0,
            monthly_color_managed INT DEFAULT 0,
            monthly_mono_unmanaged INT DEFAULT 0,
            monthly_color_unmanaged INT DEFAULT 0,
            pages_data JSON,
            cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY idx_customer (customer_code),
            INDEX idx_cached (cached_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ {$prefix}cache_page_volume created\n\n";

    // Verify tables exist
    echo "Verifying tables...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE '{$prefix}cache_connectors'");
    $connectorsExists = $stmt->rowCount() > 0;

    $stmt = $pdo->query("SHOW TABLES LIKE '{$prefix}cache_page_volume'");
    $volumeExists = $stmt->rowCount() > 0;

    if ($connectorsExists && $volumeExists) {
        echo "✓ All Phase 2 cache tables verified\n\n";
        echo "SUCCESS: Phase 2 cache infrastructure ready\n";
        echo "Next step: Run refresh-cache-enhanced.php to populate tables\n";
    } else {
        echo "✗ Table verification failed\n";
        if (!$connectorsExists) echo "  - {$prefix}cache_connectors missing\n";
        if (!$volumeExists) echo "  - {$prefix}cache_page_volume missing\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
