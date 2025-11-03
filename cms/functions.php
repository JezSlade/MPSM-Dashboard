<?php
/**
 * MPSM Dashboard - Core Functions
 *
 * All utility functions in one place
 * Following Engineering Standards Rule 13: One Responsibility Per File
 */

/**
 * Get database connection
 * Following Rule 2: One Database Access Pattern (Direct PDO)
 */
function getDatabase() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    return $pdo;
}

/**
 * Get MPS API OAuth token
 * Following Rule 10: Functions Must Be Short
 */
function getMPSToken() {
    static $token = null;
    static $tokenExpiry = 0;

    // Return cached token if still valid
    if ($token && time() < $tokenExpiry) {
        return $token;
    }

    // FIX BUG #6: Wrap token refresh in try-catch to reset state on failure
    try {
        // Request new token
        $ch = curl_init(MPS_API_TOKEN_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 10,  // Add timeout
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => MPS_GRANT_TYPE,
                'client_id' => MPS_CLIENT_ID,
                'client_secret' => MPS_CLIENT_SECRET,
                'username' => MPS_USERNAME,
                'password' => MPS_PASSWORD,
                'scope' => MPS_SCOPE
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            // FIX BUG #6: Reset token on failure
            $token = null;
            $tokenExpiry = 0;
            throw new Exception("OAuth token request failed: $curlError");
        }

        if ($httpCode !== 200) {
            // FIX BUG #6: Reset token on failure
            $token = null;
            $tokenExpiry = 0;
            throw new Exception("OAuth token request failed with HTTP $httpCode: $response");
        }

        $data = json_decode($response, true);

        if (!isset($data['access_token'])) {
            // FIX BUG #6: Reset token on failure
            $token = null;
            $tokenExpiry = 0;
            throw new Exception("No access token in OAuth response");
        }

        $token = $data['access_token'];
        // Token expires in 1 hour, refresh 5 minutes early
        $tokenExpiry = time() + 3300;

        return $token;
    } catch (Exception $e) {
        // FIX BUG #6: Ensure token is null on any failure
        $token = null;
        $tokenExpiry = 0;
        error_log("getMPSToken() failed: " . $e->getMessage());
        throw $e;  // Re-throw for caller to handle
    }
}

/**
 * Call MPS API endpoint
 * Following Rule 9: Function Naming (Verb + Noun)
 */
function callMPSAPI($action, $params = []) {
    try {
        $token = getMPSToken();

        $ch = curl_init(MPS_API_BASE . $action);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($params)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("API call failed with HTTP $httpCode");
        }

        return json_decode($response, true);

    } catch (Exception $e) {
        error_log("MPS API call failed ($action): " . $e->getMessage());
        throw $e; // Re-throw to let caller handle (Rule 7: No Silent Returns)
    }
}

/**
 * Require authentication
 * Following Rule 25: Session-Based Auth Only
 */
function requireAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['logged_in'])) {
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            // API request
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        } else {
            // Browser request
            header('Location: login.html');
            exit;
        }
    }

    // Refresh session timeout
    $_SESSION['last_activity'] = time();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['logged_in']);
}

/**
 * Login user
 */
function loginUser($username, $password) {
    $pdo = getDatabase();

    // Get user from database
    $stmt = $pdo->prepare("SELECT id, username, password FROM " . DB_PREFIX . "users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    // Set session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['last_activity'] = time();

    return true;
}

/**
 * Logout user
 */
function logoutUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}

/**
 * Get user preferences
 */
function getUserPreferences($userId) {
    $pdo = getDatabase();

    $stmt = $pdo->prepare("SELECT preferences FROM " . DB_PREFIX . "user_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();

    if ($result) {
        return json_decode($result['preferences'], true);
    }

    // Return defaults
    return [
        'theme' => 'light',
        'dealerCode' => DEFAULT_DEALER_CODE,
        'customerCode' => DEFAULT_CUSTOMER_CODE,
        'customerId' => DEFAULT_CUSTOMER_ID,
        'customerName' => DEFAULT_CUSTOMER_NAME
    ];
}

/**
 * Save user preferences
 */
function saveUserPreferences($userId, $preferences) {
    $pdo = getDatabase();

    $json = json_encode($preferences);

    $stmt = $pdo->prepare("
        INSERT INTO " . DB_PREFIX . "user_preferences (user_id, preferences)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE preferences = VALUES(preferences)
    ");

    return $stmt->execute([$userId, $json]);
}

/**
 * Track page visit
 */
function trackVisit($pageUrl = '') {
    try {
        $pdo = getDatabase();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'anonymous';

        $stmt = $pdo->prepare("
            INSERT INTO " . DB_PREFIX . "visitor_log
            (user_id, username, ip_address, user_agent, page_url, visited_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $userId,
            $username,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $pageUrl ?: $_SERVER['REQUEST_URI'] ?? '/'
        ]);

    } catch (Exception $e) {
        error_log("Failed to track visit: " . $e->getMessage());
        // Don't throw - tracking failures shouldn't break the app
    }
}

/**
 * Get comprehensive system health status with detailed verification
 */
function getSystemHealth() {
    $now = new DateTime('now', new DateTimeZone('America/New_York'));

    $health = [
        'timestamp' => $now->format('c'),
        'timezone' => 'America/New_York (Eastern)',
        'server_time' => $now->format('Y-m-d H:i:s T'),
        'database' => [
            'connected' => false,
            'error' => null,
            'verification' => null,
            'last_check' => null
        ],
        'mpsApi' => [
            'connected' => false,
            'error' => null,
            'verification' => null,
            'last_check' => null,
            'response_time_ms' => null
        ],
        'cache' => [
            'enabled' => false,
            'cached_entries' => 0,
            'fresh_entries' => 0,
            'error' => null,
            'verification' => null,
            'last_check' => null,
            'oldest_entry' => null,
            'newest_entry' => null,
            'storage_size_mb' => null
        ],
        'server' => [
            'php_version' => phpversion(),
            'memory_limit' => ini_get('memory_limit'),
            'memory_used_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'disk_free_gb' => null,
            'disk_total_gb' => null,
            'load_average' => null,
            'uptime' => null
        ],
        'session' => [
            'active' => isLoggedIn(),
            'user' => $_SESSION['username'] ?? null,
            'started_at' => isset($_SESSION['login_time']) ? date('c', $_SESSION['login_time']) : null
        ]
    ];

    // Test database with detailed verification
    try {
        $checkStart = microtime(true);
        $pdo = getDatabase();

        // Verify with actual query
        $stmt = $pdo->query("SELECT DATABASE() as db_name, NOW() as server_time, VERSION() as version");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $checkDuration = round((microtime(true) - $checkStart) * 1000, 2);

        $health['database']['connected'] = true;
        $health['database']['host'] = DB_HOST;
        $health['database']['name'] = $result['db_name'] ?? DB_NAME;
        $health['database']['version'] = $result['version'] ?? 'unknown';
        $health['database']['server_time'] = $result['server_time'] ?? null;
        $health['database']['response_time_ms'] = $checkDuration;
        $health['database']['last_check'] = $now->format('c');
        $health['database']['verification'] = "Query executed successfully in {$checkDuration}ms";

        // Get table count
        $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = DATABASE()");
        $tableResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $health['database']['table_count'] = (int)$tableResult['table_count'];

        // Get visitor log count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "visitor_log");
        $visitorResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $health['database']['visitor_log_entries'] = (int)$visitorResult['count'];

    } catch (Exception $e) {
        $health['database']['error'] = $e->getMessage();
        $health['database']['last_check'] = $now->format('c');
        $health['database']['verification'] = 'Connection failed';
    }

    // Test MPS API with timing
    try {
        $checkStart = microtime(true);
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/?action=Ping', false, $context);
        $checkDuration = round((microtime(true) - $checkStart) * 1000, 2);

        if ($response !== false) {
            $data = json_decode($response, true);
            $health['mpsApi']['connected'] = true;
            $health['mpsApi']['response_time_ms'] = $checkDuration;
            $health['mpsApi']['last_check'] = $now->format('c');
            $health['mpsApi']['verification'] = "Ping successful in {$checkDuration}ms";
            $health['mpsApi']['response_data'] = $data;
        } else {
            throw new Exception("mps-api backend not responding");
        }
    } catch (Exception $e) {
        $health['mpsApi']['error'] = $e->getMessage();
        $health['mpsApi']['last_check'] = $now->format('c');
        $health['mpsApi']['verification'] = 'Connection failed';
    }

    // Check cache engine with detailed stats
    try {
        $checkStart = microtime(true);
        $cacheDir = __DIR__ . '/../mps-api/cache/storage';

        if (is_dir($cacheDir)) {
            $health['cache']['enabled'] = true;
            $files = glob($cacheDir . '/*.json');

            if ($files !== false) {
                $health['cache']['cached_entries'] = count($files);

                $totalSize = 0;
                $oldestTime = null;
                $newestTime = null;
                $freshCount = 0;
                $tenMinutesAgo = time() - 600;

                foreach ($files as $file) {
                    $mtime = filemtime($file);
                    $totalSize += filesize($file);

                    if ($oldestTime === null || $mtime < $oldestTime) {
                        $oldestTime = $mtime;
                    }
                    if ($newestTime === null || $mtime > $newestTime) {
                        $newestTime = $mtime;
                    }
                    if ($mtime > $tenMinutesAgo) {
                        $freshCount++;
                    }
                }

                $health['cache']['fresh_entries'] = $freshCount;
                $health['cache']['storage_size_mb'] = round($totalSize / 1024 / 1024, 2);
                $health['cache']['oldest_entry'] = $oldestTime ? date('c', $oldestTime) : null;
                $health['cache']['newest_entry'] = $newestTime ? date('c', $newestTime) : null;
                $health['cache']['storage_path'] = 'mps-api/cache/storage';

                $checkDuration = round((microtime(true) - $checkStart) * 1000, 2);
                $health['cache']['last_check'] = $now->format('c');
                $health['cache']['verification'] = "Scanned {$health['cache']['cached_entries']} cache files in {$checkDuration}ms";
            }
        } else {
            $health['cache']['error'] = 'Cache directory not found';
            $health['cache']['verification'] = 'Directory does not exist';
        }
    } catch (Exception $e) {
        $health['cache']['error'] = $e->getMessage();
        $health['cache']['last_check'] = $now->format('c');
        $health['cache']['verification'] = 'Check failed';
    }

    // Server health metrics
    try {
        // Disk space
        $diskFree = @disk_free_space(__DIR__);
        $diskTotal = @disk_total_space(__DIR__);
        if ($diskFree !== false && $diskTotal !== false) {
            $health['server']['disk_free_gb'] = round($diskFree / 1024 / 1024 / 1024, 2);
            $health['server']['disk_total_gb'] = round($diskTotal / 1024 / 1024 / 1024, 2);
            $health['server']['disk_used_percent'] = round((($diskTotal - $diskFree) / $diskTotal) * 100, 1);
        }

        // Load average (Unix/Linux only)
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            if ($load !== false) {
                $health['server']['load_average'] = [
                    '1min' => round($load[0], 2),
                    '5min' => round($load[1], 2),
                    '15min' => round($load[2], 2)
                ];
            }
        }

        // Uptime (Unix/Linux only)
        if (file_exists('/proc/uptime')) {
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime !== false) {
                $uptimeSeconds = (int)explode(' ', $uptime)[0];
                $days = floor($uptimeSeconds / 86400);
                $hours = floor(($uptimeSeconds % 86400) / 3600);
                $health['server']['uptime'] = "{$days}d {$hours}h";
                $health['server']['uptime_seconds'] = $uptimeSeconds;
            }
        }

        // PHP info
        $health['server']['max_execution_time'] = ini_get('max_execution_time') . 's';
        $health['server']['upload_max_filesize'] = ini_get('upload_max_filesize');
        $health['server']['post_max_size'] = ini_get('post_max_size');

    } catch (Exception $e) {
        $health['server']['error'] = $e->getMessage();
    }

    return $health;
}

/**
 * JSON response helper
 * Following Rule 6: Always Show Errors
 */
function jsonResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Error response helper
 */
function jsonError($message, $httpCode = 500) {
    jsonResponse(['success' => false, 'error' => $message], $httpCode);
}

/**
 * Success response helper
 */
function jsonSuccess($data = []) {
    jsonResponse(array_merge(['success' => true], $data));
}

/**
 * Initialize database tables
 * Creates tables if they don't exist
 */
function initializeTables() {
    $pdo = getDatabase();

    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // User preferences table
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "user_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        preferences TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user (user_id),
        FOREIGN KEY (user_id) REFERENCES " . DB_PREFIX . "users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Visitor log table
    $pdo->exec("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "visitor_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        username VARCHAR(100),
        ip_address VARCHAR(45),
        user_agent TEXT,
        page_url TEXT,
        visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_visited_at (visited_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Create default admin user if no users exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM " . DB_PREFIX . "users");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO " . DB_PREFIX . "users (username, password) VALUES (?, ?)");
        $stmt->execute(['admin', password_hash('admin', PASSWORD_DEFAULT)]);
    }
}
