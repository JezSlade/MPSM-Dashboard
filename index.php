<?php
declare(strict_types=1);
// /includes/header.php
// This file now only emits the <header> element; the <head> and <body> wrappers live in index.php
?>
<header class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 shadow-md">
  <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
    Dashboard for <?php echo htmlspecialchars(DEALER_CODE, ENT_QUOTES, 'UTF-8'); ?>
  </h1>
  <div class="flex items-center space-x-3">
    <!-- Theme toggle -->
    <button id="theme-toggle" aria-label="Toggle theme" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
      <i data-feather="sun"></i>
    </button>

    <!-- Hard refresh -->
    <button id="refresh-all" aria-label="Hard refresh" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
      <i data-feather="refresh-cw"></i>
    </button>

    <!-- Clear session -->
    <button id="clear-session" aria-label="Clear session" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
      <i data-feather="trash-2"></i>
    </button>

    <!-- View error log -->
    <button id="view-error-log" aria-label="View error log" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
      <i data-feather="file-text"></i>
    </button>
  </div>
</header>
