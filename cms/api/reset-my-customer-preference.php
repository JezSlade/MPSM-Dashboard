<?php
/**
 * Reset Customer Preference to Default
 * Emergency fix for customer name/code mismatch
 *
 * Usage: Visit this URL to reset YOUR customer preference to Cape Fear default
 */

require '../config.php';
require '../functions.php';

requireAuth();

header('Content-Type: text/plain; charset=utf-8');

$userId = $_SESSION['user_id'];

try {
    // Reset to default Cape Fear customer
    $preferences = [
        'customerCode' => DEFAULT_CUSTOMER_CODE,
        'customerName' => DEFAULT_CUSTOMER_NAME
    ];

    saveUserPreferences($userId, $preferences);

    echo "✓ SUCCESS: Customer preference reset to default\n\n";
    echo "Customer Code: " . DEFAULT_CUSTOMER_CODE . "\n";
    echo "Customer Name: " . DEFAULT_CUSTOMER_NAME . "\n\n";
    echo "Please refresh your dashboard (Ctrl+Shift+R) to see the changes.\n";

} catch (Exception $e) {
    http_response_code(500);
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
