<?php
// token_handler.php
// Handles login and token refresh
require_once(__DIR__ . '/../config.php');

header('Content-Type: application/json');

// Read the post body parameters
function getRequestParams() {
    return (isset($_POST) ? $_POST : (array)[]);
}

// Required: grant_type
$params = getRequestParams();

if (!isset($params['grant_type'])) {
    http_response_code(400);
    echo json_encode(['error' => "Missing grant_type parameter"]);
    exit;
}

if ($params['grant_type'] === 'password') {
    $data = 'username=' . $params['username'] . '&password=' . $params['password'] . '&grant_type=password';
} elseif ($params['grant_type'] === 'refresh_token') {
    $data = 'refresh_token=' . $params['refresh_token'] . '&grant_type=refresh_token';
} else {
    http_response_code(400);
    echo json_encode(['error' => "Invalid grant_type"]);
    exit;
}

// Send to token endpoint
$ch_url = API_BASE_URL . '/Token';

$curl = curl_init($ch_url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded'
));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FRESH_CONNECT , true);
curl_setopt($curl, CURLOPT_VERIFYHOST, false);

$resp = curl_exec($curl);
$status = curl_getinfo($curl);
curl_close($curl);

if ($status) {
    echo $resp;
} else {
    http_response_code(500);
    echo json_encode(['error' => "token request failed"]);
}
