<?php
/**
 * Clear OPcache
 *
 * Forces PHP to reload all cached files
 */

header('Content-Type: text/plain');

echo "=== CLEAR OPCACHE ===\n\n";

if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✓ OPcache cleared successfully\n";
    } else {
        echo "✗ OPcache reset failed\n";
    }

    $status = opcache_get_status();
    echo "\nOPcache status:\n";
    echo "  Enabled: " . ($status['opcache_enabled'] ? 'Yes' : 'No') . "\n";
    echo "  Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    echo "  Hits: " . $status['opcache_statistics']['hits'] . "\n";
    echo "  Misses: " . $status['opcache_statistics']['misses'] . "\n";
} else {
    echo "OPcache is not available\n";
}

echo "\nNow try: /cms/api/force-refresh-start.php\n";
