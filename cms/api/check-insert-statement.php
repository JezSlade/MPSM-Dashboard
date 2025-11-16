<?php
/**
 * Check INSERT Statement
 */

header('Content-Type: text/plain');

$file = __DIR__ . '/refresh-cache-chunked.php';
$content = file_get_contents($file);

echo "=== INSERT STATEMENT CHECK ===\n\n";

// Find the INSERT statement
if (preg_match('/INSERT INTO.*?cache_devices_staging.*?\((.*?)\)/s', $content, $matches)) {
    echo "Devices table INSERT columns:\n";
    echo $matches[1] . "\n\n";

    if (strpos($matches[1], 'device_serial') !== false) {
        echo "✗ USES device_serial (WRONG)\n";
    } elseif (strpos($matches[1], 'serial_number') !== false) {
        echo "✓ USES serial_number (CORRECT)\n";
    }
}

if (preg_match('/INSERT INTO.*?cache_device_drilldown_staging.*?\((.*?)\)/s', $content, $matches)) {
    echo "\nDrilldown table INSERT columns:\n";
    echo $matches[1] . "\n\n";

    if (strpos($matches[1], 'device_serial') !== false) {
        echo "✗ USES device_serial (WRONG)\n";
    } elseif (strpos($matches[1], 'serial_number') !== false) {
        echo "✓ USES serial_number (CORRECT)\n";
    }
}

echo "\nLast modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
