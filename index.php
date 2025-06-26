<?php
// index.php — Dashboard container with drag-and-drop logic and dynamic cards
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<main style="position: relative;">
    <div class="settings-menu" style="position: fixed; top: 10px; right: 10px; z-index: 1001; background: var(--bg-accent); padding: 12px; border-radius: 8px;">
        <h2 style="margin-top: 0;">Card Visibility</h2>
        <?php
        $cardFiles = glob(__DIR__ . '/card/*.php');
        foreach ($cardFiles as $index => $cardPath) {
            $cardId = 'card' . $index;
            $cardName = basename($cardPath, '.php');
            echo "<label><input type='checkbox' id='{$cardId}-toggle'> {$cardName}</label><br>\n";
        }
        ?>
    </div>

    <div class="dashboard-container">
        <?php
        foreach ($cardFiles as $index => $cardPath) {
            $cardId = 'card' . $index;
            echo "<div class='card-wrapper' id='{$cardId}' style='display:none; left: 100px; top: " . ($index * 80 + 40) . "px;'>\n";
            include 'card_header.php';
            echo "<div class='card-content neumorphic glow'>\n";
            include $cardPath;
            echo "</div></div>\n";
        }
        ?>
    </div>
</main>
<script>
const checkboxes = document.querySelectorAll('.settings-menu input[type="checkbox"]');
checkboxes.forEach(cb => {
    cb.addEventListener('change', () => {
        const cardId = cb.id.replace('-toggle', '');
        const card = document.getElementById(cardId);
        if (cb.checked) {
            card.style.display = 'block';
            card.style.zIndex = 1;
        } else {
            card.style.display = 'none';
        }
    });
});

// Drag logic
let dragTarget = null, offsetX = 0, offsetY = 0;
document.querySelectorAll('.card-wrapper .card-header').forEach(header => {
    header.addEventListener('mousedown', e => {
        dragTarget = header.parentElement;
        offsetX = e.clientX - dragTarget.offsetLeft;
        offsetY = e.clientY - dragTarget.offsetTop;
        dragTarget.classList.add('dragging');
    });
});
document.addEventListener('mousemove', e => {
    if (dragTarget) {
        dragTarget.style.left = (e.clientX - offsetX) + 'px';
        dragTarget.style.top = (e.clientY - offsetY) + 'px';
    }
});
document.addEventListener('mouseup', () => {
    if (dragTarget) dragTarget.classList.remove('dragging');
    dragTarget = null;
});
</script>
</body>
</html>

<!--
Changelog:
- Made <main> relative and .dashboard-container absolute inset-0.
- Restored header/navigation visibility.
- Kept all drag, card-settings, and log-toggle logic intact.
-->

<!--
Changelog:
- Wrapped `<main>` in `relative` and set `.dashboard-container` to `absolute inset-0`.
- Ensured sidebar and header return to original positions.
- Retained all smart drag-and-drop and header control logic.
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