<?php
/**
 * Clear PHP OPcache
 *
 * Clears opcache to force reload of config.php changes
 */

header('Content-Type: text/plain');

echo "=== OPCACHE CLEAR ===\n\n";

if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    if ($result) {
        echo "✓ OPcache cleared successfully\n";
    } else {
        echo "✗ OPcache clear failed\n";
    }

    // Show opcache status
    $status = opcache_get_status();
    if ($status) {
        echo "\nOPcache Status:\n";
        echo "  Enabled: " . ($status['opcache_enabled'] ? 'Yes' : 'No') . "\n";
        echo "  Cache full: " . ($status['cache_full'] ? 'Yes' : 'No') . "\n";
        echo "  Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "  Hits: " . $status['opcache_statistics']['hits'] . "\n";
        echo "  Misses: " . $status['opcache_statistics']['misses'] . "\n";
    }
} else {
    echo "✗ OPcache not available\n";
}

echo "\nNow config.php should reload with latest changes.\n";
