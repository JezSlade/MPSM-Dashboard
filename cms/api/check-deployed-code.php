<?php
/**
 * Check Deployed Code Version
 *
 * Shows what's actually in the deployed refresh-cache-chunked.php
 */

header('Content-Type: text/plain');

echo "=== DEPLOYED CODE CHECK ===\n\n";

$file = __DIR__ . '/refresh-cache-chunked.php';
$content = file_get_contents($file);

// Check for the fix
if (strpos($content, "isset(\$response['Result'])") !== false) {
    echo "✓ CORRECT: Code checks for \$response['Result']\n";
} else {
    echo "✗ OLD CODE: Still checking for \$response['data']\n";
}

if (strpos($content, "\$devices = \$response['Result']") !== false) {
    echo "✓ CORRECT: Code uses \$response['Result']\n";
} else {
    echo "✗ OLD CODE: Still using \$response['data']\n";
}

if (strpos($content, "\$totalRows = \$response['TotalRows']") !== false) {
    echo "✓ CORRECT: Code uses TotalRows\n";
} else {
    echo "✗ OLD CODE: Still using pagination\n";
}

if (strpos($content, "\$serial = \$device['SerialNumber']") !== false) {
    echo "✓ CORRECT: Code uses SerialNumber (PascalCase)\n";
} else {
    echo "✗ OLD CODE: Still using snake_case or camelCase\n";
}

echo "\nLast modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
