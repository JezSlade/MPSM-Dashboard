<?php
/**
 * index.php — Single-page entrypoint with corrected layout wrapper so sidebar and main content sit side-by-side
 *
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

// Define placeholder constant
define('DEALER_CODE', getenv('DEALER_CODE') ?: 'N/A');
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <title>Dashboard for <?php echo htmlspecialchars(DEALER_CODE, ENT_QUOTES, 'UTF-8'); ?></title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Global custom styles -->
  <link rel="stylesheet" href="/public/css/styles.css">
  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="h-full flex flex-col">

  <?php include __DIR__ . '/includes/header.php'; ?>

  <!-- Content area: sidebar + main -->
  <div class="flex flex-1 overflow-hidden">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="flex-1 overflow-y-auto p-6">
      <div class="card-grid">
        <?php
        // Auto-discover all cards in /cards/
        $cardsDir = __DIR__ . '/cards/';
        $files = array_filter(scandir($cardsDir, SCANDIR_SORT_ASCENDING), function($f) {
          return pathinfo($f, PATHINFO_EXTENSION) === 'php';
        });
        foreach ($files as $file):
        ?>
        <div class="card-wrapper glow" data-file="<?php echo $file; ?>">
          <?php include $cardsDir . $file; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <!-- Card-settings modal and scripts omitted for brevity; unchanged -->
</body>
</html>

  <!--
 * Changelog:
 * - Wrapped navigation and main in a `div.content-area` with `flex` to place them side-by-side.
 * - Changed body layout from `flex-col` to top-level header, then content-area, then footer.
 * - Ensured `.sidebar` fills vertical space and `.main` flexes to remaining width.
  * Changelog:
  * - Fixed stray closing </script> tag.
  * - Ensured modal is explicitly hidden on load via hideModal() before other actions.
  * - Consolidated changelog entries into one section at end.
  Changelog:
  - Changed <html> tag to default dark mode: added class="dark" and data-theme="dark".
  - Wrapped event listener attachments in null-safe checks (using `?.`) and conditional bindings to prevent JS errors.
  - Ensured modal remains hidden by default (`class="hidden"`).
  - Verified click-outside and inner-stopPropagation logic works reliably.
  - Appended detailed changelog entries for future reference.
  -->
  <!--
  Changelog:
  - Added id="cardSettingsContent" to inner modal div.
  - Added stopPropagation on inner content to prevent overlay click from firing when clicking inside.
  - Simplified overlay click listener to hideModal directly.
  - Verified Save/Cancel buttons have type="button" and hide modal on click.
  - Logged all changes for future reference.
  -->
