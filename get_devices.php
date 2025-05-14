<?php
require_once 'working_token.php';

$token = get_token();
$apiUrl = 'https://api.abassetmanagement.com/api3/CustomerDashboard/Devices';

$headers = [
  "Authorization: Bearer $token",
  "Content-Type: application/json"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

if (curl_errno($ch)) {
  http_response_code(500);
  echo json_encode(["error" => curl_error($ch)]);
  exit;
}

curl_close($ch);
$devices = json_decode($response, true);

$simpleList = array_map(function ($device) {
  return [
    'AssetNumber' => $device['AssetNumber'] ?? '',
    'IpAddress'   => $device['IpAddress'] ?? '',
    'Model'       => $device['Model'] ?? ''
  ];
}, $devices);

header('Content-Type: application/json');
echo json_encode($simpleList);
