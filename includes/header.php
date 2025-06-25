<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= getenv('APP_NAME') ?: 'MPS Monitor Dashboard' ?></title>
  <link rel="stylesheet" href="/public/css/styles.css" />
  <!-- Feather Icons -->
  <script src="/public/js/feather-icons.js"></script>
  <!-- App Bootstrap -->
  <script src="/public/js/app.js"></script>
</head>
<body>
  <header>
    <h1><?= getenv('APP_NAME') ?: 'Dashboard' ?></h1>
    <button id="themeToggle">🌓</button>
  </header>
  <main id="cardGrid">