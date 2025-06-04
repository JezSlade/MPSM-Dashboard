<?php
/**
 * debug.php
 *
 * A “crazy robust” diagnostic script for the MPSM Dashboard skeleton,
 * now with a “Copy JSON to Clipboard” button.
 *
 * Usage:
 *   1. Place this file and debug_checks.json in /public/mpsm/
 *   2. Ensure both are world-readable (e.g., chmod 644).
 *   3. Browse to http://<your-domain>/mpsm/debug.php
 *   4. Append ?format=json for pure JSON output.
 *
 * What it does:
 *   A. Validates files/folders per debug_checks.json.
 *   B. Parses index.php for <link rel="stylesheet"> tags and checks each CSS.
 *   C. Checks PHP version, required extensions, and key ini settings.
 *   D. Recursively scans all .php files for missing include/require targets.
 *   E. Renders a combined HTML report + JSON at the bottom, plus a copy button.
 *
 * Important:
 *   • This version uses plain CSS (no SCSS).
 *   • Working.php and .env are NOT part of this project (reference only).
 *   • To extend checks, modify debug_checks.json directly.
 */

header(
    'Content-Type: '
    . (
        isset($_GET['format']) && $_GET['format'] === 'json'
            ? 'application/json'
            : 'text/html; charset=UTF-8'
    )
);

$results = [
    'checks'   => [],
    'summary'  => ['total' => 0, 'passed' => 0, 'warnings' => 0, 'failures' => 0],
    'timestamp' => date('c')
];

// Helper to record a check result
function addCheck(&$results, $section, $label, $status, $message = '', $fix = '') {
    $entry = [
        'section' => $section,
        'label'   => $label,
        'status'  => $status,    // PASS, WARN, or FAIL
        'message' => $message,
        'fix'     => $fix
    ];
    $results['checks'][] = $entry;
    $results['summary']['total']++;
    if ($status === 'PASS') {
        $results['summary']['passed']++;
    } elseif ($status === 'WARN') {
        $results['summary']['warnings']++;
    } else {
        $results['summary']['failures']++;
    }
}

/********************** SECTION A: FILE & FOLDER EXISTENCE / PERMISSIONS **********************/

$section = 'A. Files & Permissions';
$jsonCfgPath = __DIR__ . '/debug_checks.json';

if (!is_readable($jsonCfgPath)) {
    addCheck($results, $section, 'debug_checks.json', 'FAIL',
        'Cannot read debug_checks.json at root. Did you upload it?',
        'Place a valid debug_checks.json in /public/mpsm/.'
    );
} else {
    $cfgRaw = file_get_contents($jsonCfgPath);
    $cfg = json_decode($cfgRaw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        addCheck($results, $section, 'debug_checks.json', 'FAIL',
            'JSON parse error: ' . json_last_error_msg(),
            'Fix syntax in debug_checks.json.'
        );
    } else {
        foreach ($cfg['checks'] as $check) {
            $type  = $check['type'];    // currently only “file_exists”
            $path  = $check['path'];    // relative
            $label = $check['label'];   // human label
            $full = __DIR__ . '/' . $path;

            if ($type === 'file_exists') {
                if (file_exists($full)) {
                    if (is_readable($full)) {
                        addCheck($results, $section, $label, 'PASS',
                            "Found and readable at "$path"."
                        );
                    } else {
                        addCheck($results, $section, $label, 'FAIL',
                            ""$path" exists but is not readable by PHP.",
                            "chmod 644 "$path" so the webserver can read it."
                        );
                    }
                } else {
                    addCheck($results, $section, $label, 'FAIL',
                        ""$path" is missing.",
                        "Ensure "$path" is uploaded to /public/mpsm/."
                    );
                }
            } else {
                addCheck($results, $section, $label, 'WARN',
                    "Unknown check type "$type"—cannot validate.",
                    "Remove or fix this entry in debug_checks.json."
                );
            }
        }
    }
}

/********************** SECTION B: CSS <link> PARSE & EXISTENCE CHECK **********************/

$section = 'B. CSS <link> References';
$indexPath = __DIR__ . '/index.php';
if (!file_exists($indexPath) || !is_readable($indexPath)) {
    addCheck($results, $section, 'index.php', 'FAIL',
        "Cannot open index.php for parsing CSS references.",
        "Verify index.php exists and is readable."
    );
} else {
    $indexHtml = file_get_contents($indexPath);
    preg_match_all(
        '/<link\b[^>]*rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\'][^>]*>/i',
        $indexHtml,
        $matches
    );

    if (empty($matches[1])) {
        addCheck($results, $section, '
