<?php
/**
 * Check what customer code is in the session
 * This determines what notifications the dashboard will show
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

// Start session to check what customer is selected
session_start();

header('Content-Type: text/plain; charset=utf-8');

echo "=== CUSTOMER SESSION DIAGNOSTIC ===\n\n";

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    echo "❌ NOT LOGGED IN\n";
    echo "Session variables:\n";
    print_r($_SESSION);
    echo "\nTo test properly, you must be logged in.\n";
    exit;
}

echo "✓ User logged in: {$_SESSION['user_email']}\n\n";

// Check customer code in session
$sessionCustomerCode = $_SESSION['customer_code'] ?? null;
$sessionCustomerName = $_SESSION['customer_name'] ?? null;

echo "=== SESSION STATE ===\n";
echo "Customer Code: " . ($sessionCustomerCode ?? 'NOT SET') . "\n";
echo "Customer Name: " . ($sessionCustomerName ?? 'NOT SET') . "\n\n";

// Check what customers have active notifications
$pdo = getDatabase();

$stmt = $pdo->query("
    SELECT customer_code, COUNT(*) as count
    FROM " . DB_PREFIX . "dashboard_notifications
    WHERE status = 'active'
    GROUP BY customer_code
    ORDER BY count DESC
");

$customerCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== NOTIFICATIONS BY CUSTOMER ===\n";
foreach ($customerCounts as $row) {
    $code = $row['customer_code'] ?: '(empty)';
    $match = ($sessionCustomerCode && strtolower(trim($sessionCustomerCode)) === strtolower(trim($row['customer_code'])))
        ? ' ← MATCHES SESSION'
        : '';
    echo "{$code}: {$row['count']} notifications{$match}\n";
}

echo "\n=== DIAGNOSIS ===\n";

if (!$sessionCustomerCode) {
    echo "⚠️  No customer selected in session\n";
    echo "Dashboard should show ALL notifications (customer_code IS NULL filter)\n\n";
} else {
    // Check if session customer has any notifications
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM " . DB_PREFIX . "dashboard_notifications
        WHERE status = 'active'
        AND (
            LOWER(TRIM(customer_code)) = LOWER(TRIM(:customer_code))
            OR customer_code IS NULL
            OR customer_code = ''
        )
    ");
    $stmt->execute([':customer_code' => $sessionCustomerCode]);
    $matchCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo "Session customer code: {$sessionCustomerCode}\n";
    echo "Matching notifications: {$matchCount}\n\n";

    if ($matchCount === 0) {
        echo "❌ ROOT CAUSE FOUND!\n";
        echo "Session customer '{$sessionCustomerCode}' has ZERO notifications\n";
        echo "Dashboard filters notifications by customer_code\n";
        echo "No matches = empty dashboard display\n\n";

        echo "SOLUTION OPTIONS:\n";
        echo "1. Create notifications for customer '{$sessionCustomerCode}'\n";
        echo "2. Switch to a customer with notifications (see list above)\n";
        echo "3. Set customer_code = '{$sessionCustomerCode}' on existing notifications\n";
    } else {
        echo "✓ Customer has {$matchCount} matching notifications\n";
        echo "Dashboard should be showing them!\n\n";

        echo "If dashboard still not showing:\n";
        echo "1. Check browser console for JavaScript errors\n";
        echo "2. Verify hero-notifications.js is loaded\n";
        echo "3. Check API response directly:\n";
        echo "   https://mpsm.resolutionsbydesign.us/cms/api/command-center.php?action=get_notifications&status=active&customerCode={$sessionCustomerCode}\n";
    }
}
