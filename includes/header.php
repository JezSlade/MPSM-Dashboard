<?php
// --- DEBUG BLOCK (Always Keep at Top) ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ----------------------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= getenv('APP_NAME') ?: 'MPS Monitor Dashboard' ?></title>
  <link rel="stylesheet" href="/public/css/styles.css" />

  <!-- Feather Icons library -->
  <script src="https://unpkg.com/feather-icons"></script>
  <!-- Centralized initialization -->
  <script src="/public/js/feather-init.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof feather !== 'undefined') {
        feather.replace();
      }

      setTimeout(() => {
        const settingsBtn = document.getElementById('settings-btn');
        if (settingsBtn) {
          settingsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const modal = document.getElementById('settingsModal');
            if (modal) {
              modal.classList.add('open');
            }
          });
        }
      }, 100);
    });
  </script>
</head>

<body class="theme-dark">
  <header class="app-header">
    <div class="left">
      <h1 class="logo"><?= getenv('APP_NAME') ?: 'MPS Monitor Dashboard' ?></h1>
    </div>
    <div class="right">
      <button id="settings-btn" type="button" class="icon-button" title="Settings">
        <i data-feather="settings"></i>
      </button>
    </div>
  </header>
