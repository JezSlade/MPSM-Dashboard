<?php
/**
 * DATABASE OPTIMIZATION
 * Adds indexes and optimizes tables for better performance
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once dirname(__DIR__) . '/functions.php';

if (php_sapi_name() !== 'cli') {
    requireAuth();
    header('Content-Type: text/plain; charset=utf-8');
}

echo "=== DATABASE OPTIMIZATION ===\n\n";

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    // Check if we're using SQLite or MySQL
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "Database driver: $driver\n\n";

    if ($driver === 'mysql') {
        echo "Optimizing MySQL database...\n\n";

        // Add indexes for better query performance
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_cached_at ON {$prefix}cache_devices (cached_at)",
            "CREATE INDEX IF NOT EXISTS idx_customer_code ON {$prefix}cache_devices (customer_code)",
            "CREATE INDEX IF NOT EXISTS idx_drilldown_cached ON {$prefix}cache_device_drilldown (cached_at)",
            "CREATE INDEX IF NOT EXISTS idx_drilldown_serial ON {$prefix}cache_device_drilldown (serial_number)",
        ];

        foreach ($indexes as $sql) {
            try {
                echo "Running: " . substr($sql, 0, 50) . "...\n";
                $pdo->exec($sql);
                echo "  ✓ Success\n";
            } catch (PDOException $e) {
                // Index might already exist
                if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                    echo "  ℹ Already exists\n";
                } else {
                    echo "  ✗ Error: " . $e->getMessage() . "\n";
                }
            }
        }

        echo "\nOptimizing tables...\n";
        $tables = [
            "{$prefix}cache_devices",
            "{$prefix}cache_device_drilldown",
            "{$prefix}panel_messages"
        ];

        foreach ($tables as $table) {
            try {
                echo "  Optimizing $table... ";
                $pdo->exec("OPTIMIZE TABLE $table");
                echo "✓\n";
            } catch (PDOException $e) {
                echo "✗ " . $e->getMessage() . "\n";
            }
        }

        echo "\nAnalyzing tables...\n";
        foreach ($tables as $table) {
            try {
                echo "  Analyzing $table... ";
                $pdo->exec("ANALYZE TABLE $table");
                echo "✓\n";
            } catch (PDOException $e) {
                echo "✗ " . $e->getMessage() . "\n";
            }
        }

        // Get table sizes
        echo "\nTable sizes:\n";
        $stmt = $pdo->query("
            SELECT
                table_name,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                table_rows
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
            AND table_name LIKE '{$prefix}cache%'
            ORDER BY (data_length + index_length) DESC
        ");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            printf("  %-40s %10s MB  %10s rows\n",
                $row['table_name'],
                $row['size_mb'],
                number_format($row['table_rows'])
            );
        }

    } else if ($driver === 'sqlite') {
        echo "Optimizing SQLite database...\n\n";

        echo "Running VACUUM... ";
        $pdo->exec("VACUUM");
        echo "✓\n";

        echo "Running ANALYZE... ";
        $pdo->exec("ANALYZE");
        echo "✓\n";

        // Check indexes
        echo "\nExisting indexes on cache_devices:\n";
        $stmt = $pdo->query("PRAGMA index_list('{$prefix}cache_devices')");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - " . $row['name'] . "\n";
        }
    }

    echo "\n✅ Database optimization complete\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
