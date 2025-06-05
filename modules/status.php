<?php
// modules/status.php
$logged_in = isset($_SESSION['user_id']) ? 'Logged In' : 'Not Logged In';
$role = $_SESSION['role'] ?? 'Guest';
?>

<div class="glass p-4 border-t border-gray-800 mt-4">
    <h3 class="text-lg text-cyan-neon mb-2">Status</h3>
    <ul class="text-gray-300 text-sm space-y-1">
        <li>Login Status: <?php echo htmlspecialchars($logged_in); ?></li>
        <li>Current Role: <?php echo htmlspecialchars($role); ?></li>
        <li>Last Activity: <?php echo date('Y-m-d H:i:s', $_SESSION['last_activity'] ?? time()); ?></li>
    </ul>
</div>