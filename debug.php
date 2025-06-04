<?php
/**
 * debug.php
 *
 * Expanded “crazy robust” diagnostic for the MPSM Dashboard skeleton (no SCSS).
 * Now includes:
 *   - Checks for AllEndpoints.json
 *   - .env parsing for API and DB tests
 *   - getAccessToken() + callGetCustomers() tests (Working.php logic)
 *   - DB connection test if DB_* vars defined in .env
 *   - All previous file/permission, CSS link, PHP, include/require checks
 *   - Machine-readable JSON + Copy-to-Clipboard in HTML
 *
 * Usage:
 *   1. Place this file & debug_checks.json in /public/mpsm/
 *   2. Ensure both are world-readable (chmod 644).
 *   3. Populate .env with:
 *        CLIENT_ID=...
 *        CLIENT_SECRET=...
 *        USERNAME=...
 *        PASSWORD=...
 *        SCOPE=...
 *        TOKEN_URL=...
 *        BASE_URL=https://api.abassetmanagement.com/api3
 *        DEALER_CODE=SZ13qRwU5GtFLj0i_CbEgQ2
 *        (Optional DB_HOST, DB_USER, DB_PASS, DB_NAME)
 *   4. Browse to http://<your-domain>/mpsm/debug.php
 *      Append ?format=json for JSON-only.
 */

header(
  'Content-Type: ' .
  ((isset($_GET['format']) && $_GET['format'] === 'json')
    ? 'application/json'
    : 'text/html; charset=UTF-8')
);

$results = [
  'checks'   => [],
  'summary'  => ['total' => 0, 'passed' => 0, 'warnings' => 0, 'failures' => 0],
  'timestamp'=> date('c')
];

// Helper to record a check
function addCheck(&$results, $section, $label, $status, $message = '', $fix = '') {
  $entry = [
    'section' => $section,
    'label'   => $label,
    'status'  => $status,    // PASS, WARN, FAIL
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
      $type = $check['type'];    // “file_exists”
      $path = $check['path'];    // relative
      $label= $check['label'];   // human label
      $full = __DIR__ . '/' . $path;

      if ($type === 'file_exists') {
        if (file_exists($full)) {
          if (is_readable($full)) {
            addCheck($results, $section, $label, 'PASS',
              "Found and readable at \"$path\"."
            );
          } else {
            addCheck($results, $section, $label, 'FAIL',
              "\"$path\" exists but is not readable by PHP.",
              "chmod 644 \"$path\" so the webserver can read it."
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
    addCheck($results, $section, 'CSS Links', 'WARN',
      'No <link rel="stylesheet" href="…"> tags found in index.php.',
      'Ensure index.php references at least one CSS file (assets/css/styles.css).'
    );
  } else {
    foreach ($matches[1] as $href) {
      $hrefClean = explode('?', $href, 2)[0];
      if (preg_match('/^(https?:|\/\/)/i', $hrefClean)) {
        addCheck($results, $section, $hrefClean, 'PASS',
          "External CSS reference—assuming it will load from remote."
        );
        continue;
      }
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
              'Verify that your CSS file was built correctly and not empty.'
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
          "Check the <link> path in index.php or upload the CSS file."
        );
      }
    }
  }
}

/******************** SECTION C: PHP VERSION, EXTENSIONS, & INI SETTINGS ********************/

$section = 'C. PHP Configuration';
$phpVer   = phpversion();
$isPhpOk  = version_compare($phpVer, '7.4.0', '>=');
addCheck($results, $section, 'PHP Version', $isPhpOk ? 'PASS' : 'FAIL',
  "Detected PHP version {$phpVer}.",
  $isPhpOk ? '' : 'Upgrade PHP to ≥ 7.4.'
);

$required_exts = ['curl', 'json', 'mbstring'];
foreach ($required_exts as $ext) {
  $loaded = extension_loaded($ext);
  addCheck($results, $section, "Extension: {$ext}", $loaded ? 'PASS' : 'FAIL',
    $loaded ? "Extension `{$ext}` is loaded." : "Extension `{$ext}` is missing.",
    $loaded ? '' : "Install and enable the PHP `{$ext}` extension."
  );
}

$dispErr = ini_get('display_errors');
$dispErrStatus = ($dispErr == '1' || strtolower($dispErr) == 'on') ? 'WARN' : 'PASS';
addCheck($results, $section, 'PHP ini: display_errors', $dispErrStatus,
  "display_errors = {$dispErr}",
  $dispErrStatus === 'WARN'
    ? 'Turn off display_errors in production (set display_errors = Off in php.ini).'
    : ''
);

$memLimit = ini_get('memory_limit');
addCheck($results, $section, 'PHP ini: memory_limit', 'PASS',
  "memory_limit = {$memLimit} (verify ≥ 128M if needed)."
);

$serverSoft = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
addCheck($results, $section, 'Server Software', 'PASS',
  "Running on `{$serverSoft}`."
);

/******************** SECTION D: RECURSIVE SCAN FOR MISSING include/require ********************/

$section = 'D. Missing include/require Checks';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
foreach ($iterator as $fileInfo) {
  if (!$fileInfo->isFile()) continue;
  if (strtolower($fileInfo->getExtension()) !== 'php') continue;

  $fullPath = $fileInfo->getRealPath();
  $content  = file_get_contents($fullPath);
  preg_match_all(
    '/\b(include|require)(_once)?\s*[\(\'"]([^\'"]+\.php)[\'"]\)?\s*;?/i',
    $content,
    $matches,
    PREG_SET_ORDER
  );
  foreach ($matches as $m) {
    $includedRel = $m[3];
    $baseDir = dirname($fullPath);
    $target  = realpath($baseDir . '/' . $includedRel);
    $projSource = substr($fullPath, strlen(__DIR__) + 1);

    if (!$target || !file_exists($target)) {
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

/********************** SECTION E: AllEndpoints.json VALIDATION **********************/

$section = 'E. AllEndpoints.json Validation';
$endpointsPath = __DIR__ . '/AllEndpoints.json';
if (!file_exists($endpointsPath)) {
  addCheck($results, $section, 'AllEndpoints.json', 'FAIL',
    '"AllEndpoints.json" is missing.',
    'Place AllEndpoints.json (the canonical endpoint definitions) in /public/mpsm/.'
  );
} elseif (!is_readable($endpointsPath)) {
  addCheck($results, $section, 'AllEndpoints.json', 'FAIL',
    '"AllEndpoints.json" exists but is not readable.',
    'chmod 644 "AllEndpoints.json" so the webserver can read it.'
  );
} else {
  $raw = file_get_contents($endpointsPath);
  $json = json_decode($raw, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    addCheck($results, $section, 'AllEndpoints.json', 'FAIL',
      'JSON parse error: ' . json_last_error_msg(),
      'Correct syntax errors in AllEndpoints.json.'
    );
  } elseif (empty($json)) {
    addCheck($results, $section, 'AllEndpoints.json', 'WARN',
      'AllEndpoints.json parsed as an empty structure.',
      'Verify that AllEndpoints.json actually contains endpoint definitions.'
    );
  } else {
    $count = count($json);
    addCheck($results, $section, 'AllEndpoints.json', 'PASS',
      "\"AllEndpoints.json\" parsed successfully with {$count} top-level entries."
    );
  }
}

/********************** SECTION F: .env PARSING & API TESTS **********************/

$section = 'F. .env & API / DB Tests';
$envPath = __DIR__ . '/.env';
$env = [];

if (!file_exists($envPath)) {
  addCheck($results, $section, '.env', 'FAIL',
    '" .env" not found in project root.',
    'Create a .env with the required MPSM and optional DB credentials.'
  );
} elseif (!is_readable($envPath)) {
  addCheck($results, $section, '.env', 'FAIL',
    '" .env" exists but is not readable.',
    'chmod 644 ".env" so PHP can read it.'
  );
} else {
  // Parse .env manually into $env
  $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue; // skip comments
    if (!strpos($line, '=')) continue;
    list($key, $val) = explode('=', $line, 2);
    $env[trim($key)] = trim($val);
  }
  addCheck($results, $section, '.env', 'PASS',
    '" .env" loaded and parsed.'
  );

  // 1) Check required MPSM API vars
  $requiredEnv = ['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','SCOPE','TOKEN_URL','BASE_URL','DEALER_CODE'];
  foreach ($requiredEnv as $key) {
    if (isset($env[$key]) && $env[$key] !== '') {
      addCheck($results, $section, $key, 'PASS',
        "\"{$key}\" = \"{$env[$key]}\"."
      );
    } else {
      addCheck($results, $section, $key, 'FAIL',
        "\"{$key}\" is missing or empty in .env.",
        "Add `{$key}=value` to .env."
      );
    }
  }

  // 2) Attempt to include Working.php and test API calls
  $workingPath = __DIR__ . '/Working.php';
  if (file_exists($workingPath) && is_readable($workingPath)) {
    addCheck($results, $section, 'Working.php', 'PASS',
      '"Working.php" found (reference only). Include to test functions.'
    );
    try {
      include_once $workingPath;
      $funcs = ['getAccessToken','callGetCustomers'];
      foreach ($funcs as $fn) {
        if (function_exists($fn)) {
          addCheck($results, $section, "{$fn}()", 'PASS',
            "Function {$fn}() exists."
          );
        } else {
          addCheck($results, $section, "{$fn}()", 'FAIL',
            "Function {$fn}() missing.",
            "Verify Working.php is unaltered and contains {$fn}()."
          );
        }
      }

      // If getAccessToken() exists, attempt to call it
      if (function_exists('getAccessToken')) {
        try {
          $token = getAccessToken();
          if (is_string($token) && strlen($token) > 10) {
            addCheck($results, $section, 'getAccessToken()', 'PASS',
              "Received token of length " . strlen($token) . "."
            );
            // Next, test callGetCustomers() if function exists
            if (function_exists('callGetCustomers')) {
              try {
                $customers = callGetCustomers($token);
                if (is_array($customers)) {
                  $count = count($customers);
                  addCheck($results, $section, 'callGetCustomers()', 'PASS',
                    "Retrieved {$count} customers from API."
                  );
                } else {
                  addCheck($results, $section, 'callGetCustomers()', 'WARN',
                    "callGetCustomers() returned non-array: " . json_encode($customers),
                    "Verify API credentials and DealerCode."
                  );
                }
              } catch (Throwable $e) {
                addCheck($results, $section, 'callGetCustomers()', 'FAIL',
                  "Exception: " . $e->getMessage(),
                  "Check API endpoint or credentials in .env."
                );
              }
            }
          } else {
            addCheck($results, $section, 'getAccessToken()', 'FAIL',
              "getAccessToken() returned unexpected value: " . htmlspecialchars(json_encode($token)),
              "Verify API URL and credentials in .env."
            );
          }
        } catch (Throwable $e) {
          addCheck($results, $section, 'getAccessToken()', 'FAIL',
            "Exception: " . $e->getMessage(),
            "Check TOKEN_URL, CLIENT_ID, CLIENT_SECRET, USERNAME, PASSWORD, SCOPE in .env."
          );
        }
      }
    } catch (Throwable $e) {
      addCheck($results, $section, 'Working.php include', 'FAIL',
        "Error including Working.php: " . htmlspecialchars($e->getMessage()),
        "Ensure Working.php is valid PHP and readable."
      );
    }
  } else {
    addCheck($results, $section, 'Working.php', 'WARN',
      '"Working.php" not found or not readable. Skipping API function tests.',
      "Place Working.php reference in /public/mpsm/ if you want these tests."
    );
  }

  // 3) Database Connection Test (if DB_* vars exist)
  if (isset($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME'])) {
    addCheck($results, $section, 'Database creds in .env', 'PASS',
      "Found DB_HOST, DB_USER, DB_NAME in .env."
    );
    $mysqli = @new mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME']);
    if ($mysqli->connect_errno) {
      addCheck($results, $section, 'MySQL Connection', 'FAIL',
        "Connection failed: " . htmlspecialchars($mysqli->connect_error),
        "Verify DB_HOST, DB_USER, DB_PASS, DB_NAME in .env."
      );
    } else {
      addCheck($results, $section, 'MySQL Connection', 'PASS',
        "Connected successfully to MySQL (host: {$env['DB_HOST']})."
      );
      $mysqli->close();
    }
  } else {
    addCheck($results, $section, 'DB_* vars', 'WARN',
      "DB_HOST, DB_USER, DB_PASS, or DB_NAME missing in .env. Skipping DB test.",
      "If you need a DB, add those variables to .env."
    );
  }
}

/******************** SECTION G: RECURSIVE SCAN FOR MISSING include/require ********************/

$section = 'G. Missing include/require Checks';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
foreach ($iterator as $fileInfo) {
  if (!$fileInfo->isFile()) continue;
  if (strtolower($fileInfo->getExtension()) !== 'php') continue;

  $fullPath = $fileInfo->getRealPath();
  $content  = file_get_contents($fullPath);
  preg_match_all(
    '/\b(include|require)(_once)?\s*[\(\'"]([^\'"]+\.php)[\'"]\)?\s*;?/i',
    $content,
    $matches,
    PREG_SET_ORDER
  );
  foreach ($matches as $m) {
    $includedRel = $m[3];
    $baseDir = dirname($fullPath);
    $target  = realpath($baseDir . '/' . $includedRel);
    $projSource = substr($fullPath, strlen(__DIR__) + 1);

    if (!$target || !file_exists($target)) {
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

/******************** SECTION H: OUTPUT (HTML + JSON + COPY BUTTON) ********************/

if (isset($_GET['format']) && $_GET['format'] === 'json') {
  echo json_encode($results, JSON_PRETTY_PRINT);
  exit;
}

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
  .fix { color: #00E5FF; padding-left: 20px; font-style: italic; }
  .section { margin-bottom: 40px; }
  code { background: #262626; padding: 2px 4px; border-radius: 4px; }
  #jsonOutput { background: #111; color: #0F0; padding: 20px; border-radius: 8px; overflow: auto; max-height: 300px; }
  #copyButton { margin-bottom: 12px; padding: 8px 12px; border: none; border-radius: 4px; background-color: #00E5FF; color: #1E1E1E; cursor: pointer; font-weight: bold; }
  #copyButton:hover { background-color: #00C4CC; }
</style>
CSS;
echo "</head><body>";
echo "<h1>MPSM Dashboard Debug Report</h1>";
echo "<p>Timestamp: " . htmlspecialchars($results['timestamp']) . "</p>";
echo "<p>Total Checks: {$results['summary']['total']}, "
   . "<span class='PASS'>Passed: {$results['summary']['passed']}</span>, "
   . "<span class='WARN'>Warnings: {$results['summary']['warnings']}</span>, "
   . "<span class='FAIL'>Failures: {$results['summary']['failures']}</span></p>";

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

  $nextEntry = next($results['checks']);
  if ($nextEntry === false || $nextEntry['section'] !== $currentSection) {
    echo "</tbody></table></div>";
  }
  if ($nextEntry !== false) prev($results['checks']);
}

// Section H: Machine-Readable JSON + “Copy to Clipboard”
echo "<div class='section'><h2>Machine-Readable JSON Output</h2>";
echo "<button id='copyButton'>Copy JSON to Clipboard</button>";
echo "<div id='jsonOutput'><pre>" . json_encode($results, JSON_PRETTY_PRINT) . "</pre></div>";
echo "</div>";

echo <<<JS
<script>
  document.getElementById('copyButton').addEventListener('click', function() {
    const jsonText = document.getElementById('jsonOutput').innerText;
    navigator.clipboard.writeText(jsonText).then(function() {
      alert('JSON copied to clipboard!');
    }, function(err) {
      alert('Failed to copy JSON: ' + err);
    });
  });
</script>
JS;

echo "</body></html>";
exit;
?>
