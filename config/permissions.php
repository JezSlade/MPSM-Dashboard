<?php
// /public/mpsm/config/permissions.php

session_start();

// 1. Define all possible roles and their allowed modules
$ROLE_PERMISSIONS = [
    'sysop'   => [
        'modules'      => ['dashboard', 'customers', 'developer'], 
        'description'  => 'System Operator (full access)',
    ],
    'dealer'  => [
        'modules'      => ['dashboard', 'customers'], 
        'description'  => 'Dealer (view customers/devices)',
    ],
    'service' => [
        'modules'      => ['dashboard', 'customers'], 
        'description'  => 'Service (view customer data)', 
    ],
    'sales'   => [
        'modules'      => ['dashboard'], 
        'description'  => 'Sales (metrics only)', 
    ],
];

// 2. Stub: if no role in session, default to 'dealer'
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'dealer';
}

// 3. Helper: get current role
function getCurrentRole() {
    return $_SESSION['user_role'];
}

// 4. Helper: switch role (for testing)
if (isset($_GET['action']) && $_GET['action'] === 'switch' && isset($_GET['role'])) {
    $new = $_GET['role'];
    if (array_key_exists($new, $GLOBALS['ROLE_PERMISSIONS'])) {
        $_SESSION['user_role'] = $new;
    }
    header("Location: index.php");
    exit;
}

// 5. Helper: check if current role can see a given module
function canViewModule($moduleKey) {
    $role = getCurrentRole();
    return in_array($moduleKey, $GLOBALS['ROLE_PERMISSIONS'][$role]['modules']);
}

// 6. Return a list of all roles (for the switcher)
function getAllRoles() {
    return array_keys($GLOBALS['ROLE_PERMISSIONS']);
}
