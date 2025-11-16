<?php
/**
 * Reset Cache State
 *
 * Clears state file and starts fresh
 */

header('Content-Type: text/plain');

echo "=== RESET CACHE STATE ===\n\n";

$stateFile = __DIR__ . '/../locks/cache-refresh-state.json';

if (file_exists($stateFile)) {
    $backup = $stateFile . '.backup.' . date('Y-m-d-His');
    rename($stateFile, $backup);
    echo "✓ Moved old state to: $backup\n";
} else {
    echo "No existing state file\n";
}

echo "\nState reset. Ready for fresh start.\n";
echo "Next: /cms/api/force-refresh-start.php\n";
