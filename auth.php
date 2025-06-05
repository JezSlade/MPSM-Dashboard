<?php
// auth.php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function has_permission($permission) {
    $role = $_SESSION['role'] ?? 'Guest';
    $permissions = [
        'Developer' => ['view_devtools', 'manage_modules', 'run_tests'],
        'Admin' => ['manage_users', 'view_reports'],
        'Dealer' => ['view_customers', 'view_devices'],
        'Service' => ['manage_devices'],
        'Sales' => ['view_sales'],
        'Guest' => []
    ];
    return in_array($permission, $permissions[$role] ?? []);
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}