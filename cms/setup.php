<?php
/**
 * MPSM Dashboard - Database Setup
 * Run this once to create tables and default admin user
 * Following Engineering Standards Rule 6: Always Show Errors
 */

require 'config.php';
require 'functions.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<h1>MPSM Dashboard - Database Setup</h1>";
echo "<pre>";

try {
    echo "Connecting to database...\n";
    $pdo = getDatabase();
    echo "✓ Database connection successful\n\n";

    echo "Creating tables...\n";
    initializeTables();
    echo "✓ Tables created successfully\n\n";

    echo "Checking for admin user...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM " . DB_PREFIX . "users");
    $count = $stmt->fetchColumn();
    echo "✓ Found $count user(s)\n\n";

    if ($count == 0) {
        echo "ERROR: No users found! This shouldn't happen.\n";
    } else {
        $stmt = $pdo->query("SELECT id, username FROM " . DB_PREFIX . "users LIMIT 5");
        $users = $stmt->fetchAll();
        echo "Users in database:\n";
        foreach ($users as $user) {
            echo "  - ID {$user['id']}: {$user['username']}\n";
        }
    }

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✓ SETUP COMPLETE!\n";
    echo str_repeat("=", 60) . "\n\n";
    echo "Default Login:\n";
    echo "  Username: admin\n";
    echo "  Password: admin\n\n";
    echo "Next Steps:\n";
    echo "  1. Visit: https://mpsm.resolutionsbydesign.us/cms/\n";
    echo "  2. Login with admin/admin\n";
    echo "  3. DELETE this setup.php file for security\n\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
    exit(1);
}

echo "</pre>";
