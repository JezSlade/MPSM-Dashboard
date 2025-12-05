<?php
/**
 * Check if curl is available in web environment
 */
header('Content-Type: text/plain');

echo "PHP Version: " . phpversion() . "\n";
echo "curl_init exists: " . (function_exists('curl_init') ? 'YES' : 'NO') . "\n";
echo "curl_version: ";
if (function_exists('curl_version')) {
    $version = curl_version();
    print_r($version);
} else {
    echo "NOT AVAILABLE\n";
}

echo "\nLoaded extensions:\n";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo "  - $ext\n";
}
