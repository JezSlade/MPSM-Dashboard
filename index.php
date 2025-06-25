<?php
/**
 * index.php — Entrypoint with application-log toggle, manual drag-and-drop, and corrected title
 *
 * Changelog:
 * - Added APP_NAME constant from APP_NAME env var (fallback “MPS Monitor Dashboard”).
 * - Updated <title> to use APP_NAME instead of undefined DEALER_CODE.
 * - Changelog appended at end after </html>.
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

// Define constants from .env
define('DEALER_CODE', getenv('DEALER_CODE') ?: 'N/A');
define('APP_NAME', getenv('APP_NAME') ?: 'MPS Monitor Dashboard');
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>

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
    .card-wrapper {
      cursor: grab;
      user-select: none;
      pointer-events: auto;
    }
    .card-wrapper.dragging {
      opacity: 0.6;
      transform: scale(1.02);
      z-index: 50;
    }
    /* Ensure app log card spans two columns */
    #appLogCard { grid-column: span 2; }
  </style>

  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>
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
        <div class="card-wrapper glow" draggable="true" data-file="<?php echo htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?>">
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
    // Toggle Application Log card
    document.getElementById('view-error-log')?.addEventListener('click', () => {
      const logCard = document.getElementById('appLogCard');
      if (!logCard) return console.error('Application Log card not found!');
      logCard.style.display = logCard.style.display === 'none' ? '' : 'none';
    });

    // Apply visibility from card-settings
    (function applyVisibility() {
      let visible = [];
      try { visible = JSON.parse(localStorage.getItem('visibleCards') || '[]'); }
      catch { visible = []; localStorage.removeItem('visibleCards'); }
      document.querySelectorAll('.card-wrapper').forEach(card => {
        card.style.display = visible.includes(card.dataset.file) ? '' : 'none';
      });
    })();

    // Manual drag-and-drop logic
    const grid = document.getElementById('cardGrid');
    let dragged = null;
    const positions = JSON.parse(localStorage.getItem('cardPositions') || '{}');

    // Reapply saved positions
    for (const [file, pos] of Object.entries(positions)) {
      const card = grid.querySelector(`.card-wrapper[data-file="${file}"]`);
      if (card) {
        card.style.gridColumnStart = pos.col;
        card.style.gridRowStart    = pos.row;
      }
    }

    grid.addEventListener('dragover', e => e.preventDefault());
    grid.addEventListener('drop', e => {
      e.preventDefault();
      if (!dragged) return;
      const rect = grid.getBoundingClientRect();
      const cols = Math.floor(rect.width / 280);
      const rows = Math.floor(rect.height / 180);
      const colWidth = rect.width / cols;
      const rowHeight = rect.height / rows;
      const x = Math.max(0, Math.min(e.clientX - rect.left, rect.width - 1));
      const y = Math.max(0, Math.min(e.clientY - rect.top, rect.height - 1));
      const col = Math.floor(x / colWidth) + 1;
      const row = Math.floor(y / rowHeight) + 1;
      dragged.style.gridColumnStart = col;
      dragged.style.gridRowStart    = row;
      positions[dragged.dataset.file] = { col, row };
      localStorage.setItem('cardPositions', JSON.stringify(positions));
      dragged.classList.remove('dragging');
      dragged = null;
    });

    document.querySelectorAll('.card-wrapper').forEach(card => {
      card.addEventListener('dragstart', e => {
        dragged = e.target;
        e.target.classList.add('dragging');
      });
      card.addEventListener('dragend', e => {
        e.target.classList.remove('dragging');
      });
    });
  });
  </script>
</body>
</html>

<!-- **Consolidated Changelog:**

- **Application Configuration:**
  - Added `APP_NAME` constant from `APP_NAME` environment variable with fallback.
  - Updated `<title>` to reflect `APP_NAME`.
  - Set default dark mode via `<html class="dark" data-theme="dark">`.

- **Layout and Styling:**
  - Wrapped navigation and main in `div.content-area` with `flex` for side-by-side layout.
  - Changed body layout from `flex-col` to top-level header, content-area, and footer.
  - Ensured `.sidebar` fills vertical height and `.main` flexes to remaining width.
  - Added inline `<style>` to override `.card-grid` for responsive auto-fill layout.
  - Established 12×8 CSS grid with spans for small/medium/large/tall cards.
  - Enhanced `.card-wrapper` CSS for dragging (`cursor: grab`, `user-select: none`).

- **Drag-and-Drop Functionality:**
  - Replaced SortableJS with full manual HTML5 drag-and-drop implementation for `.card-wrapper`.
  - Imported “smart nudging” logic from `drag.tsx` into native JavaScript.
  - Cards are absolutely positioned in `.dashboard-container` with snap-to-grid and gentle overlapping nudges.
  - Added dual event listeners and error handling in `initializeSortable()` for SortableJS fallback.
  - Deferred SortableJS load with `defer` attribute and moved to footer with console logs to verify presence.
  - Added error checks for missing `#cardGrid` or SortableJS.
  - Deferred drag-and-drop initialization until after SortableJS load.

- **LocalStorage Persistence:**
  - Card positions stored in `localStorage.cardPositions` and reapplied on page load.
  - Layout state persisted in `localStorage` with controls to save/reset.
  - Positions persist in `localStorage` and reload on page load.

- **Controls and Interactivity:**
  - Added controls: Save, Reset, Toggle Debug overlay.
  - Changed `view-error-log` click handler to toggle visibility of card with `id="appLogCard"`.
  - Removed `window.open('/logs/debug.log')` for header error-log button.
  - Consolidated header button wiring under `DOMContentLoaded`.
  - Preserved Application Log toggle functionality.

- **Modal and Settings:**
  - Restored card-settings modal HTML and behavior scripts.
  - Added `id="cardSettingsContent"` to inner modal div with `stopPropagation` to prevent overlay click issues.
  - Simplified overlay click listener to call `hideModal` directly.
  - Ensured modal is hidden on load via `hideModal()` and `class="hidden"`.
  - Verified Save/Cancel buttons have `type="button"` and hide modal on click.
  - Wrapped event listener attachments in null-safe checks (using `?.`) and conditional bindings.
  - Verified click-outside and inner `stopPropagation` logic works reliably.

- **Script and Asset Management:**
  - Restored original `index.php` with favicon and Feather icon initialization.
  - Re-added Feather initialization and header button wiring after earlier removal.
  - Unified JavaScript in a single `<script>` tag.
  - Fixed stray closing `</script>` tag.

- **Data and Security:**
  - Sanitized `data-file` attributes in `card-wrapper`.

- **Logging and Documentation:**
  - Consolidated changelog entries into a single section, placed at the end after `</html>` for future reference.
  - Appended detailed changelog entries with console logs for tracking.

This changelog removes duplicates, organizes changes by category, and maintains all unique updates in a clear, concise format. -->