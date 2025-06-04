<?php
/**
 * debug.php
 *
 * A robust diagnostic script for the MPSM Dashboard.
 * Drop this into the root folder (same level as index.php) and access via browser.
 * It checks:
 *   1. Required files/folders (per debug_checks.json)
 *   2. PHP version & required extensions
 *   3. File/folder permissions (readable/writable)
 *   4. Working.php functions (getAccessToken, API calls, etc.)
 *   5. Basic .env loading (if present)
 *   6. Database connection (if config/database.php exists)
 *   7. Any custom checks you add to debug_checks.json
 *
 * Usage:
 *   1. Ensure debug.php and debug_checks.json are in /public/mpsm/
 *   2. Make debug.php and debug_checks.json world-readable (chmod 644).
 *   3. Visit http://<your-domain>/mpsm/debug.php in a browser.
 */

echo "<!DOCTYPE html>\n<html lang='en'><head><meta charset='UTF-8'><title>MPSM Debug Report</title>";
echo <<<CSS
<style>
  body { font-family: Consolas, monospace; background: #1E1E1E; color: #E0E0E0; padding: 20px; }
  h1 { color: #E024FA; margin-bottom: 10px; }
  h2 { color: #00E5FF; margin-top: 30px; }
  table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
  th, td { padding: 8px 12px; border: 1px solid #333; }
  th { background: #262626; text-align: left; }
  .pass { color: #00E5FF; font-weight: bold; }
  .fail { color: #FF4444; font-weight: bold; }
  .warn { color: #FFAA00; font-weight: bold; }
  .section { margin-bottom: 40px; }
  code { background: #262626; padding: 2px 4px; border-radius: 4px; }
</style>
CSS;
echo "</head><body>";

echo "<h1>MPSM Dashboard Debug Report</h1>";

// ========== SECTION 1: Load debug_checks.json and do file/folder existence checks ==========

echo "<div class='section'>";
echo "<h2>1. File & Folder Existence Checks</h2>";

$json_path = __DIR__ . '/debug_checks.json';
if (!is_readable($json_path)) {
    echo "<p class='fail'>Cannot read <code>debug_checks.json</code> in root. Did you upload it?</p>";
} else {
    $raw = file_get_contents($json_path);
    $cfg = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<p class='fail'>JSON parse error in <code>debug_checks.json</code>: " . json_last_error_msg() . "</p>";
    } else {
        echo "<table><thead><tr><th>Label</th><th>Path</th><th>Status</th></tr></thead><tbody>";
        foreach ($cfg['checks'] as $check) {
            $type = $check['type'];    // currently only "file_exists"
            $path = $check['path'];    // relative path
            $label= $check['label'];   // human label

            $full = __DIR__ . '/' . $path;
            if ($type === 'file_exists') {
                if (file_exists($full)) {
                    // test readability
                    $perm = is_readable($full) ? "<span class='pass'>OK (readable)</span>" : "<span class='warn'>Found but not readable</span>";
                    echo "<tr><td>{$label}</td><td><code>{$path}</code></td><td>{$perm}</td></tr>";
                } else {
                    echo "<tr><td>{$label}</td><td><code>{$path}</code></td><td><span class='fail'>MISSING</span></td></tr>";
                }
            } else {
                echo "<tr><td colspan='3'><span class='warn'>Unknown check type: {$type}</span></td></tr>";
            }
        }
        echo "</tbody></table>";
    }
}
echo "</div>\n"; // end section

// ========== SECTION 2: PHP Configuration ==========
echo "<div class='section'>";
echo "<h2>2. PHP Configuration & Extensions</h2>";
echo "<table><thead><tr><th>Check</th><th>Value</th><th>Status</th></tr></thead><tbody>";

// 2a. PHP Version
$phpVersion = phpversion();
$okVersion  = version_compare($phpVersion, '7.4.0', '>=');
$stat = $okVersion ? "<span class='pass'>{$phpVersion}</span>" : "<span class='fail'>{$phpVersion} (Requires ≥ 7.4)</span>";
echo "<tr><td>PHP Version</td><td>{$phpVersion}</td><td>{$stat}</td></tr>";

// 2b. Required Extensions
$required_exts = ['curl', 'json', 'mbstring'];
foreach ($required_exts as $ext) {
    $loaded = extension_loaded($ext);
    $stat   = $loaded ? "<span class='pass'>Loaded</span>" : "<span class='fail'>Missing</span>";
    echo "<tr><td>Extension: {$ext}</td><td>" . ($loaded ? 'Yes' : 'No') . "</td><td>{$stat}</td></tr>";
}

// 2c. Display Errors Setting
$displayErr = ini_get('display_errors');
$stat = ($displayErr == '1' || strtolower($displayErr) == 'on') ? "<span class='warn'>On (not recommended for production)</span>" : "<span class='pass'>Off</span>";
echo "<tr><td>PHP ini: display_errors</td><td>{$displayErr}</td><td>{$stat}</td></tr>";

// 2d. Server Software
$server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
echo "<tr><td>Server Software</td><td>{$server_software}</td><td><span class='pass'>Info</span></td></tr>";

echo "</tbody></table>";
echo "</div>\n";

// ========== SECTION 3: File/Folder Permissions ==========

echo "<div class='section'>";
echo "<h2>3. Key File & Folder Permissions</h2>";
// Define a few critical items to check writability if needed
$writables = [
    'assets/css/styles.css',
    'assets/js/main.js',
    'modules/Customers/customers.php',
    'config/permissions.php'
];

echo "<table><thead><tr><th>Path</th><th>Readable?</th><th>Writable?</th></tr></thead><tbody>";
foreach ($writables as $rel) {
    $full = __DIR__ . '/' . $rel;
    if (file_exists($full)) {
        $r = is_readable($full) ? "<span class='pass'>Yes</span>" : "<span class='fail'>No</span>";
        $w = is_writable($full) ? "<span class='pass'>Yes</span>" : "<span class='warn'>No</span>";
    } else {
        $r = "<span class='fail'>Missing</span>";
        $w = "<span class='fail'>Missing</span>";
    }
    echo "<tr><td><code>{$rel}</code></td><td>{$r}</td><td>{$w}</td></tr>";
}
echo "</tbody></table>";
echo "</div>\n";

// ========== SECTION 4: .env Loading & Basic Token Logic ==========
echo "<div class='section'>";
echo "<h2>4. .env & API Helper Checks</h2>";

// 4a. Attempt to load .env (if Dotenv is present)
$env_path = __DIR__ . '/.env';
if (file_exists($env_path)) {
    echo "<p><span class='pass'>.env file found.</span> Checking readability… ";
    echo is_readable($env_path) ? "<span class='pass'>OK</span></p>" : "<span class='fail'>Not readable.</span></p>";

    // Attempt to parse a couple of env vars (without needing Dotenv installed)
    $env_contents = file_get_contents($env_path);
    $must_have = ['CLIENT_ID', 'CLIENT_SECRET'];
    echo "<table><thead><tr><th>Env Var</th><th>Present?</th></tr></thead><tbody>";
    foreach ($must_have as $key) {
        $pattern = "/^{$key}=/m";
        $found   = preg_match($pattern, $env_contents);
        $stat    = $found ? "<span class='pass'>Yes</span>" : "<span class='fail'>No</span>";
        echo "<tr><td>{$key}</td><td>{$stat}</td></tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p><span class='warn'>.env file not found. Skipping .env checks.</span></p>";
}

// 4b. Check Working.php exists and some key functions
$working_path = __DIR__ . '/Working.php';
if (file_exists($working_path)) {
    echo "<p><span class='pass'>Working.php found.</span> Attempting to include...</p>";
    try {
        include_once $working_path;
        // List of functions we expect from Working.php
        $expected_fx = ['getAccessToken', 'callGetCustomers'];
        echo "<table><thead><tr><th>Function</th><th>Exists?</th></tr></thead><tbody>";
        foreach ($expected_fx as $fn) {
            $exists = function_exists($fn);
            $stat   = $exists ? "<span class='pass'>Yes</span>" : "<span class='fail'>No</span>";
            echo "<tr><td>{$fn}()</td><td>{$stat}</td></tr>";
        }
        echo "</tbody></table>";
    } catch (\Throwable $e) {
        echo "<p><span class='fail'>Error including Working.php:</span> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p><span class='warn'>Working.php not found. Skipping API helper checks.</span></p>";
}

echo "</div>\n";

// ========== SECTION 5: Database Connection Test (if config/database.php exists) ==========
echo "<div class='section'>";
echo "<h2>5. Database Connection (Optional)</h2>";

// If there’s a config/database.php, try to load it and connect
$db_cfg = __DIR__ . '/config/database.php';
if (file_exists($db_cfg)) {
    echo "<p><span class='pass'>config/database.php found.</span> Loading credentials…</p>";
    try {
        $db = include $db_cfg;
        if (!is_array($db) || !isset($db['host'], $db['username'], $db['password'], $db['dbname'])) {
            echo "<p><span class='fail'>config/database.php did not return expected array (host, username, password, dbname).</span></p>";
        } else {
            echo "<p>Attempting MySQL connection to <code>{$db['host']}</code>…</p>";
            $mysqli = @new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);
            if ($mysqli->connect_errno) {
                echo "<p><span class='fail'>Connection failed:</span> " . htmlspecialchars($mysqli->connect_error) . "</p>";
            } else {
                echo "<p><span class='pass'>Connected successfully to MySQL (host: {$db['host']}).</span></p>";
                $mysqli->close();
            }
        }
    } catch (\Throwable $e) {
        echo "<p><span class='fail'>Error requiring config/database.php:</span> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p><span class='warn'>config/database.php not found. Skipping DB tests.</span></p>";
}

echo "</div>\n";

// ========== SECTION 6: Additional PHP Settings ==========

echo "<div class='section'>";
echo "<h2>6. Additional PHP Settings & Limits</h2>";
echo "<table><thead><tr><th>Setting</th><th>Value</th><th>Recommendation</th></tr></thead><tbody>";

// memory_limit
$mem = ini_get('memory_limit');
echo "<tr><td>memory_limit</td><td>{$mem}</td><td>Typically ≥ 128M</td></tr>";

// max_execution_time
$max_exec = ini_get('max_execution_time');
echo "<tr><td>max_execution_time</td><td>{$max_exec}s</td><td>≥ 30</td></tr>";

// upload_max_filesize
$up_max = ini_get('upload_max_filesize');
echo "<tr><td>upload_max_filesize</td><td>{$up_max}</td><td>Depends on your needs</td></tr>";

echo "</tbody></table>";
echo "</div>\n";

// ========== SECTION 7: Sample API Token Test (if getAccessToken exists) ==========
echo "<div class='section'>";
echo "<h2>7. Sample API Token Request (Dry Run)</h2>";

// Only attempt this if getAccessToken() exists
if (function_exists('getAccessToken')) {
    try {
        echo "<p>Attempting to retrieve a token from the API…</p>";
        $token = getAccessToken(); // May throw on error
        if (is_string($token) && strlen($token) > 10) {
            echo "<p><span class='pass'>Success:</span> Received token string of length " . strlen($token) . ".</p>";
        } else {
            echo "<p><span class='warn'>Warning:</span> getAccessToken() returned something unexpected: " . htmlspecialchars(json_encode($token)) . "</p>";
        }
    } catch (\Throwable $e) {
        echo "<p><span class='fail'>Error during getAccessToken():</span> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p><span class='warn'>getAccessToken() not available. Skipping token test.</span></p>";
}
echo "</div>\n";

// ========== SECTION 8: Summary & Next Steps ==========
echo "<div class='section'>";
echo "<h2>8. Summary & Next Steps</h2>";
echo "<ul>
        <li>If any <span class='fail'>FAIL</span> items appeared above, fix those first (missing files / PHP errors).</li>
        <li>If you saw <span class='warn'>WARNING</span> items (e.g., SCSS not compiled), address them that step.</li>
        <li>Re-run at each major code change to catch newly missing files or broken functions automatically.</li>
        <li>Feel free to add new checks in <code>debug_checks.json</code> as you create new folders/modules.</li>
        <li>Remember to compile SCSS: <code>sass assets/scss/styles.scss assets/css/styles.css</code> after edits.</li>
        <li>Ensure Working.php is updated with correct API credentials and endpoints, then re-run the token test above.</li>
      </ul>";
echo "</div>\n";

echo "</body></html>";
