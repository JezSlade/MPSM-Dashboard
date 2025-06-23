<?php
declare(strict_types=1);

// ------------------------------------------------------------------
// DEBUG BLOCK (Always Keep at Top)
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);          // auto-create directory tree
}
$logFile = $logDir . '/debug.log';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', $logFile);
// ------------------------------------------------------------------

/**
 * get_devices.php  (SELF-CONTAINED)
 * ------------------------------------------------------------------
 * Proxies to upstream   POST  /Device/List
 * Requirements per MPSM standards:
 *   • No includes / external libs
 *   • Manual .env parsing in this file
 *   • Uses __DIR__ for path safety
 *   • PHP 8.4+ compatible, strict_types on
 */

// ───── Helper: parse .env into assoc array ───────────────────────
function parse_env_file(string $path): array
{
    if (!is_readable($path)) {
        return [];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env   = [];
    foreach ($lines as $ln) {
        if ($ln[0] === '#') continue;
        [$key, $val] = array_map('trim', explode('=', $ln, 2));
        $env[$key]   = $val;
    }
    return $env;
}

// ───── Helper: get OAuth token (client-cred flow) ────────────────
function get_token(array $env): string
{
    $tokenUrl = $env['TOKEN_URL'] ?? '';
    $body = http_build_query([
        'grant_type'    => 'password',
        'username'      => $env['USERNAME'] ?? '',
        'password'      => $env['PASSWORD'] ?? '',
        'client_id'     => $env['CLIENT_ID'] ?? '',
        'client_secret' => $env['CLIENT_SECRET'] ?? '',
        'scope'         => $env['SCOPE'] ?? 'account',
    ]);

    $ch = curl_init($tokenUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);

    $json = $raw ? json_decode($raw, true) : null;
    return $json['access_token'] ?? '';
}

// ───── 1) Load env + token ───────────────────────────────────────
$envPath = __DIR__ . '/../.env';
$env     = parse_env_file($envPath);
$token   = get_token($env);
if ($token === '') {
    http_response_code(500);
    echo json_encode(['IsValid'=>false,'Errors'=>[['Code'=>'Token','Description'=>'Unable to retrieve token']]]);
    exit;
}

// ───── 2) Read client JSON, ensure required fields ───────────────
$clientBody = json_decode(file_get_contents('php://input'), true) ?? [];
$clientBody['PageNumber']  = (int)($clientBody['PageNumber'] ?? 1);
$clientBody['PageRows']    = (int)($clientBody['PageRows']   ?? 15);
$clientBody['SortColumn']  = $clientBody['SortColumn'] ?? 'ExternalIdentifier';
$clientBody['SortOrder']   = $clientBody['SortOrder']  ?? 'Asc';

// If neither CustomerCode nor FilterDealerId present → fallback to dealer
if (empty($clientBody['CustomerCode']) && empty($clientBody['FilterDealerId'])) {
    $clientBody['FilterDealerId'] = $env['DEALER_ID'] ?? '';
}

// ───── 3) Forward to upstream /Device/List ───────────────────────
$apiUrl = rtrim($env['API_BASE_URL'] ?? '', '/') . '/Device/List';

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ],
    CURLOPT_POSTFIELDS     => json_encode($clientBody),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
]);
$raw = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($raw === false) {
    http_response_code(502);
    echo json_encode(['IsValid'=>false,'Errors'=>[['Code'=>'Curl','Description'=>$err]]]);
    exit;
}

header('Content-Type: application/json');
echo $raw;
