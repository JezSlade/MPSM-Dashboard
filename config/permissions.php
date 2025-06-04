<?php
/**
 * config/permissions.php
 *
 * Dynamically enforces permissions via DB tables:
 *  - roles
 *  - modules
 *  - role_module
 *  - users
 *
 * Also defines APP_VERSION and helper functions:
 *  - current_user()
 *  - user_has_permission($moduleName)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application version, bumped to v0.1.5
define('APP_VERSION', 'v0.1.5');

/**
 * Retrieve the currently‐logged‐in user record (or null if none).
 */
function current_user(): ?array {
    if (! isset($_SESSION['user_id'])) {
        return null;
    }
    static $cachedUser = null;
    if ($cachedUser !== null) {
        return $cachedUser;
    }
    // Always use require so $pdo is returned
    $pdo = require __DIR__ . '/db.php';

    $stmt = $pdo->prepare(
        "SELECT u.id, u.username, u.role_id, r.name AS role_name
         FROM users u
         JOIN roles r ON u.role_id = r.id
         WHERE u.id = ?"
    );
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $cachedUser = $user ?: null;
}

/**
 * Check if the currently logged-in user has permission for $moduleName.
 */
function user_has_permission(string $moduleName): bool {
    $user = current_user();
    if (! $user) {
        return false;
    }
    $pdo = require __DIR__ . '/db.php';
    $sql = "
      SELECT COUNT(*)
      FROM role_module rm
      JOIN roles r ON rm.role_id = r.id
      JOIN modules m ON rm.module_id = m.id
      WHERE r.id = :role_id
        AND m.name = :moduleName
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':role_id'    => $user['role_id'],
      ':moduleName' => $moduleName
    ]);
    return ((int)$stmt->fetchColumn()) > 0;
}

/**
 * Return a list of all module names, ordered alphabetically.
 */
function get_all_module_names(): array {
    $pdo = require __DIR__ . '/db.php';
    $stmt = $pdo->query("SELECT name FROM modules ORDER BY name ASC");
    return array_column($stmt->fetchAll(), 'name');
}

/**
 * Return a list of all role names, ordered alphabetically.
 */
function get_all_roles(): array {
    $pdo = require __DIR__ . '/db.php';
    $stmt = $pdo->query("SELECT name FROM roles ORDER BY name ASC");
    return array_column($stmt->fetchAll(), 'name');
}
