<?php
/**
 * debug.php
 * A robust diagnostic script for the MPSM Dashboard.
 * Drop this into the root folder (same level as index.php) and access via browser.
 * It will check file structure, permissions, PHP settings, and basic API token logic.
 */

echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Debug Report</title>";
echo "<style>
  body { font-family: Consolas, monospace; background: #1E1E1E; color: #E0E0E0; padding: 20px; }
  h1, h2 { color: #E024FA; }
  table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
  th, td { padding: 8px 12px; border: 1px solid #333; }
  th { background: #262626; text-align: left; }
  .pass { color: #00E5FF; }
  .fail { color: #FF006C; }
  .warn { color: #F6FF00; }
  code { background: #262626; padding: 2px 4px; border-radius: 4px; color: #00E5FF; }
</style></head><body>";

echo "<h1>MPSM Dashboard Debug Report</h1>";

// Load check definitions
$configFile = __DIR__ . '/debug_checks.json';
if (!file_exists($configFile)) {
    echo "<p class='fail'>ERROR: <code>debug_checks.json</code> not found in root directory.</p>";
    echo "</body></html>";
    exit;
}
$config = json_decode(file_get_contents($configFile), true);
if (!$config) {
    echo "<p class='fail'>ERROR: <code>debug_checks.json</code> couldn\'t be decoded. JSON error.</p>";
    echo "</body></html>";
    exit;
}

// 1. PHP Version Check
echo "<h2>PHP Version</h2>";
$currentVersion = phpversion();
$minVersion = $config['php_version_min'];
$versionOk = version_compare($currentVersion, $minVersion, '>=');
echo "<p>Current PHP version: <code>$currentVersion</code> ";
echo $versionOk ? "<span class='pass'>(meets minimum $minVersion)</span>" :
                 "<span class='fail'>(below required $minVersion)</span>";
echo "</p>";

// 2. PHP Extension Checks
echo "<h2>PHP Extensions</h2>";
echo "<table><tr><th>Extension</th><th>Status</th></tr>";
foreach ($config['php_extensions'] as $ext) {
    $loaded = extension_loaded($ext);
    echo "<tr><td><code>$ext</code></td>";
    echo $loaded ? "<td class='pass'>Loaded</td>" : "<td class='fail'>Missing</td>";
    echo "</tr>";
}
echo "</table>";

// 3. File & Directory Structure Checks
echo "<h2>File & Directory Checks</h2>";
echo "<table><tr><th>Path</th><th>Type</th><th>Status</th></tr>";
// Files
foreach ($config['files'] as $relPath) {
    $fullPath = __DIR__ . '/' . $relPath;
    $exists = file_exists($fullPath);
    $readable = is_readable($fullPath);
    echo "<tr><td><code>$relPath</code></td><td>File</td>";
    if (!$exists) {
        echo "<td class='fail'>MISSING</td>";
    } elseif (!$readable) {
        echo "<td class='fail'>Not readable</td>";
    } else {
        echo "<td class='pass'>OK</td>";
    }
    echo "</tr>";
}
// Directories
foreach ($config['directories'] as $relPath) {
    $fullPath = __DIR__ . '/' . $relPath;
    $exists = is_dir($fullPath);
    $writable = is_writable($fullPath);
    echo "<tr><td><code>$relPath/</code></td><td>Directory</td>";
    if (!$exists) {
        echo "<td class='fail'>MISSING</td>";
    } elseif (!$writable) {
        echo "<td class='warn'>Exists but not writable</td>";
    } else {
        echo "<td class='pass'>OK</td>";
    }
    echo "</tr>";
}
echo "</table>";

// 4. SCSS vs. CSS timestamp check
echo "<h2>SCSS → CSS Check</h2>";
$scssPath = __DIR__ . '/assets/scss/styles.scss';
$cssPath  = __DIR__ . '/assets/css/styles.css';

if (!file_exists($scssPath)) {
    echo "<p class='fail'><code>assets/scss/styles.scss</code> missing.</p>";
} elseif (!file_exists($cssPath)) {
    echo "<p class='fail'><code>assets/css/styles.css</code> missing (SCSS not compiled).</p>";
} else {
    $scssTime = filemtime($scssPath);
    $cssTime  = filemtime($cssPath);
    if ($cssTime < $scssTime) {
        echo "<p class='warn'><code>styles.css</code> is older than <code>styles.scss</code>—SCSS likely not compiled recently.</p>";
    } else {
        echo "<p class='pass'><code>styles.css</code> is up-to-date with <code>styles.scss</code>.</p>";
    }
}

// 5. API / Token Logic Check
echo "<h2>API & Token Logic Checks</h2>";
$workingPath = __DIR__ . '/Working.php';
if (!file_exists($workingPath)) {
    echo "<p class='warn'><code>Working.php</code> not found. API/token logic cannot be tested.</p>";
} else {
    // Attempt to include and call getAccessToken()
    require_once $workingPath;
    if (!function_exists('getAccessToken')) {
        echo "<p class='fail'><code>getAccessToken()</code> function not defined in Working.php.</p>";
    } else {
        try {
            $token = getAccessToken();
            if ($token && is_string($token)) {
                echo "<p class='pass'><code>getAccessToken()</code> succeeded. Token length: " . strlen($token) . " chars.</p>";
            } else {
                echo "<p class='warn'><code>getAccessToken()</code> returned unexpected value.</p>";
            }
        } catch (Exception $ex) {
            echo "<p class='fail'><code>getAccessToken()</code> threw exception: " . htmlspecialchars($ex->getMessage()) . "</p>";
        } catch (Error $err) {
            echo "<p class='fail'><code>getAccessToken()</code> encountered error: " . htmlspecialchars($err->getMessage()) . "</p>";
        }
    }
    // Check callGetCustomers
    if (!function_exists('callGetCustomers')) {
        echo "<p class='fail'><code>callGetCustomers()</code> function not defined in Working.php.</p>";
    } else {
        echo "<p class='pass'><code>callGetCustomers()</code> exists (not tested for actual API response).</p>";
    }
}

// 6. Database Connection Check (if applicable)
echo "<h2>Database Connection Check</h2>";
// If you have a db config file, test a connection here. For now, report that no DB config is present.
$dbConfigPath = __DIR__ . '/config/database.php';
if (file_exists($dbConfigPath)) {
    echo "<p>Database config found at <code>config/database.php</code>. Attempting connection…</p>";
    try {
        require_once $dbConfigPath;
        // Assumes $dbDsn, $dbUser, $dbPass are set in database.php
        if (!isset($dbDsn)) {
            throw new Exception("Missing \$dbDsn in config/database.php");
        }
        $pdo = new PDO($dbDsn, $dbUser ?? '', $dbPass ?? '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p class='pass'>Database connection successful.</p>";
    } catch (Exception $e) {
        echo "<p class='fail'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p class='warn'><code>config/database.php</code> not found. Skipping DB tests.</p>";
}

// 7. Additional Notes
echo "<h2>Additional Notes</h2>";
echo "<ul>
  <li>If any <span class='fail'>FAIL</span> or <span class='warn'>WARN</span> appears, address those issues before proceeding.</li>
  <li>Make sure to recompile SCSS after any style changes: <code>sass assets/scss/styles.scss assets/css/styles.css</code>.</li>
  <li>To test API endpoints, ensure your <code>.env</code> has correct credentials and <code>Working.php</code> logic is accurate.</li>
  <li>You can expand <code>debug_checks.json</code> with new files or functions as needed.</li>
</ul>";

echo "</body></html>";
```