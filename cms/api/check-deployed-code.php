<?php
/**
 * Check Deployed Code Version
 *
 * Shows what is currently deployed for key cache and alerts integration files.
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

echo "\n=== ALERTS INTEGRATION CHECK ===\n\n";

$indexFile = dirname(__DIR__) . '/index.php';
$commandCenterFile = dirname(__DIR__) . '/command-center.php';
$appJsFile = dirname(__DIR__) . '/assets/app.js';
$commandCenterJsFile = dirname(__DIR__) . '/assets/command-center.js';
$partialFile = dirname(__DIR__) . '/partials/alert-center.php';

$indexContent = is_readable($indexFile) ? file_get_contents($indexFile) : '';
$ccContent = is_readable($commandCenterFile) ? file_get_contents($commandCenterFile) : '';
$appJsContent = is_readable($appJsFile) ? file_get_contents($appJsFile) : '';
$ccJsContent = is_readable($commandCenterJsFile) ? file_get_contents($commandCenterJsFile) : '';

if (strpos($indexContent, 'data-tab="alerts"') !== false) {
    echo "✓ CORRECT: index.php includes top-level Alerts nav tab\n";
} else {
    echo "✗ OLD CODE: index.php missing top-level Alerts nav tab\n";
}

if (strpos($indexContent, 'id="alerts-tab"') !== false) {
    echo "✓ CORRECT: index.php includes alerts-tab content container\n";
} else {
    echo "✗ OLD CODE: index.php missing alerts-tab content container\n";
}

if (strpos($commandCenterFile, 'command-center.php') !== false && strpos($ccContent, 'data-alert-center-standalone="1"') !== false) {
    echo "✓ CORRECT: command-center.php has standalone Alert Center marker\n";
} else {
    echo "✗ OLD CODE: command-center.php missing standalone Alert Center marker\n";
}

if (strpos($ccContent, '/partials/alert-center.php') !== false || strpos($ccContent, "partials/alert-center.php") !== false) {
    echo "✓ CORRECT: command-center.php uses shared alert-center partial\n";
} else {
    echo "✗ OLD CODE: command-center.php not using shared alert-center partial\n";
}

if (is_readable($partialFile)) {
    echo "✓ CORRECT: shared partial exists at cms/partials/alert-center.php\n";
} else {
    echo "✗ OLD CODE: shared partial missing at cms/partials/alert-center.php\n";
}

if (strpos($appJsContent, 'window.AlertCenter') !== false && strpos($appJsContent, "tabName === 'alerts'") !== false) {
    echo "✓ CORRECT: app.js contains Alerts tab mount/unmount logic\n";
} else {
    echo "✗ OLD CODE: app.js missing Alerts tab mount/unmount logic\n";
}

if (strpos($ccJsContent, 'window.AlertCenter =') !== false && strpos($ccJsContent, 'mountAlertCenter') !== false) {
    echo "✓ CORRECT: command-center.js exposes mountable AlertCenter module\n";
} else {
    echo "✗ OLD CODE: command-center.js missing mountable AlertCenter module\n";
}

if (strpos($content, "'action' => 'get_notifications'") !== false || strpos($content, 'get_notifications') !== false) {
    echo "✓ INFO: command-center notifications endpoint references present\n";
}

echo "\nindex.php modified: " . (is_readable($indexFile) ? date('Y-m-d H:i:s', filemtime($indexFile)) : 'missing') . "\n";
echo "command-center.php modified: " . (is_readable($commandCenterFile) ? date('Y-m-d H:i:s', filemtime($commandCenterFile)) : 'missing') . "\n";
echo "assets/app.js modified: " . (is_readable($appJsFile) ? date('Y-m-d H:i:s', filemtime($appJsFile)) : 'missing') . "\n";
echo "assets/command-center.js modified: " . (is_readable($commandCenterJsFile) ? date('Y-m-d H:i:s', filemtime($commandCenterJsFile)) : 'missing') . "\n";
