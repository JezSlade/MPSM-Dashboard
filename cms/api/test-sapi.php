<?php
/**
 * Test SAPI Detection
 *
 * Shows what php_sapi_name() returns in different contexts
 */

header('Content-Type: text/plain');

echo "=== SAPI DETECTION TEST ===\n\n";

echo "Current SAPI: " . php_sapi_name() . "\n";
echo "Is CLI: " . (php_sapi_name() === 'cli' ? 'YES' : 'NO') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";

echo "Server variables:\n";
echo "  SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'not set') . "\n";
echo "  REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set') . "\n";
echo "  HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "\n\n";

echo "Testing CLI mode check:\n";
if (php_sapi_name() !== 'cli') {
    echo "  → Running in HTTP/CGI mode (session config would execute)\n";
} else {
    echo "  → Running in CLI mode (session config would be skipped)\n";
}

// Test the actual condition used in config.php
echo "\nTesting specific condition from config:\n";
$isCLI = php_sapi_name() === 'cli';
echo "  php_sapi_name() === 'cli': " . ($isCLI ? 'TRUE' : 'FALSE') . "\n";
echo "  php_sapi_name() !== 'cli': " . (!$isCLI ? 'TRUE' : 'FALSE') . "\n";
