<?php
// These includes are kept as per your original file.
require_once SERVER_ROOT_PATH . 'db.php';
require_once SERVER_ROOT_PATH . 'functions.php';

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

<header class="bg-gray-800 p-4 flex justify-between items-center shadow-md">
    <div class="flex items-center">
        <img src="logo.png" alt="Logo" class="h-10 mr-3"> <h1 class="text-2xl font-bold text-cyan-neon">My PHP System</h1>
    </div>
    <nav>
        <ul class="flex space-x-4">
            <?php if (isLoggedIn()): ?>
                <?php foreach ($accessible_modules as $mod_name): ?>
                    <?php if ($mod_name === 'dashboard'): ?>
                        <li><a href="?module=dashboard" class="text-gray-300 hover:text-white transition duration-300">Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="?module=<?php echo htmlspecialchars($mod_name); ?>" class="text-gray-300 hover:text-white transition duration-300"><?php echo ucfirst(htmlspecialchars($mod_name)); ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="flex items-center space-x-4">
        <?php if (isLoggedIn()): ?>
            <span class="text-sm text-gray-400">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?> (<?php echo htmlspecialchars($role); ?>)</span>
            <a href="logout.php" class="btn-primary">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn-primary">Login</a>
        <?php endif; ?>
    </div>
</header>

<div class="glass p-4 rounded-lg mt-4 text-sm">
    <h3 class="text-xl text-cyan-neon flex items-center mb-2">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9..."></path>
        </svg>
        System Status
    </h3>
    <p class="flex items-center mb-1">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="color: <?php echo $db_status === 'Connected' ? '#10B981' : '#EF4444'; ?>">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 0v10"></path>
            </svg>
            Database: <span class="font-semibold ml-1"><?php echo $db_status; ?></span>
        </p>
        <p class="flex items-center">
             <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1h-1.25M15 10l4-4m-4 4l-4-4m4 4v7a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a..."></path>
            </svg>
            Accessible Modules: <span class="font-semibold ml-1"><?php echo $num_accessible_modules; ?></span>
        </p>
</div>