<?php
// diagnose.php

declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

echo "<pre>";

// ─────────────────────────────────────────────
// Step 1: Confirm script is running
echo "✅ diagnose.php is executing.\n";

// ─────────────────────────────────────────────
// Step 2: Confirm PHP version
echo "PHP Version: " . phpversion() . "\n";

// ─────────────────────────────────────────────
// Step 3: Try to load .env and print values
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    echo "❌ .env file not found at $envPath\n";
} else {
    echo "✅ .env file found.\n";
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
    }

    echo "Loaded ENV Keys:\n";
    foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DEALER_CODE', 'ADMIN_USER'] as $key) {
        echo "  - $key: " . ($_ENV[$key] ?? '[MISSING]') . "\n";
    }
}

// ─────────────────────────────────────────────
// Step 4: Test writing to debug.log
$logPath = __DIR__ . '/storage/debug.log';
$testLog = "DIAGNOSTIC LOG ENTRY @ " . date('Y-m-d H:i:s');
@file_put_contents($logPath, "$testLog\n", FILE_APPEND);
if (file_exists($logPath)) {
    echo "✅ Wrote to debug log: $logPath\n";
} else {
    echo "❌ Could not write to debug log. Check folder permissions.\n";
}

// ─────────────────────────────────────────────
// Step 5: Check database connection
try {
    $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Connected to DB: {$_ENV['DB_NAME']}\n";
    echo "Found " . count($tables) . " tables: " . implode(', ', $tables) . "\n";
} catch (Exception $e) {
    echo "❌ DB ERROR: " . $e->getMessage() . "\n";
}

// ─────────────────────────────────────────────
// Step 6: Test session handling
session_start();
if (!isset($_SESSION['diag_test'])) {
    $_SESSION['diag_test'] = rand(1000, 9999);
    echo "🌀 Starting new session. Session ID: " . session_id() . "\n";
    echo "Session var [diag_test] set to: {$_SESSION['diag_test']}\n";
} else {
    echo "✅ Session restored. ID: " . session_id() . "\n";
    echo "Session var [diag_test]: {$_SESSION['diag_test']}\n";
}

// ─────────────────────────────────────────────
// Step 7: File permissions check
$permCheck = is_writable(__DIR__ . '/storage') ? '✅ Writable' : '❌ Not writable';
echo "Storage folder: $permCheck\n";

// ─────────────────────────────────────────────
// Step 8: Summary
echo "\n✅ Diagnostic completed.\n";
echo "Delete this file once you're done.\n";

echo "</pre>";
