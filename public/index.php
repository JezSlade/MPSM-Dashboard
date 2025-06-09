<?php
// public/index.php
// -----------------------------------------------------
// Entry point for MPSM Dashboard.
// Enables full PHP error reporting and loads the UI.
// -----------------------------------------------------

// 1) Display every PHP warning/notice/error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Load config & debug utilities
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/DebugPanel.php';

// 3) Read AllEndpoints.json
$epFile = __DIR__ . '/../AllEndpoints.json';
if (!file_exists($epFile)) {
    DebugPanel::log("AllEndpoints.json NOT found at $epFile");
    $allEndpoints = [];
} else {
    $json = file_get_contents($epFile);
    $allEndpoints = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        DebugPanel::log("JSON parse error: " . json_last_error_msg());
        $allEndpoints = [];
    } else {
        DebugPanel::log("Loaded AllEndpoints.json ({count} endpoints)", ['count'=>count($allEndpoints)]);
    }
}

// 4) Categorize into roles (hard‐coded mapping)
$mapping = [
  'Developer' => ['/ApiClient/List'],
  'Admin'     => ['/Analytics/GetReportResult','/ApiClient/List','/Account/GetAccounts','/Account/UpdateProfile'],
  'Dealer'    => ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
  'Service'   => ['/AlertLimit2/GetAllLimits','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
  'Sales'     => ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
  'Accounting'=> ['/Analytics/GetReportResult','/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'],
  'Guest'     => ['/Account/GetProfile','/Account/Logout','/Account/UpdateProfile'],
];
$categorized = [];
foreach ($mapping as $role => $paths) {
    $categorized[$role] = [];
    foreach ($paths as $p) {
        // find matching endpoint
        $found = false;
        foreach ($allEndpoints as $ep) {
            if (isset($ep['path']) && $ep['path'] === $p) {
                $categorized[$role][] = $ep;
                $found = true;
                break;
            }
        }
        if (!$found) {
            DebugPanel::log("Endpoint not found for role $role → $p");
            // still include placeholder
            $categorized[$role][] = ['method'=>'?','path'=>$p,'description'=>'(missing)'];
        }
    }
}

// 5) Render the HTML + inject JS config
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>MPSM Dashboard</title>
  <!-- Glassmorphic styles -->
  <link rel="stylesheet" href="css/styles.css">
  <script>
    // pass PHP data into JS
    window.roleEndpoints = <?php echo json_encode($categorized, JSON_HEX_TAG); ?>;
    window.debugMode    = <?php echo DEBUG_MODE ? 'true' : 'false'; ?>;
  </script>
</head>
<body>
  <!-- HEADER -->
  <header class="glass-panel">
    <div class="status-panel">
      DB:<span id="dbStatus" class="status-dot"></span>
      API:<span id="apiStatus" class="status-dot"></span>
    </div>
    <div class="role-panel">
      <label for="roleSelect">Current Role:</label>
      <select id="roleSelect"></select>
    </div>
  </header>

  <!-- CARD VIEWPORT -->
  <main id="cardsViewport" class="cards-container"></main>

  <!-- DRILLDOWN MODAL -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <span id="modalClose" class="modal-close">&times;</span>
      <div id="modalBody"></div>
    </div>
  </div>

  <!-- DEBUG PANEL -->
  <?php DebugPanel::output(); ?>

  <!-- Application JS -->
  <script src="js/app.js"></script>
</body>
</html>
