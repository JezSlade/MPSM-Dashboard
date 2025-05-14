<?php
// get_token.php
header('Content-Type: application/json');

$env = parse_ini_file(__DIR__ . '/.env');

$client_id     = $env['CLIENT_ID'] ?? null;
$client_secret = $env['CLIENT_SECRET'] ?? null;
$username      = $env['USERNAME'] ?? null;
$password      = $env['PASSWORD'] ?? null;

if (!$client_id || !$client_secret || !$username || !$password) {
    http_response_code(500);
    echo json_encode(['error' => 'Missing credentials']);
    exit;
}

$url = 'https://api.abassetmanagement.com/api3/token';
$data = http_build_query([
    'client_id'     => $client_id,
    'client_secret' => $client_secret,
    'grant_type'    => 'password',
    'username'      => $username,
    'password'      => $password,
    'scope'         => 'account',
]);

$opts = [
    "http" => [
        "method" => "POST",
        "header" => "Content-Type: application/x-www-form-urlencoded\r\nCache-Control: no-cache",
        "content" => $data
    ]
];

$context = stream_context_create($opts);
$result = file_get_contents($url, false, $context);

echo $result ?: json_encode(['error' => 'No response']);
