<?php
declare(strict_types=1);

// ───────────────────────────────────────────────
// DEBUG BLOCK (Always Keep at Top)
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) { mkdir($logDir, 0755, true); }
ini_set('error_log', $logDir . '/debug.log');
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('log_errors','1');
// ───────────────────────────────────────────────

/**
 * POST /api/get_device_details.php
 * Proxies to upstream POST /Device/Get
 * Request body: { "DeviceId": "<string>" }
 * Response: PagedResultResponse[DeviceDetailsDto]
 */

// helper to parse .env
function parse_env(string $path): array {
    if (!is_readable($path)) return [];
    $lines = file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $ln) {
        if ($ln[0]==='#') continue;
        [$k,$v] = array_map('trim', explode('=', $ln, 2));
        $env[$k] = $v;
    }
    return $env;
}
// helper to get OAuth token
function get_token(array $env): string {
    $body = http_build_query([
        'grant_type'=>'password',
        'username'=>$env['USERNAME']??'',
        'password'=>$env['PASSWORD']??'',
        'client_id'=>$env['CLIENT_ID']??'',
        'client_secret'=>$env['CLIENT_SECRET']??'',
        'scope'=>$env['SCOPE']??'account'
    ]);
    $ch = curl_init($env['TOKEN_URL'] ?? '');
    curl_setopt_array($ch, [
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$body,
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_TIMEOUT=>10
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    $json = $raw ? json_decode($raw,true) : null;
    return $json['access_token'] ?? '';
}

// 1) load env & get token
$env = parse_env(__DIR__.'/../.env');
$token = get_token($env);
if (!$token) {
    http_response_code(500);
    echo json_encode(['IsValid'=>false,'Errors'=>[['Code'=>'Auth','Description'=>'No token']]]);
    exit;
}

// 2) read input
$input = json_decode(file_get_contents('php://input'), true) ?: [];
if (empty($input['DeviceId'])) {
    http_response_code(400);
    echo json_encode(['IsValid'=>false,'Errors'=>[['Code'=>'Missing','Description'=>'DeviceId required']]]);
    exit;
}

// 3) call upstream
$url = rtrim($env['API_BASE_URL']??'', '/') . '/Device/Get';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST=>true,
    CURLOPT_HTTPHEADER=>[
        'Content-Type: application/json',
        'Authorization: Bearer '.$token
    ],
    CURLOPT_POSTFIELDS=>json_encode($input),
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_TIMEOUT=>15
]);
$raw = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($raw === false) {
    http_response_code(502);
    echo json_encode(['IsValid'=>false,'Errors'=>[['Code'=>'Curl','Description'=>$err]]]);
    exit;
}

// 4) forward upstream response
header('Content-Type: application/json');
echo $raw;
