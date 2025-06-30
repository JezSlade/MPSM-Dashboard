<?php
// Load environment and headers
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// OpenAPI-like schema (for developer integration)
/*
{
  "description": "Returns a list of customers accessible by the authenticated user",
  "method": "GET",
  "endpoint": "/api/customer/list.php",
  "auth": "Bearer token required in Authorization header or via session",
  "response": {
    "type": "array",
    "items": {
      "id": "int",
      "name": "string",
      "code": "string",
      "dealerCode": "string"
    }
  },
  "example": [
    {
      "id": 123,
      "name": "ABC Corp",
      "code": "ABC001",
      "dealerCode": "D123"
    }
  ]
}
*/

$accessToken = $_SESSION['access_token'] ?? null;

// Optional: Allow token via Authorization header for frontend calls
if (!$accessToken && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
        $accessToken = $matches[1];
    }
}

if (!$accessToken) {
    http_response_code(401);
    echo json_encode(['error' => 'Access token missing']);
    exit;
}

$apiUrl = rtrim(getenv('API_BASE_URL') ?: API_BASE_URL, '/') . '/Customer/List';

$curl = curl_init($apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode >= 200 && $httpCode < 300) {
    echo $response;
} else {
    http_response_code($httpCode);
    echo json_encode(['error' => 'Failed to retrieve customers', 'status' => $httpCode]);
}
