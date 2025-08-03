<?php

require_once __DIR__ . '/../config/mps_config.php';
require_once __DIR__ . '/../helpers/CacheHelper.php';

$curl = curl_init();

$payload = http_build_query([
  'grant_type' => 'password',
  'username'   => USERNAME,
  'password'   => PASSWORD,
]);

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
