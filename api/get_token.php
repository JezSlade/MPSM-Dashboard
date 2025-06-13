<?php
// api/get_token.php
header('Content-Type: application/json');

$envFile = __DIR__ . '/../.env';
$env = [];
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($key, $val) = explode('=', $line, 2);
        $env[trim($key)] = trim($val);
    }
}

// Validate required keys
$requiredKeys = ['CLIENT_ID', 'CLIENT_SECRET', 'USERNAME', 'PASSWORD', 'SCOPE', 'TOKEN_URL'];
foreach ($requiredKeys as $key) {
    if (empty($env[$key])) {
        http_response_code(500);
        echo json_encode(['error' => "Missing environment variable: $key"]);
        exit;
    }
}

$payload = http_build_query([
    'grant_type' => 'password',
    'client_id' => $env['CLIENT_ID'],
    'client_secret' => $env['CLIENT_SECRET'],
    'username' => $env['USERNAME'],
    'password' => $env['PASSWORD'],
    'scope' => $env['SCOPE']
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $env['TOKEN_URL']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => curl_error($ch)]);
} elseif (strpos($contentType, 'application/json') === false) {
    http_response_code($httpCode);
    echo json_encode(['error' => 'Unexpected content type', 'body' => $response]);
} else {
    http_response_code($httpCode);
    echo $response;
}
curl_close($ch);
