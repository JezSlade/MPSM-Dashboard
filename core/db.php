<?php
// core/db.php
// v1.0.0 [PDO MySQL connection]

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/debug.php';

function get_db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        debug_log('DB connection error', ['msg' => $e->getMessage()], 'ERROR');
        if (ENVIRONMENT === 'development') {
            die('DB Error: ' . $e->getMessage());
        }
        die('Database connection failed');
    }
    return $pdo;
}
