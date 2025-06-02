<?php
// core/mps_api.php
// v1.0.0 [MPS Monitor API client]

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/debug.php';

function get_mps_token(): string {
    if (!empty($_SESSION['mps_token'])
     && !empty($_SESSION['mps_token_expires'])
     && $_SESSION['mps_token_expires'] > time()) {
        debug_log('Using cached MPS token', [], 'DEBUG');
        return $_SESSION['mps_token'];
    }

    debug_log('Requesting new MPS token', [], 'DEBUG');
    $post = http_build_query([
        'client_id'     => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'grant_type'    => 'password',
        'username'      => USERNAME,
        'password'      => PASSWORD,
        'scope'         => SCOPE,
    ]);

    $ch = curl_init(TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        debug_log('Curl error get_mps_token', ['err'=>$err], 'ERROR');
        curl_close($ch);
        throw new Exception('Failed to fetch MPS token');
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 400) {
        debug_log('MPS token API error', ['code'=>$code,'resp'=>$resp], 'ERROR');
        throw new Exception("MPS token endpoint returned HTTP $code");
    }

    $json = json_decode($resp, true);
    if (empty($json['access_token']) || empty($json['expires_in'])) {
        debug_log('Invalid MPS token response', ['resp'=>$resp], 'ERROR');
        throw new Exception('Invalid token response');
    }

    $_SESSION['mps_token']          = $json['access_token'];
    $_SESSION['mps_token_expires'] = time() + $json['expires_in'] - 60;
    return $json['access_token'];
}

function fetch_mps_api(string $endpoint, array $payload = []): array {
    $token = get_mps_token();
    $url   = rtrim(BASE_URL, '/') . '/' . ltrim($endpoint, '/');
    $ch    = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer $token",
            'Content-Type: application/json'
        ],
    ]);
    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
        debug_log('Curl error fetch_mps_api', ['err'=>curl_error($ch)], 'ERROR');
        curl_close($ch);
        throw new Exception('Failed MPS API call');
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 400) {
        debug_log('MPS API HTTP error', ['endpoint'=>$endpoint,'code'=>$code,'resp'=>$resp], 'ERROR');
        throw new Exception("MPS API returned HTTP $code");
    }
    $data = json_decode($resp, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        debug_log('Invalid JSON from MPS API', ['resp'=>$resp], 'ERROR');
        throw new Exception('Invalid JSON returned by MPS API');
    }
    return $data;
}
