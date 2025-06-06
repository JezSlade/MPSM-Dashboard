<?php
session_start(); // MUST BE THE VERY FIRST LINE

// Define SERVER_ROOT_PATH if not already defined (might be defined in index.php or config.php)
defined('SERVER_ROOT_PATH') or define('SERVER_ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);

require_once SERVER_ROOT_PATH . 'config.php';
require_once SERVER_ROOT_PATH . 'db.php';
require_once SERVER_ROOT_PATH . 'auth.php'; // Contains isLoggedIn() and logout()

$db = connect_db();

$error_message = '';

// If already logged in, redirect to index.php
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $db->prepare("SELECT id, username, password, role_id FROM users WHERE username = ?");
        if ($stmt === false) {
            error_log("Login prepare failed: " . $db->error);
            $error_message = 'Database error during login. Please try again later.';
        } else {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                // Get role name
                $role_name = 'Guest'; // Default role
                $role_stmt = $db->prepare("SELECT name FROM roles WHERE id = ?");
                if ($role_stmt) {
                    $role_stmt->bind_param('i', $user['role_id']);
                    $role_stmt->execute();
                    $role_result = $role_stmt->get_result();
                    $role_data = $role_result->fetch_assoc();
                    if ($role_data) {
                        $role_name = $role_data['name'];
                    }
                    $role_stmt->close();
                }

                // Authentication successful, set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $role_name; // Store the role name in session

                // Redirect to index.php (dashboard)
                header('Location: index.php');
                exit;
            } else {
                $error_message = 'Invalid username or password.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - My PHP System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="styles-fallback.css">
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center font-sans antialiased">
    <div class="glass p-8 rounded-lg shadow-xl w-full max-w-md">
        <h2 class="text-3xl text-cyan-neon font-bold mb-6 text-center">Login</h2>

        <?php if ($error_message): ?>
            <p class="bg-red-900 bg-opacity-30 text-red-300 p-3 rounded mb-4 text-sm text-center">
                <?php echo htmlspecialchars($error_message); ?>
            </p>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-default text-sm font-medium mb-2">Username:</label>
                <input type="text" id="username" name="username" required
                       class="w-full p-3 rounded-lg bg-black-smoke border border-gray-700 focus:ring-2 focus:ring-teal-custom focus:border-transparent text-white placeholder-gray-500 shadow-inner-custom"
                       placeholder="Enter your username">
            </div>
            <div>
                <label for="password" class="block text-default text-sm font-medium mb-2">Password:</label>
                <input type="password" id="password" name="password" required
                       class="w-full p-3 rounded-lg bg-black-smoke border border-gray-700 focus:ring-2 focus:ring-teal-custom focus:border-transparent text-white placeholder-gray-500 shadow-inner-custom"
                       placeholder="Enter your password">
            </div>
            <button type="submit"
                    class="w-full bg-gradient-to-r from-teal-custom to-blue-custom hover:from-blue-custom hover:to-teal-custom text-black font-bold py-3 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 shadow-button">
                Login
            </button>
        </form>
    </div>
</body>
</html>