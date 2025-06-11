<?php
/**
 * functions.php
 *
 * MPSM Dashboard helper library.
 * - Output buffering
 * - Debug logging (debug_log)
 * - Partial inclusion (include_partial)
 * - Sanitizers (sanitize_html, sanitize_url, sanitize_int)
 * - JSON helper (respond_json)
 * - OAuth2 token management (loadEnv, loadCachedToken, cacheToken, requestNewToken, getAccessToken)
 * - Customer fetcher (fetch_customers)
 * - Card renderer (render_card)
 *
 * PHP 8.2+ required.
 */

// Output buffering
if (function_exists('ob_start')) { ob_start(); }

// Globals for debug logs
$debug_log_entries = [];
if (! isset($GLOBALS['debug_messages'])) {
    $GLOBALS['debug_messages'] = [];
}

/**
 * Logs a message with a severity.
 */
function debug_log(string $message, string $level = 'INFO'): void
{
    global $debug_log_entries;
    $level = strtoupper($level);
    $levels = defined('DEBUG_LOG_LEVELS') ? DEBUG_LOG_LEVELS : [];
    $should = in_array($level, ['ERROR','CRITICAL','SECURITY'], true)
        || (defined('DEBUG_MODE') && DEBUG_MODE && ($levels[$level] ?? false));
    if (! $should) return;

    $entry = ['time'=>date('Y-m-d H:i:s'),'level'=>$level,'message'=>$message];
    $debug_log_entries[] = $entry;
    $formatted = "[{$entry['time']}] [{$entry['level']}] {$entry['message']}";
    $GLOBALS['debug_messages'][] = $formatted;

    if (defined('DEBUG_LOG_TO_FILE') && DEBUG_LOG_TO_FILE && defined('DEBUG_LOG_FILE')) {
        $dir = dirname(DEBUG_LOG_FILE);
        if (!is_dir($dir)) mkdir($dir,0755,true);
        if ((MAX_DEBUG_LOG_SIZE_MB ?? 0) > 0
            && file_exists(DEBUG_LOG_FILE)
            && filesize(DEBUG_LOG_FILE)/(1024*1024) > MAX_DEBUG_LOG_SIZE_MB
        ) {
            file_put_contents(DEBUG_LOG_FILE,"--- Truncated ---\n",LOCK_EX);
        }
        file_put_contents(DEBUG_LOG_FILE, $formatted . "\n", FILE_APPEND|LOCK_EX);
    }
    if (in_array($level, ['ERROR','CRITICAL','SECURITY'], true)) {
        error_log("[MPSM_APP_LOG][$level] $message");
    }
}

/**
 * Includes a partial and injects data.
 */
function include_partial(string $relativePath, array $data = []): bool
{
    $path = APP_BASE_PATH . $relativePath;
    if (!file_exists($path)) {
        debug_log("Partial not found: $path", 'WARNING');
        if (DEBUG_MODE) {
            echo "<div class='warning-banner'>WARNING: Partial '{$relativePath}' missing.</div>";
        }
        return false;
    }
    extract($data, EXTR_SKIP);
    include $path;
    debug_log("Included partial: {$relativePath}", 'DEBUG');
    return true;
}

/** HTML sanitizer */
function sanitize_html(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
/** URL/slug sanitizer */
function sanitize_url(string $s): string {
    $slug = preg_replace('/[^a-zA-Z0-9_-]/','',$s);
    return strtolower(trim(preg_replace('/[-_]+/','-',$slug),'-_'));
}
/** Integer validator */
function sanitize_int($i): int {
    $v = filter_var($i, FILTER_VALIDATE_INT);
    return $v!==false ? (int)$v : 0;
}

/**
 * Clean JSON response and exit.
 */
function respond_json($data): void
{
    if (ob_get_length()!==false) ob_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// OAuth2 token management
define('ENV_FILE', __DIR__ . '/.env');
define('TOKEN_CACHE_FILE', __DIR__ . '/logs/token_cache.json');

function loadEnv(): void
{
    if (!file_exists(ENV_FILE) || !is_readable(ENV_FILE)) {
        throw new RuntimeException("Cannot load .env");
    }
    foreach (file(ENV_FILE, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line,'#')) continue;
        [$k,$v] = explode('=',$line,2)+[1=>''];
        $_ENV[trim($k)] = trim($v);
    }
    debug_log(".env loaded", 'DEBUG');
}

function loadCachedToken(): ?array
{
    if (!file_exists(TOKEN_CACHE_FILE)) {
        debug_log("No token cache", 'DEBUG');
        return null;
    }
    $raw = file_get_contents(TOKEN_CACHE_FILE);
    $data = json_decode($raw,true) ?: [];
    if (empty($data['access_token'])||empty($data['expires_at'])) {
        debug_log("Bad token cache", 'WARNING');
        return null;
    }
    if (time() >= $data['expires_at']) {
        debug_log("Token expired", 'DEBUG');
        return null;
    }
    debug_log("Using cached token", 'DEBUG');
    return $data;
}

function cacheToken(string $token, int $exp): void
{
    $p = ['access_token'=>$token,'expires_at'=>time()+$exp-30];
    $dir = dirname(TOKEN_CACHE_FILE);
    if (!is_dir($dir)) mkdir($dir,0755,true);
    file_put_contents(TOKEN_CACHE_FILE, json_encode($p,JSON_PRETTY_PRINT));
    debug_log("Cached token", 'DEBUG');
}

function requestNewToken(): array
{
    loadEnv();
    foreach (['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','TOKEN_URL'] as $k) {
        if (empty($_ENV[$k])) throw new RuntimeException("Missing $k in .env");
    }
    $form = http_build_query([
        'grant_type'=>'password',
        'client_id'=>$_ENV['CLIENT_ID'],
        'client_secret'=>$_ENV['CLIENT_SECRET'],
        'username'=>$_ENV['USERNAME'],
        'password'=>$_ENV['PASSWORD'],
        'scope'=>$_ENV['SCOPE']??''
    ]);
    debug_log("Requesting token", 'DEBUG');
    $ch = curl_init($_ENV['TOKEN_URL']);
    curl_setopt_array($ch,[
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$form,
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded']
    ]);
    $resp = curl_exec($ch);
    if ($resp===false) {
        $e=curl_error($ch); curl_close($ch);
        throw new RuntimeException("cURL error: $e");
    }
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code!==200) throw new RuntimeException("Token HTTP $code: $resp");
    $json = json_decode($resp,true);
    if (json_last_error()!==JSON_ERROR_NONE) {
        throw new RuntimeException("JSON error: ".json_last_error_msg());
    }
    debug_log("Token received", 'DEBUG');
    return $json;
}

function getAccessToken(): string
{
    debug_log("getAccessToken()", 'DEBUG');
    $c = loadCachedToken();
    if ($c) return $c['access_token'];
    $j = requestNewToken();
    cacheToken($j['access_token'],(int)$j['expires_in']);
    return $j['access_token'];
}

/**
 * Fetch customers. Defaults to DEALER_CODE in .env if none passed.
 */
function fetch_customers(?string $dealerCode=null): array
{
    loadEnv();
    if (!$dealerCode) {
        if (empty($_ENV['DEALER_CODE'])) {
            throw new RuntimeException("Missing DEALER_CODE");
        }
        $dealerCode = $_ENV['DEALER_CODE'];
    }
    debug_log("fetch_customers({$dealerCode})", 'DEBUG');
    $token = getAccessToken();
    $url   = MPSM_API_BASE_URL.'Customer/GetCustomers';
    $payload = [
        'DealerCode'=>$dealerCode,
        'Code'=>null,
        'HasHpSds'=>null,
        'FilterText'=>null,
        'PageNumber'=>1,
        'PageRows'=>2147483647,
        'SortColumn'=>'Id',
        'SortOrder'=>0
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_HTTPHEADER=>[
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
        ],
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>json_encode($payload)
    ]);
    $resp = curl_exec($ch);
    if ($resp===false) {
        $e=curl_error($ch); curl_close($ch);
        debug_log("cURL error fetch_customers: $e", 'ERROR');
        return [];
    }
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code!==200) {
        debug_log("fetch_customers HTTP $code: $resp", 'ERROR');
        return [];
    }
    $data = json_decode($resp,true)?:[];
    debug_log("fetch_customers returned ".count($data['Result']??[]), 'DEBUG');
    return $data['Result']??[];
}

/**
 * Renders a card component.
 */
function render_card(string $name, array $data): void
{
    debug_log("Rendering card: {$name}", 'DEBUG');
    include_partial("cards/{$name}.php", $data);
}
