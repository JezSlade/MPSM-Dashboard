<?php
declare(strict_types=1);

// ─── DEBUG BLOCK ─────────────────────────────────────────────
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);
ini_set('error_log', "$logDir/debug.log");
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('log_errors','1');
// ──────────────────────────────────────────────────────────────

/**
 * Simple .env parser
 */
function load_env(string $path): array {
    $data = is_readable($path) ? file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) : [];
    $out  = [];
    foreach ($data as $line) {
        if (trim($line)==='' || $line[0]==='#') continue;
        [$k,$v] = array_map('trim', explode('=', $line, 2));
        $out[$k] = $v;
    }
    return $out;
}

/**
 * Fetch an OAuth token
 */
function get_token(array $env): string {
    $body = http_build_query([
        'grant_type'=>'password',
        'username'=>$env['USERNAME']        ?? '',
        'password'=>$env['PASSWORD']        ?? '',
        'client_id'=>$env['CLIENT_ID']      ?? '',
        'client_secret'=>$env['CLIENT_SECRET']?? '',
        'scope'=>$env['SCOPE']              ?? 'account',
    ]);
    $ch = curl_init($env['TOKEN_URL'] ?? '');
    curl_setopt_array($ch, [
        CURLOPT_POST        => true,
        CURLOPT_POSTFIELDS  => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT     => 10,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    $j   = $raw ? json_decode($raw, true) : null;
    return $j['access_token'] ?? '';
}

/**
 * Call a protected endpoint with JSON body or GET query
 *
 * @param string $method  'GET' or 'POST'
 * @param string $path    e.g. '/CustomerDashboard/Devices'
 * @param array  $payload for POST use ['key'=>value], for GET it's query params
 */
function api_call(array $env, string $method, string $path, array $payload = []): array {
    $base  = rtrim($env['API_BASE_URL'] ?? '', '/');
    $token = get_token($env);
    if (!$token) {
        error_log("[api_client] Could not fetch token");
        return ['IsValid'=>false,'Errors'=>[['Code'=>'Auth','Description'=>'Token failed']]];
    }

    if (strtoupper($method) === 'GET') {
        $url = $base . $path . '?' . http_build_query($payload);
        error_log("[api_client] GET $url");
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token
        ]);
    } else {
        $url = $base . $path;
        $json = json_encode($payload);
        error_log("[api_client] POST $url payload: $json");
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);

    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        error_log("[api_client] Curl error: $err");
        return ['IsValid'=>false,'Errors'=>[['Code'=>'Curl','Description'=>$err]]];
    }

    $resp = json_decode($raw, true);
    if ($resp === null) {
        error_log("[api_client] JSON decode error: $raw");
        return ['IsValid'=>false,'Errors'=>[['Code'=>'JSON','Description'=>'Invalid JSON']]];
    }

    return $resp;
}
