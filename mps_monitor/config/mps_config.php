<?php

require_once __DIR__ . '/../config/mps_config.php';
require_once __DIR__ . '/../helpers/CacheHelper.php';

$curl = curl_init();

$payload = http_build_query([
  'grant_type'    => 'client_credentials',
  'client_id'     => CLIENT_ID,
  'client_secret' => CLIENT_SECRET,
  'scope'         => $_ENV['SCOPE'] ?? 'account'
]);

$logFile = '/tmp/token_trace.log';
file_put_contents($logFile, "\n=== TOKEN REQUEST TRACE ===\n", FILE_APPEND);
file_put_contents($logFile, "URL: " . API_BASE_URL . "/Token\n", FILE_APPEND);
file_put_contents($logFile, "Payload: $payload\n", FILE_APPEND);
file_put_contents($logFile, "ENV: " . json_encode($_ENV) . "\n", FILE_APPEND);

curl_setopt_array($curl, [
  CURLOPT_URL            => API_BASE_URL . '/Token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING       => '',
  CURLOPT_MAXREDIRS      => 10,
  CURLOPT_TIMEOUT        => 30,
  CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST  => 'POST',
  CURLOPT_POSTFIELDS     => $payload,
  CURLOPT_HTTPHEADER     => [
    'Content-Type: application/x-www-form-urlencoded'
  ],
]);

$response = curl_exec($curl);
$err      = curl_error($curl);
$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

curl_close($curl);

file_put_contents($logFile, "HTTP Code: $httpcode\n", FILE_APPEND);
file_put_contents($logFile, "Curl Error: $err\n", FILE_APPEND);
file_put_contents($logFile, "Response: $response\n", FILE_APPEND);

if ($err) {
  http_response_code(500);
  echo json_encode(['error' => 'Curl Error', 'message' => $err]);
  exit;
}

if ($httpcode >= 400) {
  http_response_code($httpcode);
  echo json_encode(['error' => 'API Error', 'status' => $httpcode, 'response' => $response]);
  exit;
}

header('Content-Type: application/json');
echo $response;
