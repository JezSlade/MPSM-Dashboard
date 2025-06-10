<?php
// public/index.php
// -----------------------------------------------------
// Main entrypoint. Enables full PHP error reporting,
// loads your Swagger spec (AllEndpoints.json),
// extracts every endpoint → passes to JS.
// -----------------------------------------------------

// 1) PHP error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Bootstrap config & debug
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/DebugPanel.php';

// 3) Load and parse AllEndpoints.json (Swagger/OpenAPI)
$specFile = __DIR__ . '/../AllEndpoints.json';
if (!file_exists($specFile)) {
    DebugPanel::log("AllEndpoints.json not found at $specFile");
    $allEndpoints = [];
} else {
    $raw = file_get_contents($specFile);
    $swagger = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        DebugPanel::log("JSON parse error: " . json_last_error_msg());
        $allEndpoints = [];
    } else {
        $allEndpoints = [];
        foreach (($swagger['paths'] ?? []) as $path => $methods) {
            foreach ($methods as $http => $details) {
                $allEndpoints[] = [
                    'method'      => strtoupper($http),
                    'path'        => $path,
                    'summary'     => $details['summary'] ?? '',
                    'description' => $details['description'] ?? ''
                ];
            }
        }
        DebugPanel::log("Extracted " . count($allEndpoints) . " endpoints");
    }
}

// 4) Render page
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>MPSM Dashboard</title>
  <link rel="stylesheet" href="../css/styles.css">
  <script>
    // Inject endpoints & debug flag into JS
    window.allEndpoints = <?php echo json_encode($allEndpoints, JSON_HEX_TAG); ?>;
    window.debugMode    = <?php echo DEBUG_MODE ? 'true' : 'false'; ?>;
  </script>
</head>
<body>
  <!-- Header with connectivity indicators -->
  <header class="glass-panel">
    <div class="status-panel">
      DB: <span id="dbStatus" class="status-dot"></span>
      API: <span id="apiStatus" class="status-dot"></span>
    </div>
  </header>

  <!-- Cards viewport: every endpoint → its own card -->
  <main id="cardsViewport" class="cards-container"></main>

  <!-- Drill-down modal -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <span id="modalClose" class="modal-close">&times;</span>
      <div id="modalBody"></div>
    </div>
  </div>

  <!-- PHP Debug Panel -->
  <?php DebugPanel::output(); ?>

  <script src="../js/app.js"></script>
</body>
</html>
