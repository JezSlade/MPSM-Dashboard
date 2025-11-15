<?php
/**
 * One-Time Config Patch: Add CLI check for session configuration
 *
 * This script modifies config.php to skip session configuration when running in CLI mode.
 * This prevents warnings/errors when CLI scripts (like cache refresh) load config.php.
 *
 * RUN ONCE: php cms/patch-config-cli.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$configFile = __DIR__ . '/config.php';

if (!file_exists($configFile)) {
    die("ERROR: config.php not found at: {$configFile}\n");
}

// Read current config
$content = file_get_contents($configFile);

// Check if already patched
if (strpos($content, "php_sapi_name() !== 'cli'") !== false) {
    die("Config already patched (CLI check exists). No changes needed.\n");
}

// Define the old pattern
$oldPattern = <<<'OLD'
// Session Configuration for Cross-Browser Compatibility
// Set secure session cookie parameters before session_start()
$isHTTPS = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || $_SERVER['SERVER_PORT'] == 443
           || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_set_cookie_params([
    'lifetime' => SESSION_TIMEOUT,
    'path' => '/',
    'domain' => '',  // Empty = current domain
    'secure' => $isHTTPS,  // Only send over HTTPS
    'httponly' => true,  // Prevent JavaScript access
    'samesite' => 'Lax'  // Cross-browser compatibility (Firefox, Safari, Chrome)
]);

// Deployment Configuration
define('DEPLOY_SECRET', 'mpsm_deploy_' . md5(SECURE_KEY . 'deployment'));

// Session name for better compatibility
session_name('MPSM_SESSION');

// Session cookie settings for mobile/Firefox
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Lax');
OLD;

// Define the new pattern
$newPattern = <<<'NEW'
// Session Configuration for Cross-Browser Compatibility
// Skip session config in CLI mode (no HTTP context)
if (php_sapi_name() !== 'cli') {
    // Set secure session cookie parameters before session_start()
    $isHTTPS = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || $_SERVER['SERVER_PORT'] == 443
               || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'domain' => '',  // Empty = current domain
        'secure' => $isHTTPS,  // Only send over HTTPS
        'httponly' => true,  // Prevent JavaScript access
        'samesite' => 'Lax'  // Cross-browser compatibility (Firefox, Safari, Chrome)
    ]);

    // Session name for better compatibility
    session_name('MPSM_SESSION');

    // Session cookie settings for mobile/Firefox
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
}

// Deployment Configuration
define('DEPLOY_SECRET', 'mpsm_deploy_' . md5(SECURE_KEY . 'deployment'));
NEW;

// Try to find and replace
if (strpos($content, $oldPattern) === false) {
    // Try alternate match without DEPLOY_SECRET (it might not exist yet)
    $oldPattern2 = <<<'OLD2'
// Session Configuration for Cross-Browser Compatibility
// Set secure session cookie parameters before session_start()
$isHTTPS = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || $_SERVER['SERVER_PORT'] == 443
           || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_set_cookie_params([
    'lifetime' => SESSION_TIMEOUT,
    'path' => '/',
    'domain' => '',  // Empty = current domain
    'secure' => $isHTTPS,  // Only send over HTTPS
    'httponly' => true,  // Prevent JavaScript access
    'samesite' => 'Lax'  // Cross-browser compatibility (Firefox, Safari, Chrome)
]);

// Session name for better compatibility
session_name('MPSM_SESSION');

// Session cookie settings for mobile/Firefox
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Lax');
OLD2;

    $newPattern2 = <<<'NEW2'
// Session Configuration for Cross-Browser Compatibility
// Skip session config in CLI mode (no HTTP context)
if (php_sapi_name() !== 'cli') {
    // Set secure session cookie parameters before session_start()
    $isHTTPS = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || $_SERVER['SERVER_PORT'] == 443
               || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'domain' => '',  // Empty = current domain
        'secure' => $isHTTPS,  // Only send over HTTPS
        'httponly' => true,  // Prevent JavaScript access
        'samesite' => 'Lax'  // Cross-browser compatibility (Firefox, Safari, Chrome)
    ]);

    // Session name for better compatibility
    session_name('MPSM_SESSION');

    // Session cookie settings for mobile/Firefox
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
}

// Deployment Configuration
define('DEPLOY_SECRET', 'mpsm_deploy_' . md5(SECURE_KEY . 'deployment'));
NEW2;

    $content = str_replace($oldPattern2, $newPattern2, $content);
} else {
    $content = str_replace($oldPattern, $newPattern, $content);
}

// Backup original
$backupFile = $configFile . '.backup.' . date('Y-m-d-His');
copy($configFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Write patched config
file_put_contents($configFile, $content);

echo "SUCCESS: config.php patched successfully!\n";
echo "Session configuration now skipped in CLI mode.\n";
echo "\n";
echo "Verify by running:\n";
echo "  php cms/api/refresh-cache-chunked.php status\n";
echo "\n";
echo "You should see clean JSON output with no warnings.\n";
