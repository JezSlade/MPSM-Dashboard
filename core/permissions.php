<?php
// core/permissions.php
// v1.1.0 [Permissions & roles mapping]

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/debug.php';

function get_user_roles(): array {
    if (empty($_SESSION['user_id'])) return [];
    $pdo = get_db();
    $stmt = $pdo->prepare("
      SELECT r.name
      FROM roles r
      JOIN user_roles ur ON ur.role_id = r.id
      WHERE ur.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return array_column($stmt->fetchAll(), 'name');
}

function is_admin(): bool {
    $roles = get_user_roles();
    return in_array('Admin', $roles, true) || in_array('Developer', $roles, true);
}

function get_user_permissions(): array {
    if (empty($_SESSION['user_id'])) return [];
    $pdo = get_db();
    $sql = "
      SELECT DISTINCT p.name
      FROM permissions p
      JOIN role_permissions rp ON rp.permission_id = p.id
      JOIN user_roles ur       ON ur.role_id   = rp.role_id
      WHERE ur.user_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    return array_column($stmt->fetchAll(), 'name');
}

function require_permission(string $perm): void {
    if (!in_array($perm, get_user_permissions(), true) && !is_admin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
}

function require_admin(): void {
    if (!is_admin()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}
