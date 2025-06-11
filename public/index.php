// public/index.php
<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/DebugPanel.php';

// Load endpoints from Swagger
$allEndpoints = [];
$specFile     = __DIR__ . '/AllEndpoints.json';
if (file_exists($specFile)) {
    $swagger = json_decode(file_get_contents($specFile), true);
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

// Role → endpoint mapping
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
    // Expose data for JS
    window.allEndpoints  = <?php echo json_encode($allEndpoints, JSON_HEX_TAG); ?>;
    window.roleMappings  = <?php echo json_encode($roleMappings, JSON_HEX_TAG); ?>;
    window.apiBaseUrl    = '<?php echo API_BASE_URL; ?>';
    window.dealerCode    = '<?php echo DEALER_CODE; ?>';  // <-- expose dealer code
  </script>
</head>
<body>
  <!-- FIXED HEADER -->
  <header class="glass-panel header">
    <div class="status-panel">
      DB: <span id="dbStatus" class="status-dot"></span>
      API: <span id="apiStatus" class="status-dot"></span>
    </div>

    <!-- Customer search bar with datalist -->
    <div class="header-search">
      <input
        type="text"
        id="customerInput"
        class="customer-search"
        list="customerList"
        placeholder="Search Customer…"
        aria-label="Filter by customer"
      />
      <datalist id="customerList"></datalist>
    </div>

    <div class="header-right">
      <span class="version-display">v<span id="versionDisplay"></span></span>
      <button id="toggleDebug" class="btn">Hide Debug</button>
    </div>
  </header>

  <!-- BODY -->
  <div class="body-container">
    <aside id="sidebar" class="sidebar"></aside>
    <main class="main-content">
      <div id="cardsViewport" class="cards-container"></div>
    </main>
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
    <div id="debug-content" class="debug-content"></div>
  </div>

  <script src="version.js"></script>
  <script src="js/app.js"></script>
  <script>
    // Show the app version
    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('versionDisplay').textContent = window.appVersion || 'n/a';
    });
  </script>
</body>
</html>
