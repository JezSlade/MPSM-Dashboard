<?php
/**
 * Show Config Session Block
 *
 * Displays the session configuration section to see what needs fixing
 */

header('Content-Type: text/plain');

$configFile = dirname(__DIR__) . '/config.php';

if (!file_exists($configFile)) {
    die("Config file not found\n");
}

$lines = file($configFile);

echo "=== CONFIG.PHP SESSION SECTION ===\n\n";
echo "Lines 48-80 (session configuration area):\n";
echo str_repeat('-', 60) . "\n";

for ($i = 47; $i < min(80, count($lines)); $i++) {
    $lineNum = $i + 1;
    $line = rtrim($lines[$i]);
    $marker = ($lineNum == 54) ? ' <-- LINE 54 (ERROR)' : '';
    printf("%3d: %s%s\n", $lineNum, $line, $marker);
}

echo "\n" . str_repeat('-', 60) . "\n";
echo "Checking for CLI wrapper...\n";

$content = file_get_contents($configFile);
if (strpos($content, "php_sapi_name() !== 'cli'") !== false) {
    echo "✓ CLI check found in config\n";
} else {
    echo "✗ NO CLI check found - needs patching\n";
}
