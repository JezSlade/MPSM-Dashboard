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
 * Set security headers for better cross-browser compatibility
 */
function setSecurityHeaders() {
    // CORS headers for same-origin requests
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        $origin = $_SERVER['HTTP_ORIGIN'];
        $allowedOrigins = [
            'https://mpsm.resolutionsbydesign.us',
            'http://localhost',
            'http://127.0.0.1'
        ];

        // Allow same-origin requests
        if (in_array($origin, $allowedOrigins) ||
            strpos($origin, 'mpsm.resolutionsbydesign.us') !== false) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Max-Age: 3600');
        }
    }

    // Security headers for all responses
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Handle OPTIONS preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Cache Management Functions
 * Simple file-based cache for API responses
 */

// Define cache directory
define('CACHE_DIR', __DIR__ . '/api/cache/');

/**
 * Get cached data
 * @param string $key Cache key
 * @return mixed|null Cached data or null if not found/expired
 */
function cacheGet($key) {
    $cacheFile = CACHE_DIR . $key . '.json';

    if (!file_exists($cacheFile)) {
        return null;
    }

    $cacheData = json_decode(file_get_contents($cacheFile), true);

    if (!$cacheData || !isset($cacheData['expires_at'])) {
        return null;
    }

    // Check if expired
    if (time() > $cacheData['expires_at']) {
        @unlink($cacheFile);
        return null;
    }

    return $cacheData['data'];
}

/**
 * Store data in cache
 * @param string $key Cache key
 * @param mixed $data Data to cache
 * @param int $ttl Time to live in seconds (default: 3600)
 */
function cacheStore($key, $data, $ttl = 3600) {
    // Ensure cache directory exists
    if (!is_dir(CACHE_DIR)) {
        mkdir(CACHE_DIR, 0755, true);
    }

    $cacheFile = CACHE_DIR . $key . '.json';

    $cacheData = [
        'data' => $data,
        'cached_at' => time(),
        'expires_at' => time() + $ttl,
        'ttl' => $ttl
    ];

    file_put_contents($cacheFile, json_encode($cacheData));
}

/**
 * Clear cache by key or pattern
 * @param string $pattern Cache key or pattern (e.g., 'devices*')
 */
function cacheClear($pattern = '*') {
    $files = glob(CACHE_DIR . $pattern . '.json');
    foreach ($files as $file) {
        @unlink($file);
    }
}

/**
 * Get cache statistics
 * @return array Cache stats
 */
function getCacheStats() {
    if (!is_dir(CACHE_DIR)) {
        return [
            'total_size_mb' => 0,
            'total_entries' => 0,
            'fresh_entries' => 0,
            'stale_entries' => 0
        ];
    }

    $files = glob(CACHE_DIR . '*.json');
    $totalSize = 0;
    $freshCount = 0;
    $staleCount = 0;
    $oldestTime = null;
    $newestTime = null;

    foreach ($files as $file) {
        $totalSize += filesize($file);
        $data = json_decode(file_get_contents($file), true);

        if ($data && isset($data['expires_at'])) {
            if (time() <= $data['expires_at']) {
                $freshCount++;
            } else {
                $staleCount++;
            }

            if (isset($data['cached_at'])) {
                if (!$oldestTime || $data['cached_at'] < $oldestTime) {
                    $oldestTime = $data['cached_at'];
                }
                if (!$newestTime || $data['cached_at'] > $newestTime) {
                    $newestTime = $data['cached_at'];
                }
            }
        }
    }

    return [
        'total_size_mb' => round($totalSize / 1024 / 1024, 2),
        'total_entries' => count($files),
        'fresh_entries' => $freshCount,
        'stale_entries' => $staleCount,
        'oldest_entry' => $oldestTime ? date('Y-m-d H:i:s', $oldestTime) : null,
        'newest_entry' => $newestTime ? date('Y-m-d H:i:s', $newestTime) : null
    ];
}

/**
 * JSON response helper
 * Following Rule 6: Always Show Errors
 */
function jsonResponse($data, $httpCode = 200) {
    setSecurityHeaders();
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
 * Ensure device CRUD feature is enabled
 */
function ensureDeviceCrudEnabled(): void
{
    if (!defined('FEATURE_DEVICE_CRUD') || FEATURE_DEVICE_CRUD !== true) {
        jsonError('Device lifecycle management is currently disabled', 403);
    }
}

/**
 * Append device CRUD activity to audit log
 *
 * @param string $action Action name (create/update/delete/list)
 * @param array $context Request context to log
 * @param array|null $response Response payload or null on failure
 * @param string $status success|error
 * @param string|null $message Additional contextual message
 */
function logDeviceCrudAction(string $action, array $context, ?array $response, string $status = 'success', ?string $message = null): void
{
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/device-crud-' . date('Y-m-d') . '.log';
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user' => $_SESSION['username'] ?? 'unknown',
        'action' => $action,
        'status' => $status,
        'context' => $context,
        'response' => $response,
        'message' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];

    file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Call MPS API via query endpoint (standardized)
 * Replaces duplicate callMpsApiDirect() functions in various endpoints
 *
 * @param string $action MPS API action (e.g., 'Device/List')
 * @param array $params Parameters for the action
 * @return array|null Response data or null on failure
 */
function callMPSQuery($action, $params = []) {
    $payload = json_encode([
        'action' => $action,
        'params' => $params
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 20,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    if (!$data || !isset($data['success']) || !$data['success']) {
        return null;
    }

    return $data['data'] ?? [];
}

/**
 * Extract devices array from API response (standardized)
 * Handles different response formats from MPS API
 *
 * @param array $response API response
 * @return array Array of devices
 */
function extractDevicesFromResponse($response) {
    if (!is_array($response)) {
        return [];
    }

    if (isset($response['Items']) && is_array($response['Items'])) {
        return $response['Items'];
    }

    if (isset($response['Result']) && is_array($response['Result'])) {
        return $response['Result'];
    }

    if (array_keys($response) === range(0, count($response) - 1)) {
        return $response;
    }

    if (isset($response['data']) && is_array($response['data'])) {
        return extractDevicesFromResponse($response['data']);
    }

    return [];
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

// ============================================================================
// BACKWARDS COMPATIBILITY WRAPPERS FOR REPOSITORY LAYER
// ============================================================================
// These functions maintain existing API while using new repository pattern
// Will be deprecated in future releases once all code migrates to repositories

/**
 * Get cached devices using DeviceRepository
 * Backwards compatible with legacy device access
 */
function getCachedDevices($filters = []) {
    try {
        return app(DeviceRepository::class)->findAll($filters);
    } catch (Exception $e) {
        error_log("getCachedDevices error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get device by serial number using DeviceRepository
 */
function getCachedDevice($serial) {
    try {
        return app(DeviceRepository::class)->findBySerial($serial);
    } catch (Exception $e) {
        error_log("getCachedDevice error: " . $e->getMessage());
        return null;
    }
}

/**
 * Cache device data using DeviceRepository
 */
function cacheDeviceData($deviceData, $ttl = 3600) {
    try {
        return app(DeviceRepository::class)->cacheDevice($deviceData, $ttl);
    } catch (Exception $e) {
        error_log("cacheDeviceData error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get device drilldown using DeviceRepository
 */
function getDeviceDrilldown($serial) {
    try {
        return app(DeviceRepository::class)->getDrilldown($serial);
    } catch (Exception $e) {
        error_log("getDeviceDrilldown error: " . $e->getMessage());
        return null;
    }
}

/**
 * Cache device drilldown using DeviceRepository
 */
function cacheDeviceDrilldown($serial, $drilldownData) {
    try {
        return app(DeviceRepository::class)->cacheDrilldown($serial, $drilldownData);
    } catch (Exception $e) {
        error_log("cacheDeviceDrilldown error: " . $e->getMessage());
        return false;
    }
}

/**
 * Search devices using DeviceRepository
 */
function searchDevices($query, $limit = 10) {
    try {
        return app(DeviceRepository::class)->search($query, $limit);
    } catch (Exception $e) {
        error_log("searchDevices error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get panel messages for device using PanelMessageRepository
 */
function getPanelMessages($serial, $limit = 100) {
    try {
        return app(PanelMessageRepository::class)->findByDevice($serial, $limit);
    } catch (Exception $e) {
        error_log("getPanelMessages error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get recent panel messages using PanelMessageRepository
 */
function getRecentPanelMessages($hours = 24, $limit = 100, $deviceSerial = null) {
    try {
        return app(PanelMessageRepository::class)->findRecent($hours, $limit, $deviceSerial);
    } catch (Exception $e) {
        error_log("getRecentPanelMessages error: " . $e->getMessage());
        return [];
    }
}

/**
 * Store panel message using PanelMessageRepository
 */
function storePanelMessage($messageData) {
    try {
        return app(PanelMessageRepository::class)->store($messageData);
    } catch (Exception $e) {
        error_log("storePanelMessage error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get panel message statistics using PanelMessageRepository
 */
function getPanelMessageStats($hours = 24) {
    try {
        return app(PanelMessageRepository::class)->getStatistics($hours);
    } catch (Exception $e) {
        error_log("getPanelMessageStats error: " . $e->getMessage());
        return [
            'total_messages' => 0,
            'unique_devices' => 0,
            'unique_customers' => 0,
            'oldest_message' => null,
            'newest_message' => null,
        ];
    }
}

/**
 * Find user by username using UserRepository
 */
function findUserByUsername($username) {
    try {
        return app(UserRepository::class)->findByUsername($username);
    } catch (Exception $e) {
        error_log("findUserByUsername error: " . $e->getMessage());
        return null;
    }
}

/**
 * Verify user password using UserRepository
 */
function verifyUserPassword($username, $password) {
    try {
        return app(UserRepository::class)->verifyPassword($username, $password);
    } catch (Exception $e) {
        error_log("verifyUserPassword error: " . $e->getMessage());
        return false;
    }
}
