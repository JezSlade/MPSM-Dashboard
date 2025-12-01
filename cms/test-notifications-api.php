<?php
/**
 * TEST: Notifications API endpoint (what dashboard uses)
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== NOTIFICATIONS API TEST ===\n\n";

// Test 1: Raw query (what API should return)
echo "TEST 1: Raw Database Query\n";
echo str_repeat("-", 50) . "\n";

$sql = "SELECT n.*, d.display_name as alert_display_name
        FROM " . DB_PREFIX . "dashboard_notifications n
        LEFT JOIN " . DB_PREFIX . "alert_definitions d ON n.alert_code = d.alert_code
        WHERE n.status = 'active'
        ORDER BY n.priority DESC, n.created_at_ny DESC
        LIMIT 10";

$stmt = $pdo->query($sql);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found: " . count($notifications) . " active notifications\n\n";

if (empty($notifications)) {
    echo "❌ NO ACTIVE NOTIFICATIONS IN DATABASE\n";
    echo "This is the problem - dashboard has nothing to display\n\n";

    // Check if they exist but with wrong status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM " . DB_PREFIX . "dashboard_notifications GROUP BY status");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Notifications by status:\n";
    foreach ($statuses as $s) {
        echo "  {$s['status']}: {$s['count']}\n";
    }
} else {
    foreach ($notifications as $notif) {
        echo "ID {$notif['id']}:\n";
        echo "  Device: {$notif['device_serial']}\n";
        echo "  Alert: {$notif['alert_code']} ({$notif['alert_display_name']})\n";
        echo "  Customer: {$notif['customer_code']}\n";
        echo "  Title: {$notif['title']}\n";
        echo "  Severity: {$notif['severity']}\n";
        echo "  Status: {$notif['status']}\n";
        echo "  Priority: {$notif['priority']}\n";
        echo "  Created: {$notif['created_at_ny']}\n\n";
    }
}

// Test 2: Check customer filter
echo "\nTEST 2: Customer Filter Check\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("
    SELECT DISTINCT customer_code, COUNT(*) as count
    FROM " . DB_PREFIX . "dashboard_notifications
    WHERE status = 'active'
    GROUP BY customer_code
");

$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Active notifications by customer:\n";
foreach ($customers as $c) {
    echo "  {$c['customer_code']}: {$c['count']} notifications\n";
}

// Test 3: Simulate API call
echo "\n\nTEST 3: Simulate API Response\n";
echo str_repeat("-", 50) . "\n";

// This is what command-center.php?action=get_notifications returns
$apiSql = "SELECT n.*,
                  d.display_name as alert_display_name,
                  d.description as alert_description,
                  d.category as alert_category
           FROM " . DB_PREFIX . "dashboard_notifications n
           LEFT JOIN " . DB_PREFIX . "alert_definitions d ON n.alert_code = d.alert_code
           WHERE n.status = :status
           ORDER BY n.priority DESC, n.created_at_ny DESC
           LIMIT 50";

$stmt = $pdo->prepare($apiSql);
$stmt->execute([':status' => 'active']);
$apiResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "API would return: " . count($apiResult) . " notifications\n";

if (empty($apiResult)) {
    echo "\n❌ PROBLEM: API returns empty array\n";
    echo "Dashboard hero-notifications.js will show 'All clear' message\n";
} else {
    echo "\n✓ API returns data:\n";
    foreach (array_slice($apiResult, 0, 3) as $item) {
        echo "  - {$item['device_serial']} / {$item['alert_code']}: {$item['title']}\n";
    }
}

// Test 4: Check if notifications expired
echo "\n\nTEST 4: Expiration Check\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("
    SELECT id, device_serial, alert_code, status, created_at_ny, expires_at_ny
    FROM " . DB_PREFIX . "dashboard_notifications
    WHERE id >= 9386
    ORDER BY id
");

$allNotifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($allNotifs as $n) {
    $expired = $n['expires_at_ny'] && strtotime($n['expires_at_ny']) < time();
    $statusIcon = $n['status'] === 'active' ? '✓' : '✗';
    $expIcon = $expired ? '⏰ EXPIRED' : '';

    echo "{$statusIcon} ID {$n['id']}: {$n['device_serial']} / {$n['alert_code']}\n";
    echo "    Status: {$n['status']} {$expIcon}\n";
    echo "    Created: {$n['created_at_ny']}\n";
    echo "    Expires: {$n['expires_at_ny']}\n\n";
}

// DIAGNOSIS
echo "\n" . str_repeat("=", 50) . "\n";
echo "DIAGNOSIS\n";
echo str_repeat("=", 50) . "\n\n";

if (count($notifications) === 0) {
    echo "❌ ROOT CAUSE: No active notifications in database\n\n";

    echo "Possible reasons:\n";
    echo "1. Notifications were created but status != 'active'\n";
    echo "2. Notifications expired (expires_at_ny < NOW())\n";
    echo "3. Notifications were dismissed\n";
    echo "4. Something deleted/updated them\n\n";

    echo "Check output above for:\n";
    echo "- 'Notifications by status' breakdown\n";
    echo "- Individual notification status in Test 4\n";
} else {
    echo "✓ Notifications exist in database\n";
    echo "✓ API would return data\n\n";

    echo "If dashboard still not showing them, check:\n";
    echo "1. Customer filter mismatch\n";
    echo "2. JavaScript errors in browser console\n";
    echo "3. hero-notifications.js not loading\n";
    echo "4. API authentication failing\n";
}
