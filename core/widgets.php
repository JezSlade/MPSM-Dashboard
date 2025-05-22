<?php
// core/widgets.php
// v1.0.1 [Removed redundant session_start; assume session started in auth.php]

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/permissions.php';

/**
 * Fetches all widget definitions from the database.
 */
function get_all_widgets(): array {
    $pdo = get_db();
    $stmt = $pdo->query("
      SELECT id, name, display_name, description, category,
             endpoint, params, method, permission, help_link
        FROM widgets
       ORDER BY name
    ");
    $widgets = $stmt->fetchAll();
    return array_map(fn($w) => [
        'id'           => $w['id'],
        'name'         => $w['name'],
        'displayName'  => $w['display_name'],
        'description'  => $w['description'],
        'category'     => $w['category'],
        'endpoint'     => $w['endpoint'],
        'params'       => $w['params'] ? json_decode($w['params'], true) : [],
        'method'       => $w['method'],
        'permission'   => $w['permission'],
        'helpLink'     => $w['help_link']
    ], $widgets);
}

/**
 * Fetches the widgets the current user is allowed to see,
 * based on their roles, user overrides, and permissions.
 */
function get_user_widgets(): array {
    // session is already started in auth.php
    $pdo = get_db();
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        return [];
    }

    // 1) Determine default widgets for the user's roles
    $roles = get_user_roles();
    if (empty($roles)) {
        return [];
    }
    $inPlaceholders = implode(',', array_fill(0, count($roles), '?'));
    $stmt = $pdo->prepare("
      SELECT widget_id
        FROM role_widgets
       WHERE role_name IN ($inPlaceholders)
    ");
    $stmt->execute($roles);
    $roleWidgets = array_column($stmt->fetchAll(), 'widget_id');

    // 2) Apply any per-user overrides
    $stmt2 = $pdo->prepare("
      SELECT widget, enabled
        FROM user_widget_settings
       WHERE user_id = ?
    ");
    $stmt2->execute([$userId]);
    $overrides = [];
    foreach ($stmt2->fetchAll() as $row) {
        $stmt3 = $pdo->prepare("SELECT id FROM widgets WHERE name = ?");
        $stmt3->execute([$row['widget']]);
        $wid = $stmt3->fetchColumn();
        if ($wid !== false) {
            $overrides[$wid] = (bool)$row['enabled'];
        }
    }
    foreach ($overrides as $wid => $enabled) {
        if ($enabled && !in_array($wid, $roleWidgets, true)) {
            $roleWidgets[] = $wid;
        } elseif (!$enabled) {
            $roleWidgets = array_filter($roleWidgets, fn($id) => $id !== $wid);
        }
    }

    if (empty($roleWidgets)) {
        return [];
    }

    // 3) Fetch the widget definitions
    $placeholders = implode(',', array_fill(0, count($roleWidgets), '?'));
    $stmt4 = $pdo->prepare("
      SELECT id, name, display_name, description, category,
             endpoint, params, method, permission, help_link
        FROM widgets
       WHERE id IN ($placeholders)
       ORDER BY name
    ");
    $stmt4->execute($roleWidgets);
    $defs = $stmt4->fetchAll();

    // 4) Filter by permission
    $perms = get_user_permissions();
    $out = [];
    foreach ($defs as $w) {
        if (in_array($w['permission'], $perms, true) || is_admin()) {
            $out[] = [
                'name'        => $w['name'],
                'displayName' => $w['display_name'],
                'description' => $w['description'],
                'category'    => $w['category'],
                'endpoint'    => $w['endpoint'],
                'params'      => $w['params'] ? json_decode($w['params'], true) : [],
                'method'      => $w['method'],
                'permission'  => $w['permission'],
                'helpLink'    => $w['help_link']
            ];
        }
    }
    return $out;
}
