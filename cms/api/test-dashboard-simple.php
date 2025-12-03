<?php
/**
 * Simple Dashboard Test - Check if APIs return data
 */
header('Content-Type: application/json');

// Test by making HTTP requests to the APIs (avoids include issues)
$baseUrl = 'https://mpsm.resolutionsbydesign.us/cms/api/';

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// Start session to get cookies
session_start();
$sessionId = session_id();

// Test 1: Dealer Summary
$ch = curl_init($baseUrl . 'get-dealer-summary.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . $sessionId);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
$result['tests']['dealer_summary'] = [
    'http_code' => $httpCode,
    'response_valid_json' => $data !== null,
    'success' => $data['success'] ?? false,
    'total_customers' => $data['summary']['totalCustomers'] ?? 0,
    'total_devices' => $data['summary']['totalDevices'] ?? 0,
    'data_source' => $data['summary']['_dataSource'] ?? 'unknown'
];

// Test 2: Customer Portfolio
$ch = curl_init($baseUrl . 'get-customer-portfolio.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . $sessionId);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
$result['tests']['customer_portfolio'] = [
    'http_code' => $httpCode,
    'response_valid_json' => $data !== null,
    'success' => $data['success'] ?? false,
    'customer_count' => isset($data['customers']) ? count($data['customers']) : 0
];

echo json_encode($result, JSON_PRETTY_PRINT);
