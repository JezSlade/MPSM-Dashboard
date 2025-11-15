<?php
/**
 * Fix Config Session Check - Use Web Context Detection
 *
 * Changes CLI check to web context check (more reliable)
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$configFile = __DIR__ . '/config.php';

if (!file_exists($configFile)) {
    die("ERROR: config.php not found\n");
}

// Read config
$content = file_get_contents($configFile);

// Replace the SAPI check with HTTP context check
$oldPattern = "if (php_sapi_name() !== 'cli') {";
$newPattern = "if (!empty(\$_SERVER['REQUEST_METHOD'])) { // HTTP request (not CLI/CRON)";

if (strpos($content, $oldPattern) === false) {
    die("ERROR: Could not find CLI check pattern to replace\n");
}

$content = str_replace($oldPattern, $newPattern, $content);

// Backup
$backup = $configFile . '.backup.' . date('Y-m-d-His');
copy($configFile, $backup);
echo "Backup created: $backup\n";

// Write fixed config
file_put_contents($configFile, $content);

echo "SUCCESS: Config now uses HTTP context detection instead of SAPI check\n";
echo "Session config will only run for web requests (litespeed)\n";
echo "CRON/CLI/CGI-FCGI will skip session config\n";
