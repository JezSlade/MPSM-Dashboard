<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'ApiClient.php';

try {
    $config = include 'config.php';
    $apiClient = new ApiClient($config);
    // Placeholder deviceId (replace with actual logic in future)
    $deviceId = '12345';
    $data = $apiClient->fetch("AlertLimit2/Device/GetDefault?id=$deviceId", 'GET');

    // Validate ListResultResponse[AlertLimit2DeviceDto]
    if (isset($data['IsValid']) && $data['IsValid'] && !empty($data['Result'])) {
        // Extract necessary fields for frontend
        $devices = array_map(function ($device) {
            return [
                'Id' => $device['Id'] ?? '',
                'SupplyType' => [
                    'Description' => $device['SupplyType']['Description'] ?? 'N/A'
                ],
                'ColorType' => [
                    'Description' => $device['ColorType']['Description'] ?? 'N/A'
                ]
            ];
        }, $data['Result']);
        echo json_encode(['success' => true, 'devices' => $devices]);
    } else {
        $error = $data['ErrorMessage'] ?? 'No device status available';
        echo json_encode(['success' => false, 'error' => $error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}