<?php
/**
 * config/permissions.php
 *
 * Defines which roles exist, and which modules each role can access.
 * Also sets the application version (used by header.php).
 */

// Application version
define('APP_VERSION', 'v0.1');

// Example permission matrix: role => [allowed modules]
$permissions = [
    'Admin'    => ['Dashboard', 'Customers', 'DevTools'],
    'Sales'    => ['Dashboard', 'Customers'],
    'Developer'=> ['Dashboard', 'DevTools'],
    // Add more roles as needed
];

/**
 * Check if the current user (based on $_SESSION['role']) has access to $moduleName.
 */
function user_has_permission(string $moduleName): bool {
    global $permissions;
    $role = $_SESSION['role'] ?? '';
    return isset($permissions[$role]) && in_array($moduleName, $permissions[$role], true);
}
