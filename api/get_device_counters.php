<?php
// --- DEBUG BLOCK ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// Load .env only if needed
if (!function_exists('load_env')) {
    return $env;
  }
}

header('Content-Type: application/json');

// 🛑 Dual-mode execution: if called from cache engine, run API logic
if (isset($_GET['token'])) {
  $env = load_env();
  $token = $_GET['token'];
  $customerCode = $_GET['customer'] ?? null;

// BEGIN API LOGIC

<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------

header('Content-Type: application/json');

// --- Manual .env parsing ---
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    http_response_code(500);
    echo json_encode(['error' => '.env file missing']);
    exit;
}
$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    [$key, $val] = explode('=', $line, 2);
    $env[trim($key)] = trim($val);
}

$dealerCode = $env['DEALER_CODE'] ?? '';
$customerCode = $_GET['customer'] ?? 'W9OPXL0YDK';
$apiBase = rtrim($env['BASE_URL'] ?? $env['API_BASE_URL'], '/');

// --- Get Token ---
$tokenPayload = http_build_query([
    'grant_type'    => 'password',
    'client_id'     => $env['CLIENT_ID'],
    'client_secret' => $env['CLIENT_SECRET'],
    'username'      => $env['USERNAME'],
    'password'      => $env['PASSWORD'],
    'scope'         => $env['SCOPE']
]);

$tokenContext = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded",
        'content' => $tokenPayload
    ]
]);
$tokenResponse = file_get_contents($env['TOKEN_URL'], false, $tokenContext);
$tokenData = json_decode($tokenResponse, true);
$token = $tokenData['access_token'] ?? null;
if (!$token) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to acquire token']);
    exit;
}

// --- Build Payload ---
$payload = json_encode([
    'DealerCode'         => $dealerCode,
    'CustomerCode'       => $customerCode,
    'SerialNumber'       => '',
    'AssetNumber'        => null,
    'CounterDetaildTags' => null
]);

// --- Make API Request ---
$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $token",
        'content' => $payload
    ]
]);
$response = file_get_contents("$apiBase/Counter/ListDetailed", false, $context);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'API request failed']);
    exit;
}

http_response_code(200);
echo $response;


// END API LOGIC
} else {
  // 🔁 Card mode: return from cache
  $cache = file_get_contents(__DIR__ . '/../cache/data.json');
  $json = json_decode($cache, true);
  echo json_encode($json['counters'] ?? []);
  exit;
}
?>