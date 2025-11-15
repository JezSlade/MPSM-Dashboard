<?php
/**
 * Direct Config Fix - Session Wrapper
 *
 * Directly wraps session configuration in CLI check
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$configFile = __DIR__ . '/config.php';

if (!file_exists($configFile)) {
    die("ERROR: config.php not found\n");
}

// Read config
$content = file_get_contents($configFile);

// Check if already wrapped
if (strpos($content, "if (php_sapi_name() !== 'cli')") !== false) {
    die("Already patched - session code is wrapped in CLI check\n");
}

// Find the session configuration block and wrap it
$pattern = '/(\$isHTTPS = .*?;.*?session_set_cookie_params\(\[.*?\]\);.*?session_name\(\'MPSM_SESSION\'\);.*?ini_set\(\'session\.cookie_samesite\',.*?\);)/s';

if (!preg_match($pattern, $content)) {
    die("ERROR: Could not find session configuration block to wrap\n");
}

$replacement = "if (php_sapi_name() !== 'cli') {\n    \$1\n}";

$newContent = preg_replace($pattern, $replacement, $content);

if ($newContent === $content) {
    die("ERROR: Pattern replacement failed\n");
}

// Backup
$backup = $configFile . '.backup.' . date('Y-m-d-His');
copy($configFile, $backup);
echo "Backup created: $backup\n";

// Write fixed config
file_put_contents($configFile, $newContent);

echo "SUCCESS: Session code wrapped in CLI check\n";
echo "The SERVER_PORT warning should now be gone in CLI mode.\n";
