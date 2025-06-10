<?php
// public/index.php
// -----------------------------------------------------
// Main UI: loads AllEndpoints.json, injects endpoints
// and roleMappings into JS, renders header with beautiful
// role selector & Debug toggle, cards container, modal,
// and a static Debug Panel with Clear.
// -----------------------------------------------------

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/config.php';

// 1) Load swagger spec
$specFile = __DIR__ . '/../AllEndpoints.json';
if (!file_exists($specFile)) {
    $allEndpoints = [];
} else {
    $raw     = file_get_contents($specFile);
    $swagger = json_decode($raw, true);
    $allEndpoints = [];
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
    }
}

// 2) Role → path list mapping
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
    window.allEndpoints  = <?php echo json_encode($allEndpoints, JSON_HEX_TAG); ?>;
    window.roleMappings  = <?php echo json_encode($roleMappings, JSON_HEX_TAG); ?>;
    window.apiBaseUrl    = '<?php echo API_BASE_URL; ?>';
  </script>
</head>
<body>
  <!-- HEADER -->
  <header class="glass-panel">
    <div class="status-panel">
      DB: <span id="dbStatus" class="status-dot"></span>
      API:<span id="apiStatus" class="status-dot"></span>
    </div>
    <select id="roleSelect" class="dropdown"></select>
    <button id="toggleDebug" class="btn">Hide Debug</button>
  </header>

  <!-- CARDS VIEW -->
  <main id="cardsViewport" class="cards-container"></main>

  <!-- MODAL -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <button id="modalClose" class="modal-close btn">×</button>
      <div id="modalBody"></div>
    </div>
  </div>

  <!-- STATIC DEBUG PANEL -->
  <div id="debug-panel" class="debug-panel">
    <div class="debug-header">
      <div class="debug-title">🐛 Debug Console</div>
      <button id="debugClear" class="btn debug-clear">Clear</button>
    </div>
    <div class="debug-content" id="debug-content"></div>
  </div>

  <script src="js/app.js"></script>
</body>
</html>
