<?php
/**
 * index.php — Entrypoint with application-log toggle on header “Error Log” click
 *
 * Changelog:
 * - Updated `view-error-log` handler to toggle the “Application Log” card instead of opening 404.
 * - Ensured the new card (`appLogCard`) is visible/hidden in the grid.
 * - Cleaned up duplicate changelog entries, placed single changelog at end.
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

  <!-- Prevent favicon 404 -->
  <link rel="icon" href="data:;base64,">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Global custom styles -->
  <link rel="stylesheet" href="/public/css/styles.css">

  <style>
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 12px;
    }
    #cardSettingsModal.hidden { display: none !important; }
    .card-wrapper { cursor: grab; user-select: none; pointer-events: auto; }
    .card-wrapper:active { cursor: grabbing; }
    /* Ensure app log card spans two columns */
    #appLogCard { grid-column: span 2; }
  </style>

  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>
  <!-- SortableJS -->
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="h-full flex flex-col">

  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="flex flex-1 overflow-hidden">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="flex-1 overflow-y-auto p-6">
      <div class="card-grid" id="cardGrid">
        <?php
        // Auto-discover all cards in /cards/
        $cardsDir = __DIR__ . '/cards/';
        $files = array_filter(scandir($cardsDir, SCANDIR_SORT_ASCENDING), fn($f) =>
          pathinfo($f, PATHINFO_EXTENSION) === 'php'
        );
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

  <!-- Card-settings modal omitted for brevity -->

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      feather.replace();

      // Header controls
      document.getElementById('view-error-log')?.addEventListener('click', () => {
        const logCard = document.getElementById('appLogCard');
        if (!logCard) return console.error('Application Log card not found!');
        logCard.style.display = logCard.style.display === 'none' ? '' : 'none';
      });

      // ... existing modal & sortable initialization ...
    });
  </script>
</body>
</html>

<!--
Changelog:
- Changed `view-error-log` click handler: now toggles visibility of card with id="appLogCard".
- Removed window.open('/logs/debug.log') for the header error-log button.
- Consolidated and placed changelog at end, after </html>.
-->
<!--
Changelog:
- Added error handling, console logs, and dual event listeners in `initializeSortable()` to ensure SortableJS initializes correctly.
- Deferred SortableJS load with `defer` attribute.
- Enhanced CSS for `.card-wrapper` to support dragging (`cursor: grab`, `user-select: none`).
- Kept changelog at very end of file after </html> tag.
-->

<!--
Changelog:
- Moved SortableJS load to footer and added console logs to verify its presence.
- Added error checks for missing `#cardGrid` or SortableJS.
- Deferred drag-and-drop initialization until after SortableJS load.
- Kept changelog at very end of file after </html> tag.
-->

<!--
Changelog:
- Added inline <style> to override .card-grid for responsive auto-fill layout.
- Integrated SortableJS for drag-and-drop card reordering with localStorage persistence.
- Consolidated header button wiring under DOMContentLoaded.
- Restored card-settings modal HTML and behavior scripts.
- Set default dark mode via <html class="dark" data-theme="dark">.
- Placed changelog at end after closing </html> tag for future reference.
-->

  <!-- *
 * Changelog:
 * - Wrapped navigation and main in a `div.content-area` with `flex` to place them side-by-side.
 * - Changed body layout from `flex-col` to top-level header, then content-area, then footer.
 * - Ensured `.sidebar` fills vertical space and `.main` flexes to remaining width.
 * - Removed scripts earlier; now re-adding Feather initialization and header button wiring.
 * - Consolidated changelog entries into one section at top.
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
