<?php declare(strict_types=1);
// /includes/header.php — top-of-page include

require_once __DIR__ . '/config.php';   // parses .env → $config
require_once __DIR__ . '/debug.php';    // universal PHP logging + appendDebug()

appendDebug('Header loaded');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($config['APP_NAME'] ?? 'MPS Monitor Dashboard', ENT_QUOTES) ?></title>
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body data-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light', ENT_QUOTES) ?>">

<header class="site-header" style="padding:1em;background:rgba(255,255,255,0.1);">
  <h1 style="margin:0;font-size:1.5em;">
    <?= htmlspecialchars($config['APP_NAME'] ?? 'MPS Monitor Dashboard', ENT_QUOTES) ?>
  </h1>
</header>

<!-- Minimal client-side error tracker (tiny inline script, no external JS files) -->
<script>
(function(){
  function logClientError(type, msg, url, line){
    fetch('/api/log_client_error.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ msg: msg, url: url, line: line })
    });
  }
  window.onerror = function (msg, url, line){ logClientError('onerror', String(msg), String(url), line||0); };
  window.addEventListener('unhandledrejection', function (e){
    logClientError('unhandledrejection', String(e.reason), location.href, 0);
  });
})();
</script>
