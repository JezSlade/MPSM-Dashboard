<?php declare(strict_types=1);
// /includes/header.php

// 0) Load global config
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($config['APP_NAME'] ?? 'MPSM Monitor Dashboard', ENT_QUOTES) ?></title>
  <link rel="stylesheet" href="/public/css/styles.css">
  <!-- add any other <meta> or <link> tags here -->
</head>
<body data-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light', ENT_QUOTES) ?>">

<header class="site-header" style="padding:1em; background:rgba(255,255,255,0.1);">
  <h1 style="margin:0; font-size:1.5em;">
    <?= htmlspecialchars($config['APP_NAME'] ?? 'MPSM Monitor Dashboard', ENT_QUOTES) ?>
  </h1>
</header>
