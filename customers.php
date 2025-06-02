<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'ApiClient.php';

try {
    $config = include 'config.php';
    $apiClient = new ApiClient($config);
    $data = $apiClient->fetch('Customer/GetCustomers', 'GET');

    // Validate ListResultResponse[CustomerDto]
    if (isset($data['IsValid']) && $data['IsValid'] && !empty($data['Result'])) {
        // Extract only necessary fields for frontend
        $customers = array_map(function ($customer) {
            return [
                'Id' => $customer['Id'] ?? '',
                'CustomerDescription' => $customer['CustomerDescription'] ?? 'Unknown Customer'
            ];
        }, $data['Result']);
        echo json_encode(['success' => true, 'customers' => $customers]);
    } else {
        $error = $data['ErrorMessage'] ?? 'No customers found';
        echo json_encode(['success' => false, 'error' => $error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}