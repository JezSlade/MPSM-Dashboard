<?php
// public/index.php
// -----------------------------------------------------
// Main UI: loads AllEndpoints.json, passes data & config
// into JS, renders header with Debug toggle, cards,
// modal, and the PHP Debug Panel.
// -----------------------------------------------------

// 1) PHP error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Bootstrap config & debug
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/DebugPanel.php';

// 3) Load and parse AllEndpoints.json
$specFile = __DIR__ . '/../AllEndpoints.json';
if (!file_exists($specFile)) {
    DebugPanel::log("AllEndpoints.json not found at $specFile");
    $allEndpoints = [];
} else {
    $raw     = file_get_contents($specFile);
    $swagger = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        DebugPanel::log("Swagger JSON parse error: " . json_last_error_msg());
        $allEndpoints = [];
    } else {
        $allEndpoints = [];
        foreach (($swagger['paths'] ?? []) as $path => $methods) {
            foreach ($methods as $http => $details) {
                $allEndpoints[] = [
                    'method'      => strtoupper($http),
                    'path'        => $path,
                    'summary'     => $details['summary']     ?? '',
                    'description' => $details['description'] ?? ''
                ];
            }
        }
        DebugPanel::log("Extracted " . count($allEndpoints) . " endpoints");
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>MPSM Dashboard</title>
  <link rel="stylesheet" href="css/styles.css">

  <!-- Pass data & config into JS -->
  <script>
    window.allEndpoints = <?php echo json_encode($allEndpoints, JSON_HEX_TAG); ?>;
    window.debugMode    = <?php echo DEBUG_MODE ? 'true' : 'false'; ?>;
    window.apiBaseUrl   = '<?php echo API_BASE_URL; ?>';
  </script>
</head>
<body>
  <!-- HEADER -->
  <header class="glass-panel">
    <div class="status-panel">
      DB: <span id="dbStatus" class="status-dot"></span>
      API:<span id="apiStatus" class="status-dot"></span>
    </div>
    <button id="toggleDebug" class="debug-toggle">Toggle Debug</button>
  </header>

  <!-- CARDS VIEW -->
  <main id="cardsViewport" class="cards-container"></main>

  <!-- DRILLDOWN MODAL -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <span id="modalClose" class="modal-close">&times;</span>
      <div id="modalBody"></div>
    </div>
  </div>

  <!-- PHP Debug Panel -->
  <?php DebugPanel::output(); ?>

  <script src="js/app.js"></script>
</body>
</html>
