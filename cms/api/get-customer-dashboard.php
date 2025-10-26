<?php
/**
 * Get Customer Dashboard Data
 */

require '../config.php';
require '../functions.php';

requireAuth();

$customerCode = $_GET['customerCode'] ?? DEFAULT_CUSTOMER_CODE;

try {
    $dashboard = callMPSAPI('Dashboard/Customer', [
        'customerCode' => $customerCode
    ]);

    jsonSuccess(['dashboard' => $dashboard]);

} catch (Exception $e) {
    jsonError("Failed to fetch dashboard: " . $e->getMessage());
}
