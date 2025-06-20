<?php declare(strict_types=1);
// /includes/header.php

// 0) Ensure debug.log exists (so PHP error_log() calls always work)
$logFile = __DIR__ . '/../logs/debug.log';
if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0664);
}

// 1) Emit the live-debug panel and JS helper immediately
echo <<<'HTML'
<style>
  #debug-console {
    background: rgba(0,0,0,0.85);
    color: #0f0;
    padding: 8px;
    font-family: monospace;
    font-size: 12px;
    height: 200px;
    overflow-y: auto;
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    z-index: 9999;
    border-top: 1px solid #333;
  }
</style>
<div id="debug-console"></div>
<script>
  window.appendDebug = function(msg) {
    var c = document.getElementById('debug-console');
    if (!c) return;
    var e = document.createElement('div');
    e.textContent = msg;
    c.appendChild(e);
    c.scrollTop = c.scrollHeight;
  };
  appendDebug('▶ Debug console initialized');
</script>
HTML;

// 2) Now output the normal document head
require_once __DIR__ . '/config.php';  // if you have global config parsing here
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($config['APP_NAME'] ?? 'MPSM Monitor Dashboard', ENT_QUOTES) ?></title>
  <link rel="stylesheet" href="/public/css/styles.css">
  <!-- any other <meta> / <link> tags -->
</head>
<body data-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light', ENT_QUOTES) ?>">

<!-- Immediately log in the JS console that the header is done -->
<script>appendDebug('▶ Header loaded');</script>
