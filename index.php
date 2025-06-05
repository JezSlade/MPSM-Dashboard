<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPSM Control Panel</title>
    <!-- Tailwind CSS CDN with fallback -->
    <link rel="stylesheet" href="/msds/styles-fallback.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind configuration (custom colors and fallbacks)
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'teal-custom': '#00cec9',
                    },
                },
            },
        };
    </script>
    <style>
        /* Fallback for backdrop-filter */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        @supports not (backdrop-filter: blur(10px)) {
            .glass {
                background: rgba(52, 73, 94, 0.5); /* Fallback color */
            }
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <?php
    session_start();
    require_once 'db.php';
    require_once 'auth.php';

    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    $role = $_SESSION['role'] ?? 'User';
    $modules = [
        'dashboard' => 'Dashboard 📌',
        'permissions' => 'Permissions 🔐',
        'devtools' => 'DevTools ⚙️'
    ];
    $current_module = $_GET['module'] ?? 'dashboard';
    ?>

    <header class="glass border-b border-gray-800 p-4 fixed w-full top-0 z-10">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl text-teal-custom">MPSM Control Panel 🎛️</h1>
            <div>
                <form method="POST" action="index.php" class="inline">
                    <select name="role" onchange="this.form.submit()" class="bg-gray-800 text-white p-2 rounded border border-gray-700">
                        <option value="User" <?php echo $role === 'User' ? 'selected' : ''; ?>>User 👤</option>
                        <option value="Developer" <?php echo $role === 'Developer' ? 'selected' : ''; ?>>Developer 🛠️</option>
                        <option value="Admin" <?php echo $role === 'Admin' ? 'selected' : ''; ?>>Admin 👑</option>
                    </select>
                </form>
                <a href="logout.php" class="ml-4 text-teal-custom hover:text-teal-300">Logout 🚪</a>
            </div>
        </div>
    </header>

    <div class="flex mt-16">
        <aside class="glass border-r border-gray-800 w-64 p-4 fixed h-[calc(100vh-64px)] overflow-y-auto">
            <nav>
                <ul class="space-y-2">
                    <?php foreach ($modules as $module => $label): ?>
                        <li>
                            <a href="?module=<?php echo $module; ?>" class="flex items-center p-2 text-gray-300 hover:bg-gray-800/20 rounded <?php echo $current_module === $module ? 'bg-gray-800 text-teal-custom' : ''; ?>">
                                <?php if ($module === 'dashboard'): ?>
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9-5v12"></path>
                                    </svg>
                                <?php elseif ($module === 'permissions'): ?>
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.564.36-3.045 1-4.364m-1 3.364a3 3 0 013-3m0 3.364a3 3 0 00-3 3m3-3v6m-1.5-1.5a1.5 1.5 0 113 0m-3 0a1.5 1.5 0 00-1.5-1.5m1.5 4.5v-3m0 3h-3"></path>
                                    </svg>
                                <?php elseif ($module === 'devtools'): ?>
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                <?php endif; ?>
                                <?php echo $label; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </aside>

        <main class="glass border-l border-gray-800 flex-1 p-6 ml-64 mt-2">
            <?php
            if (file_exists("$current_module.php")) {
                include "$current_module.php";
            } else {
                include 'dashboard.php';
            }
            ?>
        </main>
    </div>
</body>
</html>