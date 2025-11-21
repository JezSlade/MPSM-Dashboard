<?php
/**
 * Remote helper that executes the chunked cache refresh process via CLI.
 *
 * Usage: curl "https://.../run-refresh-cache-chunked.php?secret=RUN_REFRESH_2025"
 * Returns JSON describing the shell command and output to aid debugging.
 *
 * This endpoint is protected by a secret query parameter to avoid abuse.
 */

define('RUN_REFRESH_SECRET', 'RUN_REFRESH_2025');

$secret = $_GET['secret'] ?? '';
if ($secret !== RUN_REFRESH_SECRET) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Access denied (invalid secret)'
    ], JSON_PRETTY_PRINT);
    exit;
}

// Build command (same as cron but force immediate execution)
$command = '/usr/local/bin/php /home/resolut7/public_html/mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php process 2>&1';

$output = [];
$exitCode = 0;
exec($command, $output, $exitCode);

header('Content-Type: application/json');
echo json_encode([
    'success' => $exitCode === 0,
    'command' => $command,
    'exit_code' => $exitCode,
    'output' => implode("\n", $output),
    'timestamp' => date('c')
], JSON_PRETTY_PRINT);

/*
CHANGELOG
2025-11-19 Codex
- Added `run-refresh-cache-chunked.php` so authorized callers can execute the chunked refresh CLI remotely and inspect the returned output/exit code.
*/
