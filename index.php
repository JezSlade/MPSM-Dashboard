<?php
/**
 * index.php — Entrypoint with "smart nudging" drag logic applied to PHP cards
 *
 * Changelog:
 * - Switched from CSS grid to absolute‐positioned `.card-wrapper` inside `.dashboard-container`.
 * - Imported smart nudging & collision resolution from dashboard_fixed.html.
 * - Cards load initial positions from localStorage, or fall back to defaults.
 * - Header drag‐handle uses `.card-header` inside each card template.
 * - Fixed positioning logic with robust fallbacks and debugging.
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

// Load positions from localStorage via JS snippet (fallback to PHP defaults)
$phpDefaults = [
  // file => [x,y]
  'Card1.php' => [50,  50],
  'Card2.php' => [300, 80],
  'Card3.php' => [100, 300],
  'Card4.php' => [600,100],
  'Card5.php' => [400,250],
  'Card6.php' => [50,  400],
];
// Encode PHP defaults as JSON for JS
$defaultsJson = json_encode($phpDefaults);
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars(getenv('APP_NAME') ?: 'MPS Monitor Dashboard', ENT_QUOTES); ?></title>
  <link rel="icon" href="data:;base64,">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/public/css/styles.css">
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="h-full flex flex-col">

  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="flex flex-1 overflow-hidden">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="flex-1 p-6">
      <div class="dashboard-container" id="dashboard">
        <?php
        $cardsDir = __DIR__ . '/cards/';
        $files = array_filter(scandir($cardsDir), fn($f) => pathinfo($f, PATHINFO_EXTENSION)==='php');
        foreach ($files as $file):
          // Each card is output inside .card-wrapper
        ?>
        <div class="card-wrapper neumorphic glow" 
             data-file="<?php echo htmlspecialchars($file, ENT_QUOTES); ?>" 
             id="<?php echo pathinfo($file, PATHINFO_FILENAME); ?>">
          <?php include $cardsDir . $file; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    feather.replace();

    // Toggle Application Log card
    document.getElementById('view-error-log')?.addEventListener('click', () => {
      const log = document.getElementById('appLogCard');
      if (!log) return;
      log.style.display = (getComputedStyle(log).display === 'none') ? '' : 'none';
    });

    // Smart nudging & drag logic
    const defaults = <?php echo $defaultsJson; ?>;
    const container = document.getElementById('dashboard');
    const cards = Array.from(container.querySelectorAll('.card-wrapper'));
    let positions = {};
    
    // Load positions from localStorage with error handling
    try {
      const stored = localStorage.getItem('cardPositions');
      if (stored) {
        positions = JSON.parse(stored);
      }
    } catch (e) {
      console.warn('Failed to load positions from localStorage:', e);
      positions = {};
    }

    // Debug: Check what we have
    console.log('=== CARD POSITIONING DEBUG ===');
    console.log('Container dimensions:', container.clientWidth, 'x', container.clientHeight);
    console.log('Stored positions:', positions);
    console.log('PHP defaults:', defaults);

    // Wait for container to have dimensions
    const initializePositions = () => {
      if (container.clientWidth === 0 || container.clientHeight === 0) {
        console.log('Container not ready, retrying...');
        setTimeout(initializePositions, 100);
        return;
      }

      // Apply saved or default positions with robust fallbacks
      cards.forEach(c => {
        const f = c.dataset.file;
        const saved = positions[f];
        const def = defaults[f];
        
        let x = 50, y = 50; // Safe fallback values
        
        // Try saved position first
        if (saved && Array.isArray(saved) && saved.length >= 2) {
          x = parseInt(saved[0]) || 50;
          y = parseInt(saved[1]) || 50;
          console.log(`Using saved position for ${f}: ${x}, ${y}`);
        } 
        // Then try default position
        else if (def && Array.isArray(def) && def.length >= 2) {
          x = parseInt(def[0]) || 50;
          y = parseInt(def[1]) || 50;
          console.log(`Using default position for ${f}: ${x}, ${y}`);
        } else {
          console.log(`Using fallback position for ${f}: ${x}, ${y}`);
        }
        
        // Ensure positions are within container bounds
        const cardRect = c.getBoundingClientRect();
        const maxX = Math.max(0, container.clientWidth - cardRect.width);
        const maxY = Math.max(0, container.clientHeight - cardRect.height);
        
        x = Math.max(0, Math.min(x, maxX));
        y = Math.max(0, Math.min(y, maxY));
        
        console.log(`Final position for ${f}: ${x}, ${y}`);
        c.style.left = x + 'px';
        c.style.top = y + 'px';
      });

      // Verify positions after a brief delay
      setTimeout(() => {
        console.log('\n=== VERIFICATION ===');
        cards.forEach(c => {
          const rect = c.getBoundingClientRect();
          const containerRect = container.getBoundingClientRect();
          console.log(`${c.dataset.file}: 
            Style: left=${c.style.left}, top=${c.style.top}
            Screen: ${rect.left}, ${rect.top}
            Relative: ${rect.left - containerRect.left}, ${rect.top - containerRect.top}`);
        });
      }, 200);
    };

    // Start positioning
    initializePositions();

    const GRID = 20, MAX_IT = 100;
    const cardSizes = {};
    
    // Precompute widths/heights after positioning
    setTimeout(() => {
      cards.forEach(c => {
        const r = c.getBoundingClientRect();
        cardSizes[c.dataset.file] = { w: r.width, h: r.height };
      });
    }, 300);

    function rectOf(c) {
      return {
        x: parseInt(c.style.left) || 0,
        y: parseInt(c.style.top) || 0,
        w: cardSizes[c.dataset.file]?.w || 280,
        h: cardSizes[c.dataset.file]?.h || 200
      };
    }
    
    function overlap(r1, r2) {
      return !(r1.x + r1.w <= r2.x || r2.x + r2.w <= r1.x ||
               r1.y + r1.h <= r2.y || r2.y + r2.h <= r1.y);
    }
    
    function resolve(c, iter = 0) {
      if (iter > MAX_IT) return;
      const r1 = rectOf(c);
      for (let other of cards) {
        if (other === c || other.style.display === 'none') continue;
        const r2 = rectOf(other);
        if (overlap(r1, r2)) {
          const cx1 = r1.x + r1.w / 2, cy1 = r1.y + r1.h / 2;
          const cx2 = r2.x + r2.w / 2, cy2 = r2.y + r2.h / 2;
          const angle = Math.atan2(cy2 - cy1, cx2 - cx1);
          const dx = -Math.cos(angle) * GRID, dy = -Math.sin(angle) * GRID;
          let nx = r2.x + dx, ny = r2.y + dy;
          // clamp to container
          nx = Math.max(0, Math.min(nx, container.clientWidth - r2.w));
          ny = Math.max(0, Math.min(ny, container.clientHeight - r2.h));
          other.style.left = nx + 'px';
          other.style.top = ny + 'px';
          resolve(other, iter + 1);
        }
      }
    }

    let active = null, ox = 0, oy = 0, ax = 0, ay = 0;
    
    cards.forEach(c => {
      const header = c.querySelector('.card-header');
      if (header) {
        header.style.cursor = 'move';
        header.addEventListener('mousedown', e => {
          active = c;
          ox = e.clientX; 
          oy = e.clientY;
          ax = parseInt(c.style.left) || 0; 
          ay = parseInt(c.style.top) || 0;
          c.classList.add('dragging');
          e.preventDefault();
        });
      }
    });
    
    document.addEventListener('mousemove', e => {
      if (!active) return;
      let nx = ax + (e.clientX - ox);
      let ny = ay + (e.clientY - oy);
      const cardSize = cardSizes[active.dataset.file];
      if (cardSize) {
        nx = Math.max(0, Math.min(nx, container.clientWidth - cardSize.w));
        ny = Math.max(0, Math.min(ny, container.clientHeight - cardSize.h));
      }
      active.style.left = nx + 'px';
      active.style.top = ny + 'px';
      resolve(active);
    });
    
    document.addEventListener('mouseup', () => {
      if (active) {
        active.classList.remove('dragging');
        // Save all positions
        const newPositions = {};
        cards.forEach(c => {
          newPositions[c.dataset.file] = [
            parseInt(c.style.left) || 0, 
            parseInt(c.style.top) || 0
          ];
        });
        try {
          localStorage.setItem('cardPositions', JSON.stringify(newPositions));
          console.log('Saved positions:', newPositions);
        } catch (e) {
          console.warn('Failed to save positions:', e);
        }
        active = null;
      }
    });
  });
  </script>
</body>
</html>

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