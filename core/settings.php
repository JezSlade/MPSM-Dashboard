<?php
// core/settings.php
// v1.0.0 [Global settings management]

require_once __DIR__ . '/db.php';

function get_setting(string $key, $default = null) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT `value` FROM global_settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : $default;
}

function set_setting(string $key, string $value): bool {
    $pdo = get_db();
    $stmt = $pdo->prepare("
      INSERT INTO global_settings (`key`,`value`)
      VALUES (?,?)
      ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
    ");
    return $stmt->execute([$key, $value]);
}
