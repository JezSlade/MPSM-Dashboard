<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors',   '1');
ini_set('error_log',    __DIR__ . '/../logs/debug.log');
// ----------------------------------------

// Load .env into an associative array
if (!function_exists('load_env')) {
    function load_env(string $path = __DIR__ . '/../.env'): array {
        if (!file_exists($path)) {
            return [];
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            [$key, $val] = explode('=', $line, 2);
            $env[trim($key)] = trim($val);
        }
        return $env;
    }
}

// Fetch OAuth2 token via password grant
if (!function_exists('get_token')) {
    function get_token(array $env): string {
        // Ensure all required .env keys are present
        $required = ['CLIENT_ID', 'CLIENT_SECRET', 'USERNAME', 'PASSWORD', 'SCOPE', 'TOKEN_URL'];
        foreach ($required as $key) {
            if (empty($env[$key])) {
                echo json_encode(['error' => "Missing $key in .env"]);
                exit;
            }
        }

        // Build request body
        $postFields = http_build_query([
            'grant_type'    => 'password',
            'client_id'     => $env['CLIENT_ID'],
            'client_secret' => $env['CLIENT_SECRET'],
            'username'      => $env['USERNAME'],
            'password'      => $env['PASSWORD'],
            'scope'         => $env['SCOPE'],
        ]);

        // cURL POST to token endpoint
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $env['TOKEN_URL']);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);
        if ($httpCode !== 200 || empty($json['access_token'])) {
            echo json_encode([
                'error'   => 'Token request failed',
                'details' => $json
            ]);
            exit;
        }

        return $json['access_token'];
    }
}

// Load environment and fetch token once for any script that includes this file
$env   = load_env();
$token = get_token($env);

// Define global constants
define('API_BASE_URL', $env['API_BASE_URL']  ?? '');
define('APP_BASE_URL', $env['APP_BASE_URL']  ?? '/');
define('APP_NAME',     $env['APP_NAME']      ?? 'App');
define('APP_VERSION',  $env['APP_VERSION']   ?? '0.0.1');

// Simple view renderer for your SPA
function render_view(string $path): void {
    if (file_exists($path)) {
        include $path;
    } else {
        http_response_code(500);
        echo "<p>View not found: $path</p>";
    }
}
