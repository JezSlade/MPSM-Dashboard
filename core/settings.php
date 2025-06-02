<?php
// core/settings.php
// v1.0.1 [Better null checks]

require_once __DIR__ . '/db.php';

/**
 * Get a setting from global_settings or return default.
 */
function get_setting(string $key, $default = null) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT `value` FROM global_settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : $default;
}

/**
 * Set or update a setting value.
 */
function set_setting(string $key, string $value): bool {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        INSERT INTO global_settings (`key`, `value`)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
    ");
    return $stmt->execute([$key, $value]);
}
