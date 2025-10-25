<?php
/**
 * Setup MySQL Cache Table
 * Run this once to create the mpsm_cache table
 */

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../classes/Database.php';

    $db = Database::getInstance();
    $tableName = $db->getPrefix() . 'cache';

    $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cache_key VARCHAR(255) NOT NULL UNIQUE,
        cache_value LONGTEXT NOT NULL,
        expires_at DATETIME NOT NULL,
        hit_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_cache_key (cache_key),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $db->execute($sql);

    echo json_encode([
        'success' => true,
        'message' => "Cache table '{$tableName}' created successfully",
        'table' => $tableName
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
