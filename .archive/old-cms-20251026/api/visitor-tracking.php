<?php
/**
 * Visitor Tracking API
 * Logs visitor activity to MySQL database for robust analytics
 */

session_start();

// Require authentication
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Create visitor_log table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS visitor_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45),
            user_agent TEXT,
            username VARCHAR(100),
            session_id VARCHAR(128),
            page_url TEXT,
            referrer TEXT,
            visited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_visited_at (visited_at),
            INDEX idx_ip (ip_address),
            INDEX idx_username (username)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    if ($method === 'POST') {
        // Log a new visit
        $data = json_decode(file_get_contents('php://input'), true);

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $username = $_SESSION['username'] ?? 'anonymous';
        $sessionId = session_id();
        $pageUrl = $data['page_url'] ?? $_SERVER['REQUEST_URI'] ?? '';
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';

        $stmt = $pdo->prepare("
            INSERT INTO visitor_log (ip_address, user_agent, username, session_id, page_url, referrer)
            VALUES (:ip, :ua, :username, :session, :page, :referrer)
        ");

        $stmt->execute([
            ':ip' => $ip,
            ':ua' => $userAgent,
            ':username' => $username,
            ':session' => $sessionId,
            ':page' => $pageUrl,
            ':referrer' => $referrer
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Visit logged',
            'id' => $pdo->lastInsertId()
        ]);

    } elseif ($method === 'GET') {
        // Get visitor statistics
        $action = $_GET['action'] ?? 'stats';

        if ($action === 'stats') {
            // Overall statistics
            $stats = [];

            // Total visitors
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM visitor_log");
            $stats['total_visits'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Unique IPs
            $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) as unique FROM visitor_log");
            $stats['unique_visitors'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['unique'];

            // Active sessions (last 30 minutes)
            $stmt = $pdo->query("SELECT COUNT(DISTINCT session_id) as active FROM visitor_log WHERE visited_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
            $stats['active_sessions'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['active'];

            // Today's visits
            $stmt = $pdo->query("SELECT COUNT(*) as today FROM visitor_log WHERE DATE(visited_at) = CURDATE()");
            $stats['today_visits'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['today'];

            // This week's visits
            $stmt = $pdo->query("SELECT COUNT(*) as week FROM visitor_log WHERE visited_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['week_visits'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['week'];

            // Top users
            $stmt = $pdo->query("
                SELECT username, COUNT(*) as visit_count
                FROM visitor_log
                WHERE username != 'anonymous'
                GROUP BY username
                ORDER BY visit_count DESC
                LIMIT 10
            ");
            $stats['top_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'stats' => $stats]);

        } elseif ($action === 'recent') {
            // Recent access log
            $limit = (int)($_GET['limit'] ?? 50);

            $stmt = $pdo->prepare("
                SELECT id, ip_address, user_agent, username, page_url, visited_at
                FROM visitor_log
                ORDER BY visited_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $log = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'log' => $log]);

        } else {
            echo json_encode(['error' => 'Invalid action']);
        }

    } elseif ($method === 'DELETE') {
        // Clear old visitor logs (older than 90 days)
        $stmt = $pdo->query("DELETE FROM visitor_log WHERE visited_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
        $deleted = $stmt->rowCount();

        echo json_encode([
            'success' => true,
            'message' => "Deleted $deleted old log entries"
        ]);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
