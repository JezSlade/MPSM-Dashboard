<?php
// These includes are kept as per your original file.
// Ensure db.php and functions.php exist in your BASE_PATH.
require_once BASE_PATH . 'db.php';
require_once BASE_PATH . 'functions.php';

// Variables from your original status.php
// Ensure session_start() is called early in your index.php or a global config file
// if you intend to use $_SESSION['username'].
$username = $_SESSION['username'] ?? 'Unknown';
// Assuming $db is a global variable or the result of your db.php connection.
$db_status = $db ? 'Connected' : 'Disconnected';

// $role and $accessible_modules are passed from index.php's scope
// (or ensure they are globally available if status.php is included standalone)
$current_user_role = $role ?? 'N/A'; // Use $role from index.php
$num_accessible_modules = count($accessible_modules ?? []);

?>

<div class="glass p-4 rounded-lg mt-4 text-sm">
    <h3 class="text-xl text-cyan-neon flex items-center mb-2">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9"/>
        </svg>
        System Status
    </h3>

    <div class="space-y-2">
        <p class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Logged in as: <span class="font-semibold ml-1"><?php echo htmlspecialchars($username); ?></span>
        </p>
        <p class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.149-1.28.43-1.856m0 0a4 4 0 110-3.287A4 4 0 017 20zM20 12a4 4 0 100-8 4 4 0 000 8z"></path>
            </svg>
            Role: <span class="font-semibold ml-1"><?php echo htmlspecialchars($current_user_role); ?></span>
        </p>
        <p class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 0v10"></path>
            </svg>
            Database: <span class="font-semibold ml-1" style="color: <?php echo $db_status === 'Connected' ? '#10B981' : '#EF4444'; ?>"><?php echo $db_status; ?></span>
        </p>
        <p class="flex items-center">
             <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1h-1.25M15 10l4-4m-4 4l-4-4m4 4v7a2 2 0 01-2 2H7a2 0 01-2-2V7a2 0 012-2h5.586a"></path></svg>
            Accessible Modules: <span class="font-semibold ml-1"><?php echo $num_accessible_modules; ?></span>
        </p>
    </div>
</div>