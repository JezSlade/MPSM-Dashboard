<?php
/**
 * Get Customers API
 * Proxies Customer/GetCustomers for a given dealer. Uses the current user's
 * personal MPS Monitor login when saved, so switched dealers resolve.
 */
require '../config.php';
require '../functions.php';

requireAuth();

$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;

try {
    $data = queryMpsApi('Customer/GetCustomers', [
        'DealerCode' => $dealerCode,
        'PageNumber' => 1,
        'PageRows' => 500,
        'SortColumn' => 'Description',
        'SortOrder' => 'Asc'
    ], $_SESSION['user_id']);

    $customers = $data['data']['Items'] ?? $data['data'] ?? [];

    jsonSuccess([
        'customers' => $customers,
        'total' => count($customers)
    ]);
} catch (Exception $e) {
    jsonError('Failed to fetch customers: ' . $e->getMessage());
}

/*
CHANGELOG
2026-10-21 Claude
- Switched to shared queryMpsApi() helper so a saved personal MPS Monitor login
  is used for dealers the shared service account cannot access (dealer switcher).
*/
