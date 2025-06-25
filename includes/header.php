<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars(getenv('APP_NAME') ?: 'MPS Monitor Dashboard') ?></title>
  <link rel="stylesheet" href="/public/css/styles.css" />
  <!-- Feather Icons -->
  <script src="/public/js/feather-icons.js"></script>
  <!-- App Bootstrap -->
  <script src="/public/js/app.js"></script>
</head>
<body>
  <header>
    <h1><?= htmlspecialchars(getenv('APP_NAME') ?: 'Dashboard') ?></h1>
    <button id="themeToggle" aria-label="Toggle theme">🌓</button>
  </header>
  <main id="cardGrid">
