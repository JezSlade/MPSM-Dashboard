<?php
/**
 * functions.php
 *
 * Helpers: output buffering, debug logging, templating, sanitizers,
 * JSON responses, OAuth2 token mgmt, data fetching, and card rendering.
 */

// Start output buffering
if (function_exists('ob_start')) ob_start();

// Globals for debug
$debug_log_entries = [];
if (!isset($GLOBALS['debug_messages'])) {
    $GLOBALS['debug_messages'] = [];
}

/**
 * Logs a message and mirrors it to the footer panel.
 */
function debug_log(string $message, string $level = 'INFO'): void
{
    global $debug_log_entries;
    $level = strtoupper($level);
    $levels = defined('DEBUG_LOG_LEVELS') ? DEBUG_LOG_LEVELS : [];

    $should = in_array($level, ['ERROR','CRITICAL','SECURITY'], true)
           || (DEBUG_MODE && ($levels[$level] ?? false));
    if (!$should) return;

    $entry = ['time'=>date('Y-m-d H:i:s'),'level'=>$level,'message'=>$message];
    $debug_log_entries[] = $entry;
    $formatted = "[{$entry['time']}] [{$entry['level']}] {$entry['message']}";
    $GLOBALS['debug_messages'][] = $formatted;

    if (DEBUG_LOG_TO_FILE) {
        $file = DEBUG_LOG_FILE; $dir = dirname($file);
        if (!is_dir($dir)) mkdir($dir,0755,true);
        if ((MAX_DEBUG_LOG_SIZE_MB ?? 0) > 0
            && file_exists($file)
            && filesize($file)/(1024*1024) > MAX_DEBUG_LOG_SIZE_MB
        ) {
            file_put_contents($file, "--- Truncated ---\n", LOCK_EX);
        }
        file_put_contents($file, $formatted."\n", FILE_APPEND|LOCK_EX);
    }
    if (in_array($level, ['ERROR','CRITICAL','SECURITY'], true)) {
        error_log("[MPSM_APP_LOG][$level] $message");
    }
}

/**
 * Includes a PHP partial, passing in $data.
 */
function include_partial(string $relativePath, array $data = []): bool
{
    $full = APP_BASE_PATH . $relativePath;
    if (!file_exists($full)) {
        debug_log("Partial not found: $full", 'WARNING');
        if (DEBUG_MODE) {
            echo "<div class='warning-banner'>WARNING: Partial '{$relativePath}' missing.</div>";
        }
        return false;
    }
    extract($data, EXTR_SKIP);
    include $full;
    debug_log("Included partial: {$relativePath}", 'DEBUG');
    return true;
}

// Sanitizers
function sanitize_html(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function sanitize_url(string $s): string {
    $slug = preg_replace('/[^a-zA-Z0-9_-]/','',$s);
    return strtolower(trim(preg_replace('/[-_]+/','-',$slug),'-_'));
}
function sanitize_int($i): int {
    $v = filter_var($i, FILTER_VALIDATE_INT);
    return $v!==false ? (int)$v : 0;
}

/**
 * Sends JSON response and exits.
 */
function respond_json($data): void
{
    if (ob_get_length()!==false) ob_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// OAuth2 token mgmt
define('ENV_FILE',        __DIR__ . '/.env');
define('TOKEN_CACHE_FILE',__DIR__ . '/logs/token_cache.json');

function loadEnv(): void
{
    if (!file_exists(ENV_FILE) || !is_readable(ENV_FILE)) {
        throw new RuntimeException("Cannot load .env");
    }
    foreach (file(ENV_FILE, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $l) {
        $l = trim($l);
        if ($l==='' || $l[0]==='#') continue;
        [$k,$v] = explode('=',$l,2)+[1=>''];
        $_ENV[trim($k)] = trim($v);
    }
    debug_log(".env loaded into \$_ENV", 'DEBUG');
}

function loadCachedToken(): ?array
{
    if (!file_exists(TOKEN_CACHE_FILE)) return null;
    $d = json_decode(file_get_contents(TOKEN_CACHE_FILE), true) ?: [];
    if (empty($d['access_token'])||empty($d['expires_at'])||time()>= $d['expires_at']) return null;
    debug_log("Using cached token", 'DEBUG');
    return $d;
}

function cacheToken(string $t, int $e): void
{
    $p = ['access_token'=>$t,'expires_at'=>time()+$e-30];
    $dir = dirname(TOKEN_CACHE_FILE);
    if (!is_dir($dir)) mkdir($dir,0755,true);
    file_put_contents(TOKEN_CACHE_FILE, json_encode($p,JSON_PRETTY_PRINT));
    debug_log("Cached new token", 'DEBUG');
}

function requestNewToken(): array
{
    loadEnv();
    foreach (['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','TOKEN_URL'] as $k) {
        if (empty($_ENV[$k])) throw new RuntimeException("Missing \${$k} in .env");
    }
    $form = http_build_query([
        'grant_type'=>'password',
        'client_id'=>$_ENV['CLIENT_ID'],
        'client_secret'=>$_ENV['CLIENT_SECRET'],
        'username'=>$_ENV['USERNAME'],
        'password'=>$_ENV['PASSWORD'],
        'scope'=>$_ENV['SCOPE']??''
    ]);
    debug_log("Requesting token from {$_ENV['TOKEN_URL']}", 'DEBUG');
    $ch = curl_init($_ENV['TOKEN_URL']);
    curl_setopt_array($ch,[
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$form,
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_FOLLOWLOCATION=>true,
        CURLOPT_MAXREDIRS=>3,
    ]);
    $r = curl_exec($ch);
    if ($r===false) { $e=curl_error($ch); curl_close($ch); throw new RuntimeException($e); }
    $c = curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
    if ($c!==200) throw new RuntimeException("Token HTTP $c: $r");
    $j = json_decode($r,true);
    debug_log("Received new token", 'DEBUG');
    return $j;
}

function getAccessToken(): string
{
    debug_log("getAccessToken() called", 'DEBUG');
    $c = loadCachedToken();
    if ($c) return $c['access_token'];
    $j = requestNewToken();
    cacheToken($j['access_token'], (int)$j['expires_in']);
    return $j['access_token'];
}

/**
 * Fetch customer list.
 */
function fetch_customers(?string $dealerCode=null): array
{
    loadEnv();
    if (!$dealerCode) {
        if (empty($_ENV['DEALER_CODE'])) throw new RuntimeException("Missing DEALER_CODE");
        $dealerCode = $_ENV['DEALER_CODE'];
    }
    debug_log("fetch_customers({$dealerCode})", 'DEBUG');
    $token = getAccessToken();
    $url   = MPSM_API_BASE_URL . 'Customer/GetCustomers';
    $p     = ['DealerCode'=>$dealerCode,'Code'=>null,'HasHpSds'=>null,'FilterText'=>null,
              'PageNumber'=>1,'PageRows'=>2147483647,'SortColumn'=>'Id','SortOrder'=>0];
    $ch = curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_HTTPHEADER=>[
          'Content-Type: application/json',
          'Authorization: Bearer '.$token
        ],
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>json_encode($p),
        CURLOPT_FOLLOWLOCATION=>true,
        CURLOPT_MAXREDIRS=>3
    ]);
    $r = curl_exec($ch);
    if ($r===false) { $e=curl_error($ch); curl_close($ch); debug_log($e,'ERROR'); return []; }
    $c = curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
    if ($c!==200) { debug_log("HTTP $c: $r",'ERROR'); return []; }
    $d = json_decode($r,true)?:[];
    debug_log("fetch_customers returned ".count($d['Result']??[]),'DEBUG');
    return $d['Result'] ?? [];
}

/**
 * Include a card partial.
 */
function render_card(string $name, array $data): void
{
    debug_log("Rendering card: {$name}", 'DEBUG');
    include_partial("cards/{$name}.php", $data);
}
