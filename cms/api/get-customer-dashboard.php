<?php
/**
 * Get Customer Dashboard Data
 * Proxies request to mps-api backend
 * Following Engineering Standards: CMS = presentation, mps-api = API proxy
 */

require '../config.php';
require '../functions.php';

requireAuth();

$customerCode = $_GET['customerCode'] ?? DEFAULT_CUSTOMER_CODE;

try {
    // Call mps-api backend (handles OAuth, token caching, MPS API communication)
    $url = 'https://mpsm.resolutionsbydesign.us/mps-api/?action=GetCustomerDashboard&customerCode=' . urlencode($customerCode);

    $response = file_get_contents($url);
    if ($response === false) {
        throw new Exception("Failed to contact mps-api backend");
    }

    $data = json_decode($response, true);
    if (!$data) {
        throw new Exception("Invalid response from mps-api backend");
    }

    // Forward response from mps-api
    header('Content-Type: application/json');
    echo json_encode($data);

} catch (Exception $e) {
    jsonError("Failed to fetch dashboard: " . $e->getMessage());
}
