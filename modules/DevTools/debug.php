<?php
/**
 * modules/DevTools/debug.php
 *
 * Auto-discovering, cached diagnostic for MPSM, now as a module.
 */

if (!defined('ROOT_DIR')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}

define('CACHE_FILE', ROOT_DIR . '/debug_cache.json');

$results = [
  'checks'   => [],
  'summary'  => ['total'=>0, 'passed'=>0, 'warnings'=>0, 'failures'=>0],
  'timestamp'=> date('c')
];

function addCheck(&$results, $section, $label, $status, $message = '', $fix = '') {
  $results['checks'][] = compact('section','label','status','message','fix');
  $results['summary']['total']++;
  if      ($status === 'PASS')  $results['summary']['passed']++;
  elseif  ($status === 'WARN')  $results['summary']['warnings']++;
  else                           $results['summary']['failures']++;
}

// Load or initialize cache
$cacheData = [];
if (file_exists(CACHE_FILE) && is_readable(CACHE_FILE)) {
  $raw = file_get_contents(CACHE_FILE);
  $cacheData = json_decode($raw, true) ?: [];
}

// Recursively scan files under ROOT_DIR (skip node_modules, vendor, .git)
$skipDirs = ['node_modules','vendor','.git'];
$iterator = new RecursiveIteratorIterator(
  new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator(ROOT_DIR),
    function ($fileInfo, $key, $iter) use ($skipDirs) {
      if ($fileInfo->isDir()) {
        foreach ($skipDirs as $d) {
          if (strpos($fileInfo->getPathname(), "/{$d}") !== false) {
            return false;
          }
        }
      }
      return true;
    }
  )
);

foreach ($iterator as $fileInfo) {
  if (!$fileInfo->isFile()) continue;

  $relPath = substr($fileInfo->getPathname(), strlen(ROOT_DIR) + 1);
  $mtime   = $fileInfo->getMTime();
  $size    = $fileInfo->getSize();
  $ext     = strtolower($fileInfo->getExtension());

  // If cached and unchanged, reuse
  if (isset($cacheData[$relPath]) && $cacheData[$relPath]['mtime'] === $mtime) {
    foreach ($cacheData[$relPath]['status'] as $cached) {
      addCheck(
        $results,
        $cached['section'],
        $cached['label'],
        $cached['status'],
        $cached['message'],
        $cached['fix']
      );
    }
    continue;
  }

  $fileChecks = [];

  // Existence / readability
  if (is_readable($fileInfo->getPathname())) {
    $fileChecks[] = [
      'section'=>'A. File Existence & Permissions',
      'label'  =>$relPath,
      'status' =>'PASS',
      'message'=>"Found and readable.",
      'fix'    =>''
    ];
  } else {
    $fileChecks[] = [
      'section'=>'A. File Existence & Permissions',
      'label'  =>$relPath,
      'status' =>'FAIL',
      'message'=> "\"{$relPath}\" exists but is not readable by PHP.",
      'fix'    => "chmod 644 \"{$relPath}\""
    ];
  }

  // Extension-specific checks
  switch ($ext) {
    case 'php':
      $content = file_get_contents($fileInfo->getPathname());
      preg_match_all(
        '/\b(include|require)(_once)?\s*[\(\'"]([^\'"]+\.php)[\'"]\)?\s*;?/i',
        $content,
        $matches,
        PREG_SET_ORDER
      );
      foreach ($matches as $m) {
        $includedRel = $m[3];
        $baseDir     = dirname($fileInfo->getRealPath());
        $target      = realpath($baseDir . '/' . $includedRel);
        $label       = "{$relPath} → include \"{$includedRel}\"";

        if (!$target || !file_exists($target)) {
          $fileChecks[] = [
            'section'=>'D. Missing include/require Checks',
            'label'  =>$label,
            'status' =>'FAIL',
            'message'=> "\"{$includedRel}\" not found (referenced in {$relPath}).",
            'fix'    => "Ensure `{$includedRel}` exists relative to `{$relPath}`."
          ];
        } else {
          $fileChecks[] = [
            'section'=>'D. Missing include/require Checks',
            'label'  =>$label,
            'status' =>'PASS',
            'message'=> "\"{$includedRel}\" found for {$relPath}."
          ];
        }
      }
      break;

    case 'css':
      if ($relPath === 'assets/css/styles.css') {
        if ($size > 0) {
          $fileChecks[] = [
            'section'=>'B. CSS <link> References',
            'label'  =>$relPath,
            'status' =>'PASS',
            'message'=> "Size={$size} bytes."
          ];
        } else {
          $fileChecks[] = [
            'section'=>'B. CSS <link> References',
            'label'  =>$relPath,
            'status' =>'FAIL',
            'message'=> "\"{$relPath}\" is zero bytes.",
            'fix'    =>'Verify your CSS file was built correctly.'
          ];
        }
      }
      break;

    case 'json':
      if (in_array($relPath, ['AllEndpoints.json','modules/DevTools/debug_checks.json'])) {
        $raw = file_get_contents($fileInfo->getPathname());
        $j   = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
          $fileChecks[] = [
            'section'=>'E. AllEndpoints.json Validation',
            'label'  =>$relPath,
            'status' =>'FAIL',
            'message'=> 'JSON parse error: ' . json_last_error_msg(),
            'fix'    => "Fix syntax in {$relPath}."
          ];
        } elseif (empty($j)) {
          $fileChecks[] = [
            'section'=>'E. AllEndpoints.json Validation',
            'label'  =>$relPath,
            'status' =>'WARN',
            'message'=> "{$relPath} parsed as empty."
          ];
        } else {
          $cnt = count($j);
          $fileChecks[] = [
            'section'=>'E. AllEndpoints.json Validation',
            'label'  =>$relPath,
            'status' =>'PASS',
            'message'=> "{$relPath} parsed with {$cnt} entries."
          ];
        }
      }
      break;

    case 'env':
      if ($relPath === '.env') {
        $lines = file($fileInfo->getPathname(), FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        $env   = [];
        foreach ($lines as $line) {
          if (strpos(trim($line), '#') === 0) continue;
          if (!strpos($line, '=')) continue;
          list($k,$v) = explode('=', $line, 2);
          $env[trim($k)] = trim($v);
        }
        $fileChecks[] = [
          'section'=>'F. .env & API / DB Tests',
          'label'  =>'.env',
          'status' =>'PASS',
          'message'=> '".env" loaded and parsed.'
        ];

        $requiredEnv = [
          'CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD',
          'SCOPE','TOKEN_URL','BASE_URL','DEALER_CODE'
        ];
        foreach ($requiredEnv as $k) {
          if (!isset($env[$k]) || $env[$k] === '') {
            $fileChecks[] = [
              'section'=>'F. .env & API / DB Tests',
              'label'  =>$k,
              'status' =>'FAIL',
              'message'=> "\"{$k}\" missing in .env.",
              'fix'    => "Add `{$k}=…` to .env."
            ];
          } else {
            $fileChecks[] = [
              'section'=>'F. .env & API / DB Tests',
              'label'  =>$k,
              'status' =>'PASS',
              'message'=> "{$k} = \"{$env[$k]}\"."
            ];
          }
        }

        $workingPath = ROOT_DIR . '/Working.php';
        if (file_exists($workingPath) && is_readable($workingPath)) {
          $fileChecks[] = [
            'section'=>'F. .env & API / DB Tests',
            'label'  =>'Working.php',
            'status' =>'PASS',
            'message'=> '"Working.php" found—for API tests.'
          ];
          try {
            include_once $workingPath;
            foreach (['getAccessToken','callGetCustomers'] as $fn) {
              if (function_exists($fn)) {
                $fileChecks[] = [
                  'section'=>'F. .env & API / DB Tests',
                  'label'  => "{$fn}()",
                  'status' =>'PASS',
                  'message'=> "{$fn}() exists."
                ];
              } else {
                $fileChecks[] = [
                  'section'=>'F. .env & API / DB Tests',
                  'label'  => "{$fn}()",
                  'status' =>'FAIL',
                  'message'=> "{$fn}() missing.",
                  'fix'    => "Verify Working.php contains {$fn}()."
                ];
              }
            }

            if (function_exists('getAccessToken')) {
              try {
                $token = getAccessToken();
                if (is_string($token) && strlen($token) > 10) {
                  $fileChecks[] = [
                    'section'=>'F. .env & API / DB Tests',
                    'label'  => 'getAccessToken()',
                    'status' =>'PASS',
                    'message'=> "Received token length " . strlen($token) . "."
                  ];
                  if (function_exists('callGetCustomers')) {
                    try {
                      $cust = callGetCustomers($token);
                      if (is_array($cust)) {
                        $n = count($cust);
                        $fileChecks[] = [
                          'section'=>'F. .env & API / DB Tests',
                          'label'  => 'callGetCustomers()',
                          'status' =>'PASS',
                          'message'=> "Retrieved {$n} customers."
                        ];
                      } else {
                        $fileChecks[] = [
                          'section'=>'F. .env & API / DB Tests',
                          'label'  => 'callGetCustomers()',
                          'status' =>'WARN',
                          'message'=> "Returned non-array."
                        ];
                      }
                    } catch (Throwable $e) {
                      $fileChecks[] = [
                        'section'=>'F. .env & API / DB Tests',
                        'label'  => 'callGetCustomers()',
                        'status' =>'FAIL',
                        'message'=> "Exception: " . $e->getMessage(),
                        'fix'    => "Check API endpoint or credentials."
                      ];
                    }
                  }
                } else {
                  $fileChecks[] = [
                    'section'=>'F. .env & API / DB Tests',
                    'label'  => 'getAccessToken()',
                    'status' =>'FAIL',
                    'message'=> "Unexpected return: " . htmlspecialchars(json_encode($token)),
                    'fix'    => "Verify `.env` TOKEN_URL, CLIENT_ID, etc."
                  ];
                }
              } catch (Throwable $e) {
                $fileChecks[] = [
                  'section'=>'F. .env & API / DB Tests',
                  'label'  => 'getAccessToken()',
                  'status' =>'FAIL',
                  'message'=> "Exception: " . $e->getMessage(),
                  'fix'    => "Check `.env` values."
                ];
              }
            }
          } catch (Throwable $e) {
            $fileChecks[] = [
              'section'=>'F. .env & API / DB Tests',
              'label'  => 'Working.php include',
              'status' =>'FAIL',
              'message'=> "Error including Working.php: " . htmlspecialchars($e->getMessage()),
              'fix'    => "Ensure Working.php is valid PHP."
            ];
          }
        } else {
          $fileChecks[] = [
            'section'=>'F. .env & API / DB Tests',
            'label'  => 'Working.php',
            'status' =>'WARN',
            'message'=> '"Working.php" not found; skipping API tests.'
          ];
        }

        if (isset($env['DB_HOST'],$env['DB_USER'],$env['DB_PASS'],$env['DB_NAME'])) {
          $fileChecks[] = [
            'section'=>'F. .env & API / DB Tests',
            'label'  => 'DB_* vars',
            'status' =>'PASS',
            'message'=> "Found DB_HOST, DB_USER, DB_NAME."
          ];
          $mysqli = @new mysqli($env['DB_HOST'],$env['DB_USER'],$env['DB_PASS'],$env['DB_NAME']);
          if ($mysqli->connect_errno) {
            $fileChecks[] = [
              'section'=>'F. .env & API / DB Tests',
              'label'  => 'MySQL Connection',
              'status' =>'FAIL',
              'message'=> "Conn failed: " . htmlspecialchars($mysqli->connect_error),
              'fix'    => "Verify DB_HOST, DB_USER, DB_NAME."
            ];
          } else {
            $fileChecks[] = [
              'section'=>'F. .env & API / DB Tests',
              'label'  => 'MySQL Connection',
              'status' =>'PASS',
              'message'=> "Connected to MySQL (host: {$env['DB_HOST']})."
            ];
            $mysqli->close();
          }
        } else {
          $fileChecks[] = [
            'section'=>'F. .env & API / DB Tests',
            'label'  => 'DB_* vars',
            'status' =>'WARN',
            'message'=> 'DB_HOST, DB_USER, DB_NAME missing; skipping DB test.'
          ];
        }
      }
      break;

    default:
      break;
  }

  // Cache results
  $cacheData[$relPath] = [
    'mtime'  => $mtime,
    'status' => $fileChecks
  ];

  // Add to overall
  foreach ($fileChecks as $c) {
    addCheck($results, $c['section'], $c['label'], $c['status'], $c['message'], $c['fix'] ?? '');
  }
}

// Save cache
file_put_contents(CACHE_FILE, json_encode($cacheData, JSON_PRETTY_PRINT));

// Output
if (isset($_GET['format']) && $_GET['format'] === 'json') {
  echo json_encode($results, JSON_PRETTY_PRINT);
  exit;
}

$grouped = [];
foreach ($results['checks'] as $entry) {
  $section = $entry['section'];
  if (!isset($grouped[$section])) {
    $grouped[$section] = [];
  }
  $grouped[$section][] = $entry;
}

echo "<!DOCTYPE html>\n<html lang='en'><head><meta charset='UTF-8'><title>Dev Tools: Debug Report</title>";
echo <<<CSS
<style>
  body { font-family: Consolas, monospace; background: #1E1E1E; color: #E0E0E0; padding: 20px; }
  h1 { color: #E024FA; margin-bottom: 10px; }
  h2 { color: #00E5FF; margin-top: 30px; }
  table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
  th, td { padding: 8px 12px; border: 1px solid #555; vertical-align: top; }
  th { background: #333; color: #fff; text-align: left; }
  tr:nth-child(even) { background: #2a2a2a; }
  .PASS { color: #00E5FF; font-weight: bold; }
  .FAIL { color: #FF4444; font-weight: bold; }
  .WARN { color: #FFAA00; font-weight: bold; }
  .fix { color: #00E5FF; padding-left: 12px; font-style: italic; }
  .section { margin-bottom: 40px; }
  code { background: #262626; padding: 2px 4px; border-radius: 4px; }
  #jsonOutput { background: #111; color: #0F0; padding: 20px; border-radius: 8px; overflow: auto; max-height: 300px; }
  #copyButton { margin-bottom: 12px; padding: 8px 12px; border: none; border-radius: 4px; background-color: #00E5FF; color: #1E1E1E; cursor: pointer; font-weight: bold; }
  #copyButton:hover { background-color: #00C4CC; }
</style>
CSS;
echo "</head><body>";

echo "<h1>Dev Tools: Debug Report</h1>";
echo "<p>Timestamp: " . htmlspecialchars($results['timestamp']) . "</p>";
echo "<p>Total Checks: {$results['summary']['total']}, "
   . "<span class='PASS'>Passed: {$results['summary']['passed']}</span>, "
   . "<span class='WARN'>Warnings: {$results['summary']['warnings']}</span>, "
   . "<span class='FAIL'>Failures: {$results['summary']['failures']}</span></p>";

foreach ($grouped as $section => $entries) {
  echo "<div class='section'><h2>" . htmlspecialchars($section) . "</h2>";
  echo "<table>";
  echo "<thead><tr><th style='width:30%'>Check</th><th style='width:10%'>Status</th><th style='width:40%'>Message</th><th style='width:20%'>Fix Suggestion</th></tr></thead><tbody>";
  foreach ($entries as $e) {
    $label   = htmlspecialchars($e['label']);
    $status  = $e['status'];
    $message = htmlspecialchars($e['message']);
    $fix     = htmlspecialchars($e['fix'] ?? '');
    echo "<tr>";
    echo "<td><code>{$label}</code></td>";
    echo "<td class='{$status}'>{$status}</td>";
    echo "<td>{$message}</td>";
    echo "<td class='fix'>{$fix}</td>";
    echo "</tr>";
  }
  echo "</tbody></table></div>";
}

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
