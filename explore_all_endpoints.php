<?php
/**
 * MPSM Endpoint Explorer (No Composer, Matches Your .env)
 * 
 * - Uses your .env variable names
 * - Parses .env manually
 * - Explores all endpoints
 * - Displays debug output if token fails
 */

// ─────────────────────────────────────────────────────────────
// Load .env file manually (no Composer)
// ─────────────────────────────────────────────────────────────
function loadEnv() {
    $envPath = __DIR__ . '/.env';
    if (!file_exists($envPath)) die(".env file missing");
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') === false || str_starts_with(trim($line), '#')) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// ─────────────────────────────────────────────────────────────
// Get Access Token (uses .env variables)
// ─────────────────────────────────────────────────────────────
function getAccessToken() {
    $url = $_ENV['TOKEN_URL'];
    $post = http_build_query([
        'client_id' => $_ENV['CLIENT_ID'],
        'client_secret' => $_ENV['CLIENT_SECRET'],
        'grant_type' => 'password',
        'username' => $_ENV['USERNAME'],
        'password' => $_ENV['PASSWORD'],
        'scope' => $_ENV['SCOPE']
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_VERBOSE => true
    ]);

    ob_start();
    $response = curl_exec($ch);
    $curlLog = ob_get_clean();
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($_ENV['DEBUG'] === 'true') {
        echo "<pre><strong>DEBUG: Token Request</strong>\n";
        echo "HTTP Code: $httpCode\n";
        echo "CURL Error: $error\n";
        echo "Raw Response: $response\n</pre>";
    }

    $data = json_decode($response, true);
    if (!isset($data['access_token'])) {
        die("<strong>Auth failed</strong>. Check credentials and endpoint.<br><pre>" . htmlspecialchars($response) . "</pre>");
    }

    return $data['access_token'];
}

// ─────────────────────────────────────────────────────────────
// Call API Endpoint
// ─────────────────────────────────────────────────────────────
function callEndpoint($method, $url, $token, $payload = null) {
    $ch = curl_init($url);
    $headers = [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ];
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ];
    if (strtoupper($method) === 'POST') {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = json_encode($payload ?? []);
    }
    curl_setopt_array($ch, $opts);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

// ─────────────────────────────────────────────────────────────
// UI Rendering Helpers
// ─────────────────────────────────────────────────────────────
function printPrettyJson($data) {
    echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
}

function htmlHeader() {
    echo <<<HTML
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>MPSM Endpoint Explorer</title>
<style>
body { background: #111; color: #eee; font-family: monospace; padding: 2em; }
h1, h2 { color: #6cf; }
pre { background: #222; color: #0f0; padding: 1em; overflow-x: auto; }
label { width: 130px; display: inline-block; }
input[type=text] { width: 300px; }
.error { color: red; }
</style>
</head><body>
<h1>MPSM Endpoint Explorer (No Composer)</h1>
HTML;
}

function htmlFooter() {
    echo "</body></html>";
}

// ─────────────────────────────────────────────────────────────
// Dynamic Payload Builder
// ─────────────────────────────────────────────────────────────
function detectRequiredFields($path) {
    $fields = [];
    if (str_contains($path, 'Customer')) $fields[] = 'CustomerId';
    if (str_contains($path, 'Device'))   $fields[] = 'DeviceId';
    if (str_contains($path, 'Asset'))    $fields[] = 'AssetNumber';
    return $fields;
}

function buildPayload($path, $required = []) {
    $payload = [
        'DealerId' => $_ENV['MPSM_DEALER_ID'] ?? 'SZ13qRwU5GtFLj0i_CbEgQ2',
        'PageIndex' => 0,
        'PageSize' => 5
    ];
    foreach ($required as $field) {
        $payload[$field] = $_GET[$field] ?? null;
    }
    return $payload;
}

// ─────────────────────────────────────────────────────────────
// Main Execution
// ─────────────────────────────────────────────────────────────
loadEnv();
htmlHeader();
$token = getAccessToken();
$baseUrl = rtrim($_ENV['BASE_URL'], '/');
$endpoints = json_decode(file_get_contents(__DIR__ . '/AllEndpoints.json'), true);

// Render Input Form
echo "<form method='GET'><h2>Set Required Fields</h2>";
foreach (['CustomerId', 'DeviceId', 'AssetNumber'] as $field) {
    echo "<label>$field:</label><input type='text' name='$field' value='" . ($_GET[$field] ?? '') . "'><br>";
}
echo "<br><input type='submit' value='Run Test'></form>";

foreach ($endpoints as $group => $list) {
    echo "<h2>$group</h2>";
    foreach ($list as $entry) {
        $method = strtoupper($entry['method'] ?? 'GET');
        $path = ltrim($entry['path'] ?? '', '/');
        $desc = htmlspecialchars($entry['summary'] ?? '');
        $url = "$baseUrl/$path";

        $required = detectRequiredFields($path);
        $missing = array_filter($required, fn($f) => empty($_GET[$f]));
        echo "<h3>{$method} /$path</h3><p>$desc</p>";

        if (!empty($missing)) {
            echo "<p class='error'>Missing required: " . implode(', ', $missing) . "</p>";
            continue;
        }

        $payload = buildPayload($path, $required);
        $result = callEndpoint($method, $url, $token, $method === 'POST' ? $payload : null);
        printPrettyJson($result);
    }
}

htmlFooter();
