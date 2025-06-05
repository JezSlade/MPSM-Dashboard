<?php
// modules/status.php
require_once BASE_PATH . 'functions.php';

$logged_in = isset($_SESSION['user_id']) ? 'Logged In' : 'Not Logged In';
$username = $_SESSION['username'] ?? 'Unknown';
$role = $_SESSION['role'] ?? 'Guest';
$user_id = $_SESSION['user_id'] ?? 'N/A';
$last_login = isset($_SESSION['last_login']) ? date('Y-m-d H:i:s', $_SESSION['last_login']) : 'N/A';
$last_activity = isset($_SESSION['last_activity']) ? date('Y-m-d H:i:s', $_SESSION['last_activity']) : date('Y-m-d H:i:s', time());

// Fetch permissions from session cache
$permissions = $_SESSION['permissions'] ?? get_user_permissions($_SESSION['user_id']);
$_SESSION['permissions'] = $permissions; // Ensure cache is updated
$permissions_list = !empty($permissions) ? implode(', ', $permissions) : 'None';
?>

<div class="glass p-4 border-t border-gray-800 mt-4">
    <h3 class="text-lg text-cyan-neon mb-2">Status</h3>
    <ul class="text-gray-300 text-sm space-y-1">
        <li>Login Status: <?php echo htmlspecialchars($logged_in); ?></li>
        <li>Username: <?php echo htmlspecialchars($username); ?></li>
        <li>User ID: <?php echo htmlspecialchars($user_id); ?></li>
        <li>Current Role: <?php echo htmlspecialchars($role); ?></li>
        <li>Permissions: <?php echo htmlspecialchars($permissions_list); ?></li>
        <li>Last Login: <?php echo htmlspecialchars($last_login); ?></li>
        <li>Last Activity: <?php echo htmlspecialchars($last_activity); ?></li>
    </ul>
</div>