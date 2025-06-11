<?php
// Enable full error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include configuration constants (including hard-coded DEALER_CODE) and DebugPanel helper
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/DebugPanel.php';

// ─── Load all API endpoints from the Swagger JSON ─────────────────────────────
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

// ─── Define which endpoints each role may access ──────────────────────────────
$roleMappings = [
    'Developer'  => ['/ApiClient/List'],
    'Admin'      => ['/Analytics/GetReportResult','/ApiClient/List','/Account/GetAccounts','/Account/UpdateProfile'],
    'Dealer'     => ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
    'Service'    => ['/AlertLimit2/GetAllLimits','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
    'Sales'      => ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
    'Accounting' => ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
    'Guest'      => ['/Account/GetProfile','/Account/Logout','/Account/UpdateProfile'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>MPSM Dashboard</title>
  <link rel="stylesheet" href="css/styles.css">

  <!-- Expose PHP constants/data to JavaScript -->
  <script>
    window.dealerCode   = '<?php echo DEALER_CODE; ?>';
    window.apiBaseUrl   = '<?php echo API_BASE_URL; ?>';
    window.roleMappings = <?php echo json_encode($roleMappings, JSON_HEX_TAG); ?>;
    window.allEndpoints = <?php echo json_encode($allEndpoints, JSON_HEX_TAG); ?>;
  </script>
</head>
<body>
  <!-- FIXED HEADER: Status indicators — Customer search — Version & Debug toggle -->
  <header class="glass-panel header">
    <div class="status-panel">
      DB: <span id="dbStatus" class="status-dot"></span>
      API: <span id="apiStatus" class="status-dot"></span>
    </div>

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

  <!-- MAIN LAYOUT: Sidebar for roles — Cards view for endpoints -->
  <div class="body-container">
    <aside id="sidebar" class="sidebar"></aside>
    <main class="main-content">
      <div id="cardsViewport" class="cards-container"></div>
    </main>
  </div>

  <!-- MODAL: “Try-It” endpoint tester -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <button id="modalClose" class="btn modal-close">×</button>
      <div id="modalBody"></div>
    </div>
  </div>

  <!-- DEBUG PANEL: Logging console at bottom -->
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
    // Populate version number once the DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('versionDisplay').textContent = window.appVersion || 'n/a';
    });
  </script>
</body>
</html>
