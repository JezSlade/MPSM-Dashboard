<?php
// get_device_details.php
// v1.1.0 [Live device detail lookup using DeviceId]
header('Content-Type: application/json');

function fail($msg, $extra = []) {
  http_response_code(500);
  echo json_encode(array_merge(['error' => $msg], $extra));
  exit;
}

$env = parse_ini_file(__DIR__ . '/.env');
$client_id = $env['CLIENT_ID'] ?? fail('Missing client_id');
$client_secret = $env['CLIENT_SECRET'] ?? fail('Missing client_secret');
$username = $env['USERNAME'] ?? fail('Missing username');
$password = $env['PASSWORD'] ?? fail('Missing password');

function getToken($client_id, $client_secret, $username, $password) {
  $url = 'https://api.abassetmanagement.com/api3/token';
  $data = http_build_query([
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'grant_type' => 'password',
    'username' => $username,
    'password' => $password,
    'scope' => 'account'
  ]);
  $opts = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
  ];
  $ch = curl_init($url);
  curl_setopt_array($ch, $opts);
  $response = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  $json = json_decode($response, true);
  if ($code >= 400 || !$json['access_token']) fail('Token error', ['http' => $code, 'body' => $response]);
  return $json['access_token'];
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$deviceId = $data['DeviceId'] ?? null;
if (!$deviceId) fail('Missing DeviceId');

$token = getToken($client_id, $client_secret, $username, $password);

$url = 'https://api.abassetmanagement.com/api3/Device/GetDetailedInformations';
$payload = json_encode([ 'DeviceId' => $deviceId ]);

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $payload,
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
  ]
]);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code >= 400) fail("API error", ['http' => $code, 'body' => $response]);
echo $response;
