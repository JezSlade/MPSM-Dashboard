<?php
/**
 * index.php — Single-page entrypoint for the Neumorphic Dashboard UI
 * This file sets up the HTML skeleton and includes all main components.
 */

declare(strict_types=1);
// Enable full error reporting for development
error_reporting(E_ALL);
ini_set('display_errors','1');

// Define a placeholder for DEALER_CODE (to be replaced by backend or env)
define('DEALER_CODE', getenv('DEALER_CODE') ?: 'N/A');

?>
<!DOCTYPE html>
<html lang="en" class="h-full" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>Dashboard for <?php echo htmlspecialchars(DEALER_CODE, ENT_QUOTES, 'UTF-8'); ?></title>

  <!-- Tailwind CSS for utility classes -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Global custom styles for Neumorphism -->
  <link rel="stylesheet" href="/public/css/styles.css">
  <!-- Feather Icons for placeholder icons -->
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="h-full flex flex-col transition-colors duration-300">

  <?php
  // Include the header partial
  include __DIR__ . '/includes/header.php';
  // Include the navigation (sidebar) partial
  include __DIR__ . '/includes/navigation.php';
  ?>

  <main class="flex-1 overflow-y-auto p-6">
    <div class="card-grid">
      <?php
      // Include a sample card as proof of concept
      include __DIR__ . '/cards/SampleCard.php';
      ?>
    </div>
  </main>

  <?php
  // Include the footer partial
  include __DIR__ . '/includes/footer.php';
  ?>

  <!-- Initialize Feather icons and UI behaviors -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Replace all <i data-feather="..."> with SVG icons
      feather.replace();

      // Theme toggle button behavior
      document.getElementById('theme-toggle').addEventListener('click', () => {
        const html = document.documentElement;
        // Toggle between light and dark themes
        html.setAttribute('data-theme',
          html.getAttribute('data-theme') === 'light' ? 'dark' : 'light'
        );
      });

      // Hard refresh button behavior
      document.getElementById('refresh-all').addEventListener('click', () => {
        // true => bypass cache
        location.reload(true);
      });

      // Clear session button behavior
      document.getElementById('clear-session').addEventListener('click', () => {
        // Delete all cookies
        document.cookie.split(';').forEach(c => {
          document.cookie = c.split('=')[0].trim() + '=;expires=Thu, 01 Jan 1970 GMT;path=/';
        });
        // Reload page
        location.reload();
      });

      // View debug log button behavior
      document.getElementById('view-error-log').addEventListener('click', () => {
        // Open the debug log in a new tab
        window.open('/logs/debug.log', '_blank');
      });
    });
  </script>
</body>
</html>
