<?php
// File: diagnose.php
// ------------------
// Purpose: Display environment diagnostics, check file structure & permissions,
// verify required PHP extensions, list available MPS Monitor API endpoints,
// and test API authentication. Uses shared header/footer and the neon‐on‐black stylesheet.

// 1. Set a custom page title
$pageTitle = 'MPSM Dashboard ‐ Diagnose';

// 2. Include the shared header (loads CSS, navigation, opens <body><main>)
require_once __DIR__ . '/header.php';
?>

  <!-- ============================ -->
  <!-- 1. Environment Information   -->
  <!-- ============================ -->
  <div class="card">
    <h2 class="sub-title">Environment Info</h2>
    <ul style="margin-top: 1rem; line-height: 1.6;">
      <li><strong>PHP Version:</strong> <?= htmlspecialchars(phpversion()) ?></li>
      <li><strong>Server Software:</strong> <?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') ?></li>
      <li><strong>Document Root:</strong> <?= htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') ?></li>
      <li><strong>Current File:</strong> <?= htmlspecialchars(__FILE__) ?></li>
      <li><strong>Working Directory:</strong> <?= htmlspecialchars(getcwd()) ?></li>
    </ul>
  </div>

  <!-- ============================================== -->
  <!-- 2. File Structure & Permissions Check          -->
  <!-- ============================================== -->
  <div class="card" style="margin-top: 1.5rem;">
    <h2 class="sub-title">File Structure & Permissions</h2>
    <?php
    // List of critical files/folders to check
    $checks = [
      'Header Include'         => ['path' => __DIR__ . '/header.php',      'writable' => false],
      'Footer Include'         => ['path' => __DIR__ . '/footer.php',      'writable' => false],
      'AllEndpoints.json'      => ['path' => __DIR__ . '/AllEndpoints.json','writable' => false],
      'working_token.php'      => ['path' => __DIR__ . '/working_token.php','writable' => false],
      'Style Sheet'            => ['path' => __DIR__ . '/assets/css/style.css','writable' => false],
      'JavaScript (app.js)'    => ['path' => __DIR__ . '/assets/js/app.js', 'writable' => false],
      'Assets/CSS Directory'   => ['path' => __DIR__ . '/assets/css',       'writable' => true],
      'Assets/JS Directory'    => ['path' => __DIR__ . '/assets/js',        'writable' => true],
    ];

    echo '<ul style="margin-top: 1rem; line-height: 1.6;">';
    foreach ($checks as $label => $info) {
        $path = $info['path'];
        $exists = file_exists($path);
        $readable = $exists && is_readable($path);
        $writable = $exists && is_writable($path);
        echo '<li>';
        if (!$exists) {
            echo '<span class="icon-warning">⚠️</span> ';
            echo "<strong>{$label}:</strong> <span style=\"color: #ff9000;\">Missing (“{$path}”).</span>";
        } else {
            // If it’s supposed to be writable, check writability; otherwise just check readability.
            if ($info['writable']) {
                if ($writable) {
                    echo '<span style="color: #00ffcc;">✔️</span> ';
                    echo "<strong>{$label}:</strong> Exists and writable.";
                } else {
                    echo '<span class="icon-warning">⚠️</span> ';
                    echo "<strong>{$label}:</strong> Exists but <span style=\"color: #ff9000;\">not writable</span> (“{$path}”).";
                }
            } else {
                if ($readable) {
                    echo '<span style="color: #00ffcc;">✔️</span> ';
                    echo "<strong>{$label}:</strong> Exists and readable.";
                } else {
                    echo '<span class="icon-warning">⚠️</span> ';
                    echo "<strong>{$label}:</strong> Exists but <span style=\"color: #ff9000;\">not readable</span> (“{$path}”).";
                }
            }
        }
        echo '</li>';
    }
    echo '</ul>';
    ?>
  </div>

  <!-- ============================================== -->
  <!-- 3. Dependencies & PHP Extensions Check        -->
  <!-- ============================================== -->
  <div class="card" style="margin-top: 1.5rem;">
    <h2 class="sub-title">Dependencies & PHP Extensions</h2>
    <div style="margin-top: 1rem;">
      <?php
      // Define extensions that are required for MPSM Dashboard functionality
      $required_exts = ['curl', 'json', 'openssl'];
      echo '<ul style="margin-top: 0.5rem; line-height: 1.6;">';
      foreach ($required_exts as $ext) {
          if (extension_loaded($ext)) {
              echo '<li><span style="color: #00ffcc;">✔️</span> PHP extension <code>' 
                   . htmlspecialchars($ext) 
                   . '</code> is loaded.</li>';
          } else {
              echo '<li><span class="icon-warning">⚠️</span> PHP extension <code>' 
                   . htmlspecialchars($ext) 
                   . '</code> is <span style="color: #ff9000;">missing</span>.</li>';
          }
      }
      echo '</ul>';

      // Also list all loaded extensions (scrollable if many):
      echo '<h4 style="margin-top: 1rem;">All Loaded PHP Extensions</h4>';
      echo '<div style="max-height: 200px; overflow-y: auto; margin-top: 0.5rem;">';
      echo '<ul style="list-style: none; padding: 0; line-height: 1.6;">';
      foreach (get_loaded_extensions() as $loaded) {
          echo '<li>' . htmlspecialchars($loaded) . '</li>';
      }
      echo '</ul></div>';
      ?>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- 4. Available MPS Monitor API Endpoints          -->
  <!-- ============================================== -->
  <div class="card" style="margin-top: 1.5rem;">
    <h2 class="sub-title">Available API Endpoints</h2>
    <?php
    $endpointsPath = __DIR__ . '/AllEndpoints.json';
    $endpoints    = [];
    $endpointsRaw = @file_get_contents($endpointsPath);
    if ($endpointsRaw !== false) {
        $endpoints = json_decode($endpointsRaw, true);
    }
    ?>
    <?php if (!empty($endpoints) && is_array($endpoints)): ?>
      <table class="table" style="margin-top: 1rem;">
        <thead>
          <tr>
            <th>Key</th>
            <th>HTTP Method</th>
            <th>URL</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($endpoints as $key => $ep): ?>
            <tr>
              <td><?= htmlspecialchars($key) ?></td>
              <td><?= htmlspecialchars($ep['method'] ?? '') ?></td>
              <td style="word-break: break-all;"><?= htmlspecialchars($ep['url'] ?? '') ?></td>
              <td><?= htmlspecialchars($ep['description'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p style="margin-top: 1rem; color: #ff9000;">
        <span class="icon-warning">⚠️</span> Unable to load or parse <code>AllEndpoints.json</code> at <code><?= htmlspecialchars($endpointsPath) ?></code>.
      </p>
    <?php endif; ?>
  </div>

  <!-- ============================================== -->
  <!-- 5. API Authentication Test                       -->
  <!-- ============================================== -->
  <div class="card" style="margin-top: 1.5rem;">
    <h2 class="sub-title">API Authentication Test</h2>
    <div style="margin-top: 1rem;">
      <?php
      // Attempt to retrieve an access token using the existing working_token logic.
      $token = null;
      if (file_exists(__DIR__ . '/working_token.php')) {
          require_once __DIR__ . '/working_token.php';
          try {
              $token = getAccessToken();
              if (!empty($token)) {
                  echo '<p style="color: #00ffcc;"><strong>✔️ Access token retrieved successfully.</strong></p>';
                  echo '<pre style="background: #222; padding: 1rem; border-radius: 6px; overflow-x: auto; color: #fafbfe;">'
                     . htmlspecialchars($token)
                     . '</pre>';
              } else {
                  echo '<p style="color: #ff9000;"><span class="icon-warning">⚠️ Failed to retrieve access token.</span></p>';
              }
          } catch (Exception $e) {
              echo '<p style="color: #ff9000;"><span class="icon-warning">⚠️ Exception during token retrieval:</span> '
                   . htmlspecialchars($e->getMessage())
                   . '</p>';
          }
      } else {
          echo '<p style="color: #ff9000;"><span class="icon-warning">⚠️ Cannot find <code>working_token.php</code> in project root.</span></p>';
      }
      ?>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- 6. Sample API Call: Customer/GetCustomers       -->
  <!-- ============================================== -->
  <div class="card" style="margin-top: 1.5rem;">
    <h2 class="sub-title">Sample API Call: Customer/GetCustomers</h2>
    <div style="margin-top: 1rem;">
      <?php
      if (!empty($token) && is_string($token)) {
          $apiBaseUrl = 'https://api.abassetmanagement.com/api3/Customer/GetCustomers';
          $dealerCode = 'SZ13qRwU5GtFLj0i_CbEgQ2';
          $payload    = [
            'DealerCode' => $dealerCode,
            'PageNumber' => 1,
            'PageRows'   => 10,
            'SortColumn' => 'Code',
            'SortOrder'  => 'Asc'
          ];

          $ch = curl_init($apiBaseUrl);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
          ]);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
          curl_setopt($ch, CURLOPT_TIMEOUT, 10);

          $response = curl_exec($ch);
          $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $curlErr  = curl_error($ch);
          curl_close($ch);

          if ($curlErr) {
              echo '<p style="color: #ff9000;"><span class="icon-warning">⚠️ cURL Error:</span> '
                   . htmlspecialchars($curlErr)
                   . '</p>';
          } elseif ($httpCode !== 200) {
              echo '<p style="color: #ff9000;"><span class="icon-warning">⚠️ HTTP Error ('
                   . htmlspecialchars((string)$httpCode)
                   . ')</span></p>';
              echo '<pre style="background: #222; padding: 1rem; border-radius: 6px; overflow-x: auto; color: #fafbfe;">'
                   . htmlspecialchars($response)
                   . '</pre>';
          } else {
              $jsonData = json_decode($response, true);
              if (json_last_error() === JSON_ERROR_NONE) {
                  echo '<p style="color: #00ffcc;"><strong>✔️ /Customer/GetCustomers returned successfully (HTTP 200).</strong></p>';
                  echo '<div style="max-height: 300px; overflow-y: auto; margin-top: 1rem;">';
                  echo '<table class="table">';
                  echo '<thead><tr><th>Code</th><th>Description</th><th>IsActive</th></tr></thead>';
                  echo '<tbody>';
                  foreach ($jsonData['Result'] ?? [] as $cust) {
                      echo '<tr>';
                      echo '<td>' . htmlspecialchars($cust['Code'] ?? '') . '</td>';
                      echo '<td>' . htmlspecialchars($cust['Description'] ?? '') . '</td>';
                      echo '<td>' . ((isset($cust['IsActive']) && $cust['IsActive']) ? 'Yes' : 'No') . '</td>';
                      echo '</tr>';
                  }
                  echo '</tbody></table>';
                  echo '</div>';
              } else {
                  echo '<p style="color: #ff9000;"><span class="icon-warning">⚠️ Failed to decode JSON response.</span></p>';
                  echo '<pre style="background: #222; padding: 1rem; border-radius: 6px; overflow-x: auto; color: #fafbfe;">'
                       . htmlspecialchars($response)
                       . '</pre>';
              }
          }
      } else {
          echo '<p style="color: #ff9000;"><span class="icon-warning">⚠️ Skipping API call because no valid token was obtained above.</span></p>';
      }
      ?>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- 7. PHP Configuration & Extensions              -->
  <!-- ============================================== -->
  <div class="card" style="margin-top: 1.5rem; margin-bottom: 2rem;">
    <h2 class="sub-title">PHP Configuration & Extensions</h2>
    <div style="margin-top: 1rem;">
      <?php
      $phpSettings = [
          'display_errors'      => ini_get('display_errors'),
          'error_reporting'     => ini_get('error_reporting'),
          'memory_limit'        => ini_get('memory_limit'),
          'max_execution_time'  => ini_get('max_execution_time'),
          'post_max_size'       => ini_get('post_max_size'),
          'upload_max_filesize' => ini_get('upload_max_filesize')
      ];

      echo '<table class="table">';
      echo '<thead><tr><th>Setting</th><th>Value</th></tr></thead><tbody>';
      foreach ($phpSettings as $setting => $value) {
          echo '<tr>';
          echo '<td>' . htmlspecialchars($setting) . '</td>';
          echo '<td>' . htmlspecialchars($value) . '</td>';
          echo '</tr>';
      }
      echo '</tbody></table>';
      ?>
    </div>
  </div>

<?php
// 8. Include the shared footer (closes </main>, adds footer, closes </body></html>)
require_once __DIR__ . '/footer.php';
?>
