<?php declare(strict_types=1);
// /views/dashboard.php

error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// (customer context code omitted for brevity)

$cardsDir = __DIR__ . '/../cards/';
$all = array_filter(scandir($cardsDir), fn($f) =>
  pathinfo($f, PATHINFO_EXTENSION)==='php' && str_starts_with($f,'card_')
);
$visible = isset($_COOKIE['visible_cards'])
           ? array_intersect(explode(',', $_COOKIE['visible_cards']), $all)
           : $all;
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
  <meta charset="UTF-8">
  <title>Dashboard – <?= htmlspecialchars($customerName) ?></title>
  <link rel="stylesheet" href="/public/css/styles.css" />
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

  <?php include __DIR__ . '/../components/preferences-modal.php'; ?>

  <header class="dashboard-header flex items-center justify-between px-6 py-3 bg-gray-800 bg-opacity-50 backdrop-blur-sm">
    <h1 class="text-xl font-semibold"><?= htmlspecialchars($customerName) ?></h1>
    <!-- PREFERENCES GEAR ICON (no onclick attr needed any more) -->
    <button id="preferences-toggle"
            class="p-3 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-400"
            title="Preferences">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <!-- gear path omitted -->
      </svg>
    </button>
  </header>

  <main class="flex-1 overflow-auto p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($visible as $card): include $cardsDir . $card; endforeach; ?>
    </div>
  </main>

  <!-- No additional <script> needed here – handler lives in header.php -->
</body>
</html>
