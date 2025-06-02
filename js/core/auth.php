<?php
// core/auth.php
// v1.0.0 [Session-based authentication]

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/debug.php';

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function login_user(string $user, string $pass): bool {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ?');
    $stmt->execute([$user]);
    $row = $stmt->fetch();
    if ($row && password_verify($pass, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $row['id'];
        debug_log('User logged in', ['user_id' => $row['id']], 'INFO');
        return true;
    }
    return false;
}

function logout_user(): void {
    session_unset();
    session_destroy();
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, username FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}
