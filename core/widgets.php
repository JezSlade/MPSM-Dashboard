<?php
// core/widgets.php
// v1.0.1 [Removed redundant session_start; use auth.php’s session]

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/permissions.php';

function get_all_widgets(): array {
    $pdo = get_db();
    $stmt = $pdo->query("
      SELECT id, name, display_name, description, category,
             endpoint, params, method, permission, help_link
        FROM widgets
       ORDER BY name
    ");
    return array_map(fn($w)=>[
        'id'          => $w['id'],
        'name'        => $w['name'],
        'displayName' => $w['display_name'],
        'description' => $w['description'],
        'category'    => $w['category'],
        'endpoint'    => $w['endpoint'],
        'params'      => $w['params'] ? json_decode($w['params'],true) : [],
        'method'      => $w['method'],
        'permission'  => $w['permission'],
        'helpLink'    => $w['help_link']
    ], $stmt->fetchAll());
}

function get_user_widgets(): array {
    $pdo    = get_db();
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) return [];

    // 1) By-role defaults
    $roles = get_user_roles();
    if (!$roles) return [];
    $ph    = implode(',', array_fill(0, count($roles), '?'));
    $stmt  = $pdo->prepare("SELECT widget_id FROM role_widgets WHERE role_name IN ($ph)");
    $stmt->execute($roles);
    $widgets = array_column($stmt->fetchAll(), 'widget_id');

    // 2) Per-user overrides
    $ovr = $pdo->prepare("SELECT widget,enabled FROM user_widget_settings WHERE user_id=?");
    $ovr->execute([$userId]);
    foreach ($ovr->fetchAll() as $row) {
        $wStmt = $pdo->prepare("SELECT id FROM widgets WHERE name=?");
        $wStmt->execute([$row['widget']]);
        $wid = $wStmt->fetchColumn();
        if ($wid) {
            if ($row['enabled']) {
                $widgets[] = $wid;
            } else {
                $widgets = array_diff($widgets, [$wid]);
            }
        }
    }
    if (!$widgets) return [];

    // 3) Fetch definitions & filter by permission
    $ph2  = implode(',', array_fill(0, count($widgets), '?'));
    $stmt = $pdo->prepare("
      SELECT * FROM widgets WHERE id IN ($ph2) ORDER BY name
    ");
    $stmt->execute($widgets);
    $defs = $stmt->fetchAll();

    $perms = get_user_permissions();
    return array_filter($defs, fn($w)=>
        in_array($w['permission'],$perms,true) || is_admin()
    );
}
