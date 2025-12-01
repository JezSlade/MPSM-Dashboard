<?php
/**
 * Check which notifications are expired vs active
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== NOTIFICATION EXPIRATION CHECK ===\n\n";

// Check customer W9OPXL0YDK specifically
$customerCode = 'W9OPXL0YDK';

$stmt = $pdo->prepare("
    SELECT id, device_serial, alert_code, status, priority,
           created_at_ny, expires_at_ny, rule_id,
           trigger_count, time_window_hours,
           CASE
               WHEN expires_at_ny IS NULL THEN 'never'
               WHEN expires_at_ny < NOW() THEN 'expired'
               ELSE 'valid'
           END as expiration_status
    FROM " . DB_PREFIX . "dashboard_notifications
    WHERE customer_code = :customer
    ORDER BY priority DESC, created_at_ny DESC
");

$stmt->execute([':customer' => $customerCode]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Customer: {$customerCode}\n";
echo "Total notifications in DB: " . count($notifications) . "\n\n";

$counts = [
    'active_valid' => 0,
    'active_expired' => 0,
    'active_no_expiry' => 0,
    'dismissed' => 0,
    'acknowledged' => 0
];

foreach ($notifications as $notif) {
    if ($notif['status'] === 'active') {
        if ($notif['expiration_status'] === 'expired') {
            $counts['active_expired']++;
        } elseif ($notif['expiration_status'] === 'never') {
            $counts['active_no_expiry']++;
        } else {
            $counts['active_valid']++;
        }
    } elseif ($notif['status'] === 'dismissed') {
        $counts['dismissed']++;
    } elseif ($notif['status'] === 'acknowledged') {
        $counts['acknowledged']++;
    }
}

echo "=== STATUS BREAKDOWN ===\n";
echo "Active + Valid (not expired): {$counts['active_valid']}\n";
echo "Active + Expired: {$counts['active_expired']}\n";
echo "Active + No expiration: {$counts['active_no_expiry']}\n";
echo "Dismissed: {$counts['dismissed']}\n";
echo "Acknowledged: {$counts['acknowledged']}\n\n";

// Show details of active+valid notifications (what SHOULD display)
echo "=== ACTIVE & VALID NOTIFICATIONS (should display) ===\n";
foreach ($notifications as $notif) {
    if ($notif['status'] === 'active' && $notif['expiration_status'] === 'valid') {
        echo "ID {$notif['id']}: {$notif['device_serial']} / {$notif['alert_code']}\n";
        echo "  Priority: {$notif['priority']}\n";
        echo "  Created: {$notif['created_at_ny']}\n";
        echo "  Expires: {$notif['expires_at_ny']}\n";
        echo "  Rule: {$notif['rule_id']}\n\n";
    }
}

// Show active+expired (should NOT display but status is still 'active')
echo "\n=== ACTIVE BUT EXPIRED (bug - should be auto-dismissed) ===\n";
$expiredCount = 0;
foreach ($notifications as $notif) {
    if ($notif['status'] === 'active' && $notif['expiration_status'] === 'expired') {
        $expiredCount++;
        if ($expiredCount <= 10) {
            echo "ID {$notif['id']}: {$notif['device_serial']} / {$notif['alert_code']}\n";
            echo "  Created: {$notif['created_at_ny']}\n";
            echo "  Expired at: {$notif['expires_at_ny']}\n\n";
        }
    }
}
if ($expiredCount > 10) {
    echo "... and " . ($expiredCount - 10) . " more expired notifications\n";
}

// Check API query (what dashboard actually sees)
echo "\n=== WHAT DASHBOARD API RETURNS ===\n";

$stmt = $pdo->prepare("
    SELECT id, device_serial, alert_code, priority, created_at_ny
    FROM " . DB_PREFIX . "dashboard_notifications
    WHERE status = 'active'
    AND (customer_code = :customer OR customer_code IS NULL OR customer_code = '')
    ORDER BY priority DESC, created_at_ny DESC
    LIMIT 6
");

$stmt->execute([':customer' => $customerCode]);
$apiResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Dashboard API returns (top 6 by priority): " . count($apiResults) . " notifications\n\n";

foreach ($apiResults as $notif) {
    // Check if expired
    $checkStmt = $pdo->prepare("SELECT expires_at_ny FROM " . DB_PREFIX . "dashboard_notifications WHERE id = :id");
    $checkStmt->execute([':id' => $notif['id']]);
    $expiry = $checkStmt->fetch(PDO::FETCH_ASSOC)['expires_at_ny'];

    $expiredFlag = '';
    if ($expiry && strtotime($expiry) < time()) {
        $expiredFlag = ' [EXPIRED]';
    }

    echo "ID {$notif['id']}: {$notif['device_serial']} / {$notif['alert_code']}{$expiredFlag}\n";
    echo "  Priority: {$notif['priority']}\n";
    echo "  Created: {$notif['created_at_ny']}\n";
}

echo "\n=== DIAGNOSIS ===\n";

if ($counts['active_expired'] > 0) {
    echo "❌ BUG FOUND: {$counts['active_expired']} notifications have status='active' but are expired\n";
    echo "   The API returns them because it only checks status='active'\n";
    echo "   But they SHOULD have been auto-dismissed when they expired\n\n";

    echo "SOLUTION:\n";
    echo "1. Add expiration check to API query:\n";
    echo "   WHERE status = 'active' AND (expires_at_ny IS NULL OR expires_at_ny > NOW())\n";
    echo "2. Or run cleanup job to set status='expired' on old notifications\n";
}

if ($counts['active_valid'] === 0 && $counts['active_no_expiry'] === 0) {
    echo "❌ NO VALID NOTIFICATIONS: All notifications for this customer have expired\n";
    echo "   Need to create new notifications from recent alerts\n";
}
