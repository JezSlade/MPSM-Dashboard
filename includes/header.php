<?php
// /includes/header.php — Shared header for all views
declare(strict_types=1);

// Always enable error display for debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Ensure APP_NAME is defined
if (!defined('APP_NAME')) {
    define('APP_NAME', getenv('APP_NAME') ?: 'MPS Monitor Dashboard');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars(APP_NAME) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
  <header class="app-header">
    <div class="header-left">
      <h1><?= htmlspecialchars(APP_NAME) ?></h1>
    </div>
    <div class="header-right">
      <!-- Feather icon buttons -->
      <button class="icon-button" data-action="open-settings" title="Settings">
        <i data-feather="settings"></i>
      </button>
      <button class="icon-button" data-action="refresh-page" title="Refresh">
        <i data-feather="refresh-cw"></i>
      </button>
    </div>
  </header>
