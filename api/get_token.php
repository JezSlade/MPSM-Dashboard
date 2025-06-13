<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

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

header('Content-Type: application/json');

$env = load_env();

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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(["error" => curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);
http_response_code($httpCode);
echo $response;
?>
