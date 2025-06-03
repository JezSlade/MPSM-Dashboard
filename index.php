<?php
require_once __DIR__ . '/src/EnvLoader.php';
require_once __DIR__ . '/src/DebugLogger.php';
require_once __DIR__ . '/src/Auth.php';

EnvLoader::load(__DIR__ . '/.env');
Auth::init();
Auth::checkLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MPSM Dashboard</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <!-- Vue 3 CDN (Production Build) -->
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
</head>
<body>
  <header class="header">
    <h1>MPSM Dashboard</h1>
    <customer-select @selected="onCustomerSelected"></customer-select>
    <button onclick="location.href='/logout.php'" style="margin-left:auto; background:var(--neon-yellow); border:none; padding:0.5rem; cursor:pointer;">
      Logout
    </button>
  </header>

  <main id="app">
    <device-list v-if="selectedCustomer" :customer="selectedCustomer" @view-device="onDeviceSelected"></device-list>
    <device-drill v-if="selectedDevice" :device-id="selectedDevice"></device-drill>
    <blank-module></blank-module>
  </main>

  <debug-panel v-if="debugOn"></debug-panel>

  <script type="module" src="/assets/js/app.js"></script>
</body>
</html>
