<?php
require_once BASE_PATH . 'db.php';
require_once BASE_PATH . 'functions.php';

if (!has_permission('view_status')) {
    echo "<p class='text-red-500 p-4'>Access denied.</p>";
    exit;
}

$username = $_SESSION['username'] ?? 'Unknown';
$db_status = $db ? 'Connected' : 'Disconnected';

?>

<div class="glass p-4 border border-gray-800 rounded space-y-2">
    <h3 class="text-xl text-cyan-neon flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Status
    </h3>
    <p class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Username: <?php echo htmlspecialchars($username); ?>
    </p>
    <p class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke="<?php echo $db ? '#10B981' : '#EF4444'; ?>">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 0v10"></path>
        </svg>
        Database: <?php echo $db_status; ?>
    </p>
</div>