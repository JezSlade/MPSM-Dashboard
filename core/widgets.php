<?php
// core/widgets.php
// v1.0.0 [DB-driven widget definitions]

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/permissions.php';

function get_all_widgets(): array {
    $pdo = get_db();
    $stmt = $pdo->query("
      SELECT id,name,display_name,description,category,endpoint,params,method,permission,help_link
        FROM widgets
       ORDER BY name
    ");
    $rows = $stmt->fetchAll();
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
    ], $rows);
}

function get_user_widgets(): array {
    session_start();
    $pdo = get_db();
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) return [];

    $roles = get_user_roles();
    if (empty($roles)) return [];

    // Default by role
    $in    = implode(',', array_fill(0,count($roles),'?'));
    $stmt  = $pdo->prepare("SELECT widget_id FROM role_widgets WHERE role_name IN ($in)");
    $stmt->execute($roles);
    $roleWidgets = array_column($stmt->fetchAll(), 'widget_id');

    // User overrides
    $stmt2 = $pdo->prepare("SELECT widget,enabled FROM user_widget_settings WHERE user_id=?");
    $stmt2->execute([$userId]);
    $over = [];
    foreach ($stmt2->fetchAll() as $r) {
        $stmt3 = $pdo->prepare("SELECT id FROM widgets WHERE name=?");
        $stmt3->execute([$r['widget']]);
        $wid = $stmt3->fetchColumn();
        if ($wid) $over[$wid] = (bool)$r['enabled'];
    }
    foreach ($over as $wid => $enabled) {
        if ($enabled && !in_array($wid, $roleWidgets)) {
            $roleWidgets[] = $wid;
        } elseif (!$enabled) {
            $roleWidgets = array_filter($roleWidgets, fn($x)=>$x!==$wid);
        }
    }
    if (empty($roleWidgets)) return [];

    // Fetch definitions
    $in2  = implode(',', array_fill(0,count($roleWidgets),'?'));
    $stmt4= $pdo->prepare("
      SELECT id,name,display_name,description,category,endpoint,params,method,permission,help_link
        FROM widgets
       WHERE id IN ($in2)
       ORDER BY name
    ");
    $stmt4->execute($roleWidgets);
    $defs = $stmt4->fetchAll();

    // Filter by permission
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
              'params'      => $w['params'] ? json_decode($w['params'],true) : [],
              'method'      => $w['method'],
              'permission'  => $w['permission'],
              'helpLink'    => $w['help_link']
            ];
        }
    }
    return $out;
}
