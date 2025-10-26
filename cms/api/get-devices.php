<?php
/**
 * Get Devices API
 * Fetches devices from MPS API for a customer
 */

require '../config.php';
require '../functions.php';

requireAuth();

$customerCode = $_GET['customerCode'] ?? DEFAULT_CUSTOMER_CODE;
$customerId = $_GET['customerId'] ?? DEFAULT_CUSTOMER_ID;

try {
    $devices = callMPSAPI('Device/List', [
        'FilterDealerId' => DEFAULT_DEALER_ID,
        'FilterCustomerCode' => $customerCode,
        'FilterCustomerId' => $customerId,
        'pageNumber' => 1,
        'pageRows' => 100
    ]);

    jsonSuccess(['devices' => $devices]);

} catch (Exception $e) {
    jsonError("Failed to fetch devices: " . $e->getMessage());
}
