<?php declare(strict_types=1);
// /includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Expect $title and $customerName to be set before including this file
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= htmlspecialchars($title ?? 'Dashboard') ?></title>
  <link rel="stylesheet" href="/public/css/styles.css" />
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

  <!-- Preferences Modal (dark/light, etc.) -->
  <?php include __DIR__ . '/../components/preferences-modal.php'; ?>

  <header class="dashboard-header flex items-center justify-between px-6 py-3 bg-gray-800 bg-opacity-50 backdrop-blur-sm">
    <div class="flex items-center space-x-4">
      <!-- Optional logo or icon -->
      <i data-feather="grid" class="h-6 w-6 text-purple-400"></i>
      <h1 class="text-xl font-semibold"><?= htmlspecialchars($customerName ?? 'All Customers') ?></h1>
    </div>
    <div class="flex items-center space-x-3">
      <!-- Customer selector -->
      <?php include __DIR__ . '/navigation.php'; ?>

      <!-- Preferences button -->
      <button id="preferences-toggle"
              onclick="togglePreferencesModal(true)"
              class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-400"
              title="Preferences">
        <i data-feather="settings" class="h-6 w-6 text-purple-400"></i>
      </button>
    </div>
  </header>
