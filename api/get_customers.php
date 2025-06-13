<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

// Load .env manually (no includes)
function load_env($path = __DIR__ . '/../.env') {
    if (!file_exists($path)) {
        http_response_code(500);
        echo json_encode(["error" => ".env file not found"]);
        exit;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $val] = explode('=', $line, 2);
        $env[trim($key)] = trim($val);
    }
    return $env;
}

function get_token($env) {
    $required = ['CLIENT_ID', 'CLIENT_SECRET', 'USERNAME', 'PASSWORD', 'SCOPE', 'TOKEN_URL'];
    foreach ($required as $key) {
        if (empty($env[$key])) {
            echo json_encode(["error" => "Missing $key in .env"]);
            exit;
        }
    }

    $postFields = http_build_query([
        'grant_type'    => 'password',
        'client_id'     => $env['CLIENT_ID'],
        'client_secret' => $env['CLIENT_SECRET'],
        'username'      => $env['USERNAME'],
        'password'      => $env['PASSWORD'],
        'scope'         => $env['SCOPE']
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $env['TOKEN_URL']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($response, true);
    if ($code !== 200 || !isset($json['access_token'])) {
        echo json_encode(["error" => "Token request failed", "details" => $json]);
        exit;
    }

    return $json['access_token'];
}

// Main logic
header('Content-Type: application/json');

$env = load_env();
$token = get_token($env);

$api_url = rtrim($env['API_BASE_URL'], '/') . '/Customer/GetCustomers';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json",
    "Accept: application/json"
]);

// No payload needed for this raw call
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'DealerCode'  => $env['DEALER_CODE'],
    'Code'        => null,
    'HasHpSds'    => null,
    'FilterText'  => null,
    'PageNumber'  => 1,
    'PageRows'    => 2147483647,
    'SortColumn'  => 'Id',
    'SortOrder'   => 0
]));



$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($code);
echo $response;
?>
