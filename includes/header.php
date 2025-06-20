<?php declare(strict_types=1);
// /includes/header.php

// 0) Debug-console CSS + container + JS logger (loads first)
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
    if(!c) return;
    var e = document.createElement('div');
    e.textContent = msg;
    c.appendChild(e);
    c.scrollTop = c.scrollHeight;
  };
  appendDebug('▶ Debug console initialized');
</script>
HTML;

// 1) Your existing doctype/head
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($config['APP_NAME'] ?? 'MPSM') ?></title>
  <link rel="stylesheet" href="/public/css/styles.css">
  <!-- etc. -->
</head>
<body data-theme="<?= ($_COOKIE['theme'] ?? 'light') ?>">
<?php appendDebug('▶ Header.php loaded'); ?>
