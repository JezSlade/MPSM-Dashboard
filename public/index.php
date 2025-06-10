<?php
// public/index.php
// -----------------------------------------------------
// Entry point: loads AllEndpoints.json & roleMappings,
// injects into JS, renders left sidebar, header/status,
// cards, modal, and Debug Panel.
// -----------------------------------------------------

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/DebugPanel.php';

// 1) Load AllEndpoints.json
$allEndpoints = [];
$specFile = __DIR__ . '/AllEndpoints.json';
if (file_exists($specFile)) {
    $raw     = file_get_contents($specFile);
    $swagger = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
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
    } else {
        DebugPanel::log("Error parsing AllEndpoints.json: " . json_last_error_msg());
    }
} else {
    DebugPanel::log("AllEndpoints.json not found in public/");
}

// 2) Role→paths mapping
$roleMappings = [
  'Developer'  => ['/ApiClient/List'],
  'Admin'      => ['/Analytics/GetReportResult','/ApiClient/List','/Account/GetAccounts','/Account/UpdateProfile'],
  'Dealer'     => ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
  'Service'    => ['/AlertLimit2/GetAllLimits','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
  'Sales'      => ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
  'Accounting' => ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
  'Guest'      => ['/Account/GetProfile','/Account/Logout','/Account/UpdateProfile']
];
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>MPSM Dashboard</title>
  <link rel="stylesheet" href="css/styles.css">

  <script>
    window.allEndpoints = <?php echo json_encode($allEndpoints, JSON_HEX_TAG); ?>;
    window.roleMappings = <?php echo json_encode($roleMappings, JSON_HEX_TAG); ?>;
    window.apiBaseUrl   = '<?php echo API_BASE_URL; ?>';
  </script>
</head>
<body>
  <div class="app-container">
    <!-- SIDEBAR -->
    <aside id="sidebar" class="sidebar"></aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
      <header class="glass-panel header">
        <div class="status-panel">
          DB: <span id="dbStatus" class="status-dot"></span>
          API: <span id="apiStatus" class="status-dot"></span>
        </div>
        <div class="header-right">
          <span class="version-display">v<span id="versionDisplay"></span></span>
          <button id="toggleDebug" class="btn">Hide Debug</button>
        </div>
      </header>

      <main id="cardsViewport" class="cards-container"></main>
    </div>
  </div>

  <!-- MODAL -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <button id="modalClose" class="btn modal-close">×</button>
      <div id="modalBody"></div>
    </div>
  </div>

  <!-- DEBUG PANEL -->
  <div id="debug-panel" class="debug-panel">
    <div class="debug-header">
      <div class="debug-title">🐛 Debug Console</div>
      <button id="debugClear" class="btn debug-clear">Clear</button>
    </div>
    <div class="debug-content" id="debug-content"></div>
  </div>

  <script src="version.js"></script>
  <script src="js/app.js"></script>
  <script>
    // Populate version
    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('versionDisplay').textContent = window.appVersion || 'n/a';
    });
  </script>
</body>
</html>
