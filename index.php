@@ <body class="h-full flex flex-col">
   <?php include __DIR__ . '/includes/header.php'; ?>

-  <div class="flex flex-1 overflow-hidden">
+  <div class="flex flex-1 overflow-hidden">
     <?php include __DIR__ . '/includes/navigation.php'; ?>

-    <main class="flex-1 p-6">
-      <!-- ABSOLUTE LAYOUT CONTAINER -->
-      <div class="dashboard-container" id="dashboard">
+    <main class="flex-1 relative p-6">  <!-- make main relative -->
+      <!-- ABSOLUTE LAYOUT CONTAINER -->
+      <div class="dashboard-container absolute inset-0" id="dashboard">
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
-      </div>
+      </div>
     </main>
   </div>

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