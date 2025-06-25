<?php
/**
 * index.php — Enhanced entrypoint with responsive grid, drag-and-drop reordering, and deferred SortableJS loading
 *
 * Changelog:
 * - Moved SortableJS `<script>` load to just before initialization in footer.
 * - Added console logs and error checks to verify SortableJS and `#cardGrid` existence.
 * - Deferred initialization until after SortableJS is loaded.
 * - Consolidated all scripts in one block for clarity.
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

  <!-- Inline override to ensure grid spreads out -->
  <style>
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 12px;
    }
    /* ensure modal.hidden truly hides the modal */
    #cardSettingsModal.hidden { display: none !important; }
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

  <!-- Card-settings modal -->
  <div id="cardSettingsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
    <div id="cardSettingsContent" class="bg-light dark:bg-dark neumorphic p-4 rounded w-11/12 md:w-1/3 max-h-[80vh] overflow-auto">
      <h2 class="text-lg font-semibold mb-2">Select Cards to Display</h2>
      <form id="cardSettingsForm" class="space-y-2">
        <?php foreach ($files as $file):
            $id = pathinfo($file, PATHINFO_FILENAME);
        ?>
        <label class="flex items-center space-x-2">
          <input type="checkbox" name="cards" value="<?php echo $file; ?>" checked>
          <span><?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?></span>
        </label>
        <?php endforeach; ?>
      </form>
      <div class="flex justify-end space-x-2 mt-4">
        <button id="cardSettingsSave" type="button" class="neu-btn">Save</button>
        <button id="cardSettingsCancel" type="button" class="neu-btn">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Core Scripts: Feather initialization, header buttons, modal behavior -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const html = document.documentElement;

      // Feather icons
      feather.replace();

      // Header controls
      document.getElementById('theme-toggle')?.addEventListener('click', () => {
        const current = html.getAttribute('data-theme');
        html.setAttribute('data-theme', current === 'light' ? 'dark' : 'light');
        html.classList.toggle('dark');
      });
      document.getElementById('refresh-all')?.addEventListener('click', () => location.reload(true));
      document.getElementById('clear-session')?.addEventListener('click', () => {
        document.cookie.split(';').forEach(c => {
          document.cookie = c.split('=')[0].trim() + '=;expires=Thu,01 Jan 1970 GMT;path=/';
        });
        location.reload();
      });
      document.getElementById('view-error-log')?.addEventListener('click', () => {
        window.open('/logs/debug.log','_blank');
      });
      document.getElementById('card-settings')?.addEventListener('click', () => {
        const m = document.getElementById('cardSettingsModal');
        m.style.display = 'flex';
        m.classList.remove('hidden');
      });

      // Card-settings modal behavior
      const modal = document.getElementById('cardSettingsModal');
      const content = document.getElementById('cardSettingsContent');
      modal.addEventListener('click', () => {
        modal.style.display = 'none';
        modal.classList.add('hidden');
      });
      content.addEventListener('click', e => e.stopPropagation());
      document.getElementById('cardSettingsCancel')?.addEventListener('click', e => {
        e.preventDefault();
        modal.style.display = 'none';
        modal.classList.add('hidden');
      });
      document.getElementById('cardSettingsSave')?.addEventListener('click', e => {
        e.preventDefault();
        const checked = Array.from(document.querySelectorAll('#cardSettingsForm input[name="cards"]:checked'))
                             .map(i => i.value);
        try { localStorage.setItem('visibleCards', JSON.stringify(checked)); } catch {}
        applyCardVisibility();
        modal.style.display = 'none';
        modal.classList.add('hidden');
      });

      function applyCardVisibility() {
        let visible;
        try {
          visible = JSON.parse(localStorage.getItem('visibleCards') || '[]');
        } catch {
          visible = [];
          localStorage.removeItem('visibleCards');
        }
        document.querySelectorAll('.card-wrapper').forEach(card => {
          card.style.display = visible.includes(card.dataset.file) ? '' : 'none';
        });
      }
      applyCardVisibility();
    });
  </script>

  <!-- Load SortableJS and initialize drag-and-drop -->
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      console.log('SortableJS:', typeof Sortable);
      const grid = document.getElementById('cardGrid');
      if (!grid) {
        console.error('cardGrid not found!');
        return;
      }
      if (typeof Sortable !== 'function') {
        console.error('Sortable is not loaded!');
        return;
      }
      // Restore saved order
      const saved = JSON.parse(localStorage.getItem('cardOrder') || '[]');
      if (saved.length) {
        saved.forEach(file => {
          const el = grid.querySelector(`[data-file="${file}"]`);
          if (el) grid.appendChild(el);
        });
      }
      new Sortable(grid, {
        animation: 150,
        ghostClass: 'opacity-50',
        onEnd: () => {
          const order = Array.from(grid.children).map(c => c.dataset.file);
          localStorage.setItem('cardOrder', JSON.stringify(order));
          console.log('Saved order:', order);
        }
      });
    });
  </script>
</body>
</html>

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
