<?php
// Enable PHP error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Define BASE_PATH
define('BASE_PATH', __DIR__ . '/');

// Include dependencies
require_once BASE_PATH . 'db.php';
require_once BASE_PATH . 'auth.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    try {
        if (empty($username) || empty($password)) {
            throw new Exception('Username and password are required.');
        }

        // Mock authentication (replace with your actual logic)
        if ($username === 'admin' && $password === 'password') {
            $_SESSION['user_id'] = 1;
            $_SESSION['role'] = 'Admin';
            header('Location: index.php');
            exit;
        } else {
            throw new Exception('Invalid credentials.');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MPSM Control Panel</title>
    <!-- Tailwind CSS CDN with fallback -->
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>styles-fallback.css" type="text/css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
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
        /* Fallback for backdrop-filter and offline */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        @supports not (backdrop-filter: blur(10px)) {
            .glass {
                background: rgba(52, 73, 94, 0.5);
            }
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen font-sans flex items-center justify-center">
    <div class="glass p-6 rounded-lg border border-gray-800 w-full max-w-md">
        <h2 class="text-2xl text-teal-custom mb-4 text-center">Login to MPSM 🎛️</h2>
        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4 text-center"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-gray-300">Username:</label>
                <input type="text" name="username" required class="w-full p-2 bg-gray-800 text-white border border-gray-700 rounded">
            </div>
            <div>
                <label class="block text-gray-300">Password:</label>
                <input type="password" name="password" required class="w-full p-2 bg-gray-800 text-white border border-gray-700 rounded">
            </div>
            <button type="submit" class="w-full bg-gray-800 text-teal-custom p-2 rounded border border-gray-700 hover:bg-gray-700">Login 🔐</button>
        </form>
    </div>
</body>
</html>