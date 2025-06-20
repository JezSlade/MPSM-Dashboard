<?php declare(strict_types=1);
// /includes/header.php

require_once __DIR__ . '/config.php';          // parses .env → $config
require_once __DIR__ . '/debug.php';           // adds silent appendDebug()

// Global PHP error reporting
ini_set('display_errors', '0');
ini_set('log_errors',     '1');
ini_set('error_log',      __DIR__ . '/../logs/debug.log');
error_reporting(E_ALL);

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

<header class="site-header" style="padding:1em; background:rgba(255,255,255,0.1);">
  <h1 style="margin:0; font-size:1.5em;">
    <?= htmlspecialchars($config['APP_NAME'] ?? 'MPS Monitor Dashboard', ENT_QUOTES) ?>
  </h1>
</header>
