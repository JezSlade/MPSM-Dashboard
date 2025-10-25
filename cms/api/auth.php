<?php
/**
 * Lightweight Authentication API
 * Simple user CRUD - keeps honest people honest
 */

header('Content-Type: application/json');

// Simple user database (file-based, not secure but lightweight)
define('USERS_FILE', __DIR__ . '/../data/users.json');

// Ensure data directory exists
$dataDir = dirname(USERS_FILE);
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// Initialize users file if it doesn't exist
if (!file_exists(USERS_FILE)) {
    $defaultUsers = [
        [
            'id' => 1,
            'username' => 'admin',
            'password' => password_hash('admin', PASSWORD_DEFAULT),
            'created' => date('c')
        ]
    ];
    file_put_contents(USERS_FILE, json_encode($defaultUsers, JSON_PRETTY_PRINT));
}

// Load users
function loadUsers() {
    return json_decode(file_get_contents(USERS_FILE), true) ?: [];
}

// Save users
function saveUsers($users) {
    return file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

// Handle request
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: [];

try {
    switch ($method) {
        case 'POST':
            $action = $input['action'] ?? '';

            if ($action === 'login') {
                // Login
                $username = $input['username'] ?? '';
                $password = $input['password'] ?? '';

                $users = loadUsers();
                foreach ($users as $user) {
                    if ($user['username'] === $username && password_verify($password, $user['password'])) {
                        session_start();
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['logged_in'] = true;

                        echo json_encode([
                            'success' => true,
                            'user' => ['id' => $user['id'], 'username' => $user['username']]
                        ]);
                        exit;
                    }
                }

                echo json_encode(['success' => false, 'error' => 'Invalid credentials']);

            } elseif ($action === 'create') {
                // Create user
                session_start();
                if (empty($_SESSION['logged_in'])) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Not authenticated']);
                    exit;
                }

                $username = $input['username'] ?? '';
                $password = $input['password'] ?? '';

                if (empty($username) || empty($password)) {
                    echo json_encode(['success' => false, 'error' => 'Username and password required']);
                    exit;
                }

                $users = loadUsers();

                // Check if username exists
                foreach ($users as $user) {
                    if ($user['username'] === $username) {
                        echo json_encode(['success' => false, 'error' => 'Username already exists']);
                        exit;
                    }
                }

                // Create new user
                $newUser = [
                    'id' => count($users) + 1,
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'created' => date('c')
                ];

                $users[] = $newUser;
                saveUsers($users);

                echo json_encode([
                    'success' => true,
                    'user' => ['id' => $newUser['id'], 'username' => $newUser['username']]
                ]);
            }
            break;

        case 'GET':
            // List users (no passwords)
            session_start();
            if (empty($_SESSION['logged_in'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Not authenticated']);
                exit;
            }

            $users = loadUsers();
            $safeUsers = array_map(function($user) {
                return [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'created' => $user['created'] ?? null
                ];
            }, $users);

            echo json_encode(['success' => true, 'users' => $safeUsers]);
            break;

        case 'DELETE':
            // Delete user
            session_start();
            if (empty($_SESSION['logged_in'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Not authenticated']);
                exit;
            }

            $userId = $input['id'] ?? 0;

            if ($userId == 1) {
                echo json_encode(['success' => false, 'error' => 'Cannot delete admin user']);
                exit;
            }

            $users = loadUsers();
            $users = array_filter($users, function($user) use ($userId) {
                return $user['id'] != $userId;
            });

            saveUsers(array_values($users));
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
