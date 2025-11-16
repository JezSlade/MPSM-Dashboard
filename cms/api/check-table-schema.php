<?php
/**
 * Check Table Schema
 *
 * Shows the actual database table structure
 */

require '../config.php';
require '../functions.php';

header('Content-Type: text/plain');

echo "=== TABLE SCHEMA CHECK ===\n\n";

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    // Check main table
    echo "Table: {$prefix}cache_devices\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM {$prefix}cache_devices");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Columns:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }

    echo "\n" . str_repeat('-', 80) . "\n\n";

    // Check drilldown table
    echo "Table: {$prefix}cache_device_drilldown\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM {$prefix}cache_device_drilldown");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Columns:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }

    // Check for _staging tables
    echo "\n" . str_repeat('-', 80) . "\n\n";

    $stmt = $pdo->query("SHOW TABLES LIKE '{$prefix}cache_%_staging'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($tables) > 0) {
        echo "Staging tables found:\n";
        foreach ($tables as $table) {
            echo "  - {$table}\n";
        }
    } else {
        echo "No staging tables exist yet\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
