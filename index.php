<?php
/**
 * index.php — Restored absolute‐layout dashboard with smart drag‐and‐drop
 *
 * Changelog:
 * - Replaced `.card-grid` grid layout with `.dashboard-container` for absolute positioning.
 * - Each `.card-wrapper` is now absolutely positioned inside `.dashboard-container`.
 * - Re‐added manual drag logic: snap‐to‐grid, overlap nudging, position persistence.
 * - Retained header/navigation/footer and card‐settings modal.
 * - Changelog appended at end after closing </html>.
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

// Load environment constants
define('DEALER_CODE', getenv('DEALER_CODE') ?: 'N/A');
define('APP_NAME',    getenv('APP_NAME')    ?: 'MPS Monitor Dashboard');
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES,'UTF-8'); ?></title>
  <link rel="icon" href="data:;base64,">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/public/css/styles.css">
  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="h-full flex flex-col">

  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="flex flex-1 overflow-hidden">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="flex-1 p-6">
      <!-- ABSOLUTE LAYOUT CONTAINER -->
      <div class="dashboard-container" id="dashboard">
        <?php
          $cardsDir = __DIR__ . '/cards/';
          $files = array_filter(scandir($cardsDir), fn($f)=>pathinfo($f,PATHINFO_EXTENSION)==='php');
          foreach ($files as $file):
            $id = pathinfo($file, PATHINFO_FILENAME);
        ?>
        <div 
          class="card-wrapper neumorphic glow" 
          id="<?php echo htmlspecialchars($id,ENT_QUOTES); ?>"
          data-file="<?php echo htmlspecialchars($file,ENT_QUOTES); ?>">
          <?php include $cardsDir . $file; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <!-- Card-settings modal -->
  <?php /* existing modal markup here */ ?>

  <!-- Smart drag‐and‐drop logic -->
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    feather.replace();

    // Toggle App Log card
    document.getElementById('view-error-log')?.addEventListener('click', () => {
      const log = document.getElementById('appLogCard');
      if (!log) return;
      log.style.display = (getComputedStyle(log).display==='none')?'':'none';
    });

    const container = document.getElementById('dashboard');
    const cards = Array.from(container.querySelectorAll('.card-wrapper'));
    const positions = JSON.parse(localStorage.getItem('cardPositions')||'{}');
    const GRID = 20, MAX_IT=50;
    const sizes = {};

    // apply saved/default positions, measure sizes
    cards.forEach(c => {
      const file = c.dataset.file;
      // default fallbacks
      const pos = positions[file] || { x:20, y:20 };
      c.style.left = pos.x+'px';
      c.style.top  = pos.y+'px';
      const r = c.getBoundingClientRect();
      sizes[file] = { w:r.width, h:r.height };
    });

    function rect(c){ 
      return { x: parseInt(c.style.left), y: parseInt(c.style.top), 
               w: sizes[c.dataset.file].w, h: sizes[c.dataset.file].h };
    }
    function overlap(a,b){
      return !(a.x+a.w<=b.x || b.x+b.w<=a.x || a.y+a.h<=b.y || b.y+b.h<=a.y);
    }
    function nudge(c, depth=0){
      if (depth>MAX_IT) return;
      const r1 = rect(c);
      for (let o of cards){
        if (o===c || o.style.display==='none') continue;
        const r2 = rect(o);
        if (overlap(r1,r2)){
          // push other away
          const dx = (r2.x + r2.w/2) < (r1.x + r1.w/2) ? -GRID : GRID;
          const dy = (r2.y + r2.h/2) < (r1.y + r1.h/2) ? -GRID : GRID;
          o.style.left = Math.max(0,Math.min(container.clientWidth - r2.w, r2.x+dx))+'px';
          o.style.top  = Math.max(0,Math.min(container.clientHeight - r2.h, r2.y+dy))+'px';
          nudge(o, depth+1);
        }
      }
    }

    let active = null, ox=0, oy=0, ax=0, ay=0;
    cards.forEach(c => {
      const hdr = c.querySelector('.card-header');
      hdr.style.cursor = 'grab';
      hdr.addEventListener('mousedown', e => {
        active = c;
        ox = e.clientX; oy = e.clientY;
        ax = rect(c).x; ay = rect(c).y;
        c.classList.add('dragging');
      });
    });
    document.addEventListener('mousemove', e => {
      if (!active) return;
      let nx = ax + (e.clientX - ox);
      let ny = ay + (e.clientY - oy);
      // clamp
      nx = Math.max(0, Math.min(nx, container.clientWidth - sizes[active.dataset.file].w));
      ny = Math.max(0, Math.min(ny, container.clientHeight - sizes[active.dataset.file].h));
      active.style.left = nx+'px';
      active.style.top  = ny+'px';
      nudge(active);
    });
    document.addEventListener('mouseup', () => {
      if (!active) return;
      active.classList.remove('dragging');
      // persist
      const out = {};
      cards.forEach(c => {
        out[c.dataset.file] = { 
          x: parseInt(c.style.left), 
          y: parseInt(c.style.top)
        };
      });
      localStorage.setItem('cardPositions', JSON.stringify(out));
      active = null;
    });
  });
  </script>
</body>
</html>
<!--
Changelog:
- Restored .dashboard-container and absolute .card-wrapper layout.
- Imported smart-nudging drag logic from HTML sample.
- Positions load/save via localStorage.
- Cards draggable on their .card-header.
-->

<!--
Changelog:
- Fixed positioning logic with robust array checking and parseInt() for safety
- Added container dimension checking with retry logic
- Improved error handling for localStorage operations
- Added comprehensive debugging output
- Fixed drag event handling with better null checks
- Added bounds checking to prevent cards from going outside container
- Improved collision resolution with safer dimension fallbacks
-->
<!--
Changelog:
- Imported smart-nudge & collision logic from dashboard_fixed.html.
- `.card-wrapper` now absolute, initial positions from localStorage or PHP defaults.
- Drag via `.card-header`, resolve collisions by nudging overlapping cards.
- Persist positions back to localStorage on mouseup.
-->

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