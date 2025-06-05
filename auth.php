<?php
// auth.php
function isLoggedIn() {
    // Check if user_id is set in session (basic auth check)
    return isset($_SESSION['user_id']);
}

function has_permission($permission) {
    // Basic permission check based on role
    $role = $_SESSION['role'] ?? 'User';
    $permissions = [
        'User' => [],
        'Developer' => ['view_devtools'],
        'Admin' => ['view_devtools', 'manage_modules']
    ];
    return in_array($permission, $permissions[$role] ?? []);
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}