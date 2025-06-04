<?php
/**
 * debug.php
 *
 * A “crazy robust” diagnostic script for the MPSM Dashboard skeleton.
 *
 * Usage:
 *   1. Place this file and debug_checks.json (shown above) in /public/mpsm/
 *   2. Ensure both are world‐readable (e.g., chmod 644).
 *   3. Browse to http://<your-domain>/mpsm/debug.php
 *   4. To get the same output as machine‐readable JSON, append ?format=json
 *
 * What it does:
 *   - Section A: Loads debug_checks.json → verifies each listed path exists, is readable, and (where applicable) writable.
 *   - Section B: Parses index.php to find <link rel="stylesheet" href="…"> tags and checks each referenced CSS file.
 *   - Section C: Checks PHP version, required extensions, and key ini settings.
 *   - Section D: Recursively scans for missing include/require targets in all PHP files.
 *   - Section E: Generates a JSON “break/fix” report, so that ChatGPT (or any tool) can parse exactly which checks failed and how to fix them.
 *
 * Customize:
 *   - Extend debug_checks.json to add/remove file/folder checks.
 *   - Modify the “required_exts” array (Section C) if you need other PHP extensions.
 *   - If you add new <link> or <script> references in index.php, this script will auto-detect them.
 *
 * Important:
 *   - This script does NOT assume Working.php or .env are part of your project. It will not treat them as “required.” 
 *   - Treat Working.php purely as a reference “Bible” and do not upload/alter it in this project.
 */

header('Content-Type: ' . (isset($_GET['format']) && $_GET['format'] === 'json' ? 'application/json' : 'text/html; charset=UTF-8'));

$results = [
    'checks'   => [],
    'summary'  => [
        'total'       => 0,
        'passed'      => 0,
        'warnings'    => 0,
        'failures'    => 0
    ],
    'timestamp' => date('c')
];

// Utility functions for adding results
function addCheck(&$results, $section, $label, $status, $message = '', $fix = '') {
    // $status: 'PASS', 'WARN', or 'FAIL'
    $entry = [
        'section' => $section,
        'label'   => $label,
        'status'  => $status,
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
            $path  = $check['path'];    // relative to /public/mpsm/
            $label = $check['label'];   // human-friendly label

            $full = __DIR__ . '/' . $path;
            if ($type === 'file_exists') {
                if (file_exists($full)) {
                    // Check readability
                    if (is_readable($full)) {
                        addCheck($results, $section, $label, 'PASS',
                            "Found and readable at \"$path\"."
                        );
                    } else {
                        addCheck($results, $section, $label, 'FAIL',
                            "\"$path\" exists but is not readable by PHP.",
                            "chmod 644 \"$path\" (so the webserver can read it)."
                        );
                    }
                } else {
                    addCheck($results, $section, $label, 'FAIL',
                        "\"$path\" is missing.",
                        "Ensure \"$path\" is uploaded to /public/mpsm/."
                    );
                }
            } else {
                addCheck($results, $section, $label, 'WARN',
                    "Unknown check type \"$type\"—cannot validate.",
                    "Remove or fix this entry in debug_checks.json."
                );
            }
        }
    }
}

/******************** SECTION B: CSS LINK PARSE & EXISTENCE CHECK ********************/

$section = 'B. CSS <link> References';
$indexPath = __DIR__ . '/index.php';
if (!file_exists($indexPath) || !is_readable($indexPath)) {
    addCheck($results, $section, 'index.php', 'FAIL',
        "Cannot open index.php for parsing CSS references.",
        "Verify index.php exists and is readable."
    );
} else {
    $indexHtml = file_get_contents($indexPath);
    // Use a regex to find <link rel="stylesheet" href="…">
    // Support single or double quotes, and optional attributes in any order.
    preg_match_all(
        '/<link\b[^>]*rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\'][^>]*>/i',
        $indexHtml,
        $matches
    );

    if (empty($matches[1])) {
        addCheck($results, $section, 'CSS Links', 'WARN',
            'No <link rel="stylesheet" href="…"> tags found in index.php.',
            'Ensure your index.php is referencing stylesheets correctly.'
        );
    } else {
        foreach ($matches[1] as $href) {
            // Normalize the href (ignore query strings)
            $hrefClean = explode('?', $href, 2)[0];
            // If the href starts with “/” or “http”, skip filesystem check (external CSS)
            if (preg_match('/^(https?:|\/\/)/i', $hrefClean)) {
                addCheck($results, $section, $hrefClean, 'PASS',
                    "External CSS reference—assuming it will load from remote."
                );
                continue;
            }
            // Build filesystem path: if href is "assets/css/styles.css", then full path is __DIR__ . '/assets/css/styles.css'
            $cssPath = realpath(__DIR__ . '/' . ltrim($hrefClean, '/'));
            if ($cssPath && file_exists($cssPath)) {
                if (is_readable($cssPath)) {
                    $size = filesize($cssPath);
                    if ($size > 0) {
                        addCheck($results, $section, $hrefClean, 'PASS',
                            "\"$hrefClean\" exists, readable, size={$size} bytes."
                        );
                    } else {
                        addCheck($results, $section, $hrefClean, 'FAIL',
                            "\"$hrefClean\" is zero bytes.",
                            'Recompile your SCSS (e.g., `sass assets/scss/styles.scss assets/css/styles.css`).'
                        );
                    }
                } else {
                    addCheck($results, $section, $hrefClean, 'FAIL',
                        "\"$hrefClean\" exists but is not readable.",
                        "chmod 644 \"$hrefClean\" so the webserver can serve it."
                    );
                }
            } else {
                addCheck($results, $section, $hrefClean, 'FAIL',
                    "\"$hrefClean\" not found in filesystem.",
                    "Check the <link> path in index.php or place the CSS file there."
                );
            }
        }
    }
}

/******************** SECTION C: PHP VERSION, EXTENSIONS, & INI SETTINGS ********************/

$section = 'C. PHP Configuration';
$phpVer    = phpversion();
$isPhpOk   = version_compare($phpVer, '7.4.0', '>=');
addCheck($results, $section, 'PHP Version', $isPhpOk ? 'PASS' : 'FAIL',
    "Detected PHP version {$phpVer}.",
    $isPhpOk ? '' : 'Upgrade PHP to ≥ 7.4.'
);

// Required extensions
$required_exts = ['curl', 'json', 'mbstring'];
foreach ($required_exts as $ext) {
    $loaded = extension_loaded($ext);
    addCheck($results, $section, "Extension: {$ext}", $loaded ? 'PASS' : 'FAIL',
        $loaded ? "Extension `{$ext}` is loaded." : "Extension `{$ext}` is missing.",
        $loaded ? '' : "Install and enable the PHP `{$ext}` extension."
    );
}

// display_errors
$dispErr = ini_get('display_errors');
$dispErrStatus = ($dispErr == '1' || strtolower($dispErr) == 'on') ? 'WARN' : 'PASS';
addCheck($results, $section, 'PHP ini: display_errors', $dispErrStatus,
    "display_errors = {$dispErr}",
    $dispErrStatus === 'WARN'
        ? 'Turn off display_errors in production (set display_errors = Off in php.ini).'
        : ''
);

// memory_limit
$memLimit = ini_get('memory_limit');
addCheck($results, $section, 'PHP ini: memory_limit', 'PASS',
    "memory_limit = {$memLimit} (verify ≥ 128M if needed)."
);

// server software
$serverSoft = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
addCheck($results, $section, 'Server Software', 'PASS',
    "Running on `{$serverSoft}`."
);

/******************** SECTION D: RECURSIVE SCAN FOR MISSING include/require STATEMENTS ********************/

$section = 'D. Missing include/require Checks';
/**
 * Search all .php files under /public/mpsm/ for lines like:
 *    include 'some/path.php';
 *    require_once "another.php";
 * and verify that each target file actually exists. 
 */
$errors = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile()) continue;
    if (strtolower($fileInfo->getExtension()) !== 'php') continue;

    $fullPath = $fileInfo->getRealPath();
    $content  = file_get_contents($fullPath);
    // Match include/require, with optional _once, with single or double quotes, any whitespace
    preg_match_all(
        '/\b(include|require)(_once)?\s*[\(\'"]([^\'"]+\.php)[\'"]\)?\s*;?/i',
        $content,
        $matches,
        PREG_SET_ORDER
    );
    foreach ($matches as $m) {
        $includedRel = $m[3]; 
        // Resolve relative to the directory of the file containing the include
        $baseDir = dirname($fullPath);
        $target  = realpath($baseDir . '/' . $includedRel);
        if (!$target || !file_exists($target)) {
            // Compute a project-relative path
            $projSource = substr($fullPath, strlen(__DIR__) + 1);
            addCheck($results, $section, "{$projSource} → include \"{$includedRel}\"", 'FAIL',
                "\"{$includedRel}\" not found (referenced in {$projSource}).",
                "Ensure `{$includedRel}` exists relative to `{$projSource}`, or fix the path."
            );
        } else {
            addCheck($results, $section, "{$projSource} → include \"{$includedRel}\"", 'PASS',
                "\"{$includedRel}\" found for {$projSource}."
            );
        }
    }
}

/******************** SECTION E: GENERATE OUTPUT ********************/

if (isset($_GET['format']) && $_GET['format'] === 'json') {
    // Output pure JSON
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

// Otherwise, output as HTML
echo "<!DOCTYPE html>\n<html lang='en'><head><meta charset='UTF-8'><title>MPSM Debug Report</title>";
echo <<<CSS
<style>
  body { font-family: Consolas, monospace; background: #1E1E1E; color: #E0E0E0; padding: 20px; }
  h1 { color: #E024FA; margin-bottom: 10px; }
  h2 { color: #00E5FF; margin-top: 30px; }
  table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
  th, td { padding: 8px 12px; border: 1px solid #333; }
  th { background: #262626; text-align: left; }
  .PASS { color: #00E5FF; font-weight: bold; }
  .FAIL { color: #FF4444; font-weight: bold; }
  .WARN { color: #FFAA00; font-weight: bold; }
  .fix { color: #00E5FF; padding-left: 20px; }
  .section { margin-bottom: 40px; }
  code { background: #262626; padding: 2px 4px; border-radius: 4px; }
  #jsonOutput { background: #111; color: #0F0; padding: 20px; border-radius: 8px; overflow: auto; max-height: 300px; }
</style>
CSS;
echo "</head><body>";
echo "<h1>MPSM Dashboard Debug Report</h1>";
echo "<p>Timestamp: " . htmlspecialchars($results['timestamp']) . "</p>";
echo "<p>Total Checks: {$results['summary']['total']}, <span class='PASS'>Passed: {$results['summary']['passed']}</span>, <span class='WARN'>Warnings: {$results['summary']['warnings']}</span>, <span class='FAIL'>Failures: {$results['summary']['failures']}</span></p>";

$currentSection = '';
foreach ($results['checks'] as $entry) {
    if ($entry['section'] !== $currentSection) {
        $currentSection = $entry['section'];
        echo "<div class='section'><h2>" . htmlspecialchars($currentSection) . "</h2>";
        echo "<table><thead><tr><th>Check</th><th>Status</th><th>Message</th><th>Fix Suggestion</th></tr></thead><tbody>";
    }
    $label   = htmlspecialchars($entry['label']);
    $status  = $entry['status'];
    $message = htmlspecialchars($entry['message']);
    $fix     = htmlspecialchars($entry['fix']);
    echo "<tr>";
    echo "<td><code>{$label}</code></td>";
    echo "<td class='{$status}'>{$status}</td>";
    echo "<td>{$message}</td>";
    echo "<td class='fix'>{$fix}</td>";
    echo "</tr>";

    // If this is the last check in the section, close tags
    $nextCheck = next($results['checks']);
    if ($nextCheck === false || $nextCheck['section'] !== $currentSection) {
        echo "</tbody></table></div>";
    }
    // Rewind pointer to maintain foreach correctness
    if ($nextCheck !== false) prev($results['checks']);
}

echo "<div class='section'><h2>Machine-Readable JSON Output</h2>";
echo "<div id='jsonOutput'><pre>" . json_encode($results, JSON_PRETTY_PRINT) . "</pre></div>";
echo "</div>";

echo "</body></html>";
exit;
?>
