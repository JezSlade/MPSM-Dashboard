<?php
// /includes/header.php — Shared header for all views
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= APP_NAME ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
  <header class="app-header">
    <div class="header-left">
      <h1><?= APP_NAME ?></h1>
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
