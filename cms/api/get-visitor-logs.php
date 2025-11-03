<?php
/**
 * Get Visitor Logs with Filtering
 * Returns visitor log entries with comprehensive filtering and timezone support
 * Following Engineering Standards Rule 10: Keep Functions Short
 */

require '../config.php';
require '../functions.php';

requireAuth();

// Pagination
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit < 1 || $limit > 500) {
    $limit = 50;
}

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
if ($offset < 0) {
    $offset = 0;
}

// Filters
$username = $_GET['username'] ?? null;
$ipAddress = $_GET['ip_address'] ?? null;
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$pageUrl = $_GET['page_url'] ?? null;

try {
    $pdo = getDatabase();

    // Build WHERE clause
    $where = [];
    $params = [];

    if ($username) {
        $where[] = "username LIKE ?";
        $params[] = '%' . $username . '%';
    }

    if ($ipAddress) {
        $where[] = "ip_address = ?";
        $params[] = $ipAddress;
    }

    if ($startDate) {
        $where[] = "visited_at >= ?";
        $params[] = $startDate;
    }

    if ($endDate) {
        $where[] = "visited_at <= ?";
        $params[] = $endDate . ' 23:59:59';
    }

    if ($pageUrl) {
        $where[] = "page_url LIKE ?";
        $params[] = '%' . $pageUrl . '%';
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "visitor_log {$whereClause}";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get logs with pagination
    $sql = "
        SELECT
            id,
            user_id,
            username,
            ip_address,
            user_agent,
            page_url,
            visited_at,
            DATE_FORMAT(visited_at, '%Y-%m-%d %H:%i:%s') as formatted_time
        FROM " . DB_PREFIX . "visitor_log
        {$whereClause}
        ORDER BY visited_at DESC
        LIMIT ? OFFSET ?
    ";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unique visitors
    $uniqueSql = "SELECT COUNT(DISTINCT username) as unique_users FROM " . DB_PREFIX . "visitor_log";
    $uniqueStmt = $pdo->query($uniqueSql);
    $uniqueUsers = (int)$uniqueStmt->fetch(PDO::FETCH_ASSOC)['unique_users'];

    // Get unique IPs
    $uniqueIpSql = "SELECT COUNT(DISTINCT ip_address) as unique_ips FROM " . DB_PREFIX . "visitor_log";
    $uniqueIpStmt = $pdo->query($uniqueIpSql);
    $uniqueIps = (int)$uniqueIpStmt->fetch(PDO::FETCH_ASSOC)['unique_ips'];

    // Get most recent visit
    $recentSql = "SELECT MAX(visited_at) as last_visit FROM " . DB_PREFIX . "visitor_log";
    $recentStmt = $pdo->query($recentSql);
    $lastVisit = $recentStmt->fetch(PDO::FETCH_ASSOC)['last_visit'];

    jsonSuccess([
        'count' => count($logs),
        'total' => $totalCount,
        'limit' => $limit,
        'offset' => $offset,
        'has_more' => ($offset + $limit) < $totalCount,
        'timezone' => 'America/New_York (Eastern)',
        'stats' => [
            'unique_users' => $uniqueUsers,
            'unique_ips' => $uniqueIps,
            'last_visit' => $lastVisit,
            'total_visits' => $totalCount
        ],
        'filters_applied' => [
            'username' => $username,
            'ip_address' => $ipAddress,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'page_url' => $pageUrl
        ],
        'logs' => $logs
    ]);

} catch (Exception $e) {
    jsonError('Failed to retrieve visitor logs: ' . $e->getMessage());
}
