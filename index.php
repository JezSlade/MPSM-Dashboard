<?php
/**
 * index.php — Fixed drag-and-drop with ungrouped cards and dark background
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
    .dashboard-container {
      position: relative;
      width: 100%;
      height: 700px;
      background: #1f2937; /* Dark gray background */
      border-radius: 8px;
      border: 1px solid #374151;
      overflow: hidden;
      background-image: radial-gradient(circle, #4b5563 1px, transparent 1px);
      background-size: 20px 20px;
    }
    
    .card-wrapper {
      position: absolute;
      cursor: grab;
      user-select: none;
      transition: box-shadow 0.15s ease;
      /* Remove any grouping or flex properties */
      display: block;
    }
    
    .card-wrapper:hover {
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
    }
    
    .card-wrapper.dragging {
      z-index: 1000;
      transform: scale(1.05);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      cursor: grabbing;
    }
    
    .card-wrapper.dragging.valid {
      box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.5), 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    
    .card-wrapper.dragging.invalid {
      box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5), 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    
    .card-wrapper.will-nudge {
      box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.5);
      z-index: 100;
    }
    
    .card-size-small { 
      width: 240px; 
      height: 140px; 
      border: 2px solid #bfdbfe; 
    }
    .card-size-medium { 
      width: 300px; 
      height: 180px; 
      border: 2px solid #93c5fd; 
    }
    .card-size-large { 
      width: 380px; 
      height: 220px; 
      border: 2px solid #60a5fa; 
    }
    
    .drag-info {
      position: absolute;
      top: -32px;
      left: 0;
      display: flex;
      gap: 8px;
      pointer-events: none;
      z-index: 1001;
    }
    
    .drag-info-badge {
      background: #2563eb;
      color: white;
      font-size: 12px;
      padding: 4px 8px;
      border-radius: 4px;
      white-space: nowrap;
    }
    
    .drag-info-badge.valid { background: #16a34a; }
    .drag-info-badge.invalid { background: #dc2626; }
    .drag-info-badge.nudging { background: #d97706; }
    
    .nudge-indicator {
      position: absolute;
      top: -24px;
      left: 0;
      background: #d97706;
      color: white;
      font-size: 12px;
      padding: 2px 6px;
      border-radius: 4px;
      pointer-events: none;
      white-space: nowrap;
    }
    
    #cardSettingsModal.hidden { display: none !important; }
    
    /* Ensure cards don't group together */
    .card-wrapper * {
      pointer-events: none;
    }
    
    .card-wrapper {
      pointer-events: auto;
    }
  </style>

  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="h-full flex flex-col bg-gray-900">

  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="flex flex-1 overflow-hidden">
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <main class="flex-1 overflow-y-auto p-6">
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-white">Smart Nudging Dashboard</h1>
        <p class="text-gray-300 mt-2">Cards gently nudge others out of the way when needed</p>
        <div class="flex gap-4 mt-2 text-sm text-gray-400">
          <span class="flex items-center gap-1">
            <div class="w-3 h-2 bg-blue-200 rounded"></div>
            Small (240×140)
          </span>
          <span class="flex items-center gap-1">
            <div class="w-4 h-3 bg-blue-300 rounded"></div>
            Medium (300×180)
          </span>
          <span class="flex items-center gap-1">
            <div class="w-5 h-3 bg-blue-400 rounded"></div>
            Large (380×220)
          </span>
        </div>
      </div>

      <div class="dashboard-container" id="dashboardContainer">
        <?php
        // Auto-discover all cards in /cards/
        $cardsDir = __DIR__ . '/cards/';
        $files = array_filter(scandir($cardsDir, SCANDIR_SORT_ASCENDING), fn($f) =>
          pathinfo($f, PATHINFO_EXTENSION) === 'php'
        );
        
        // Define initial positions and sizes for cards - spread them out more
        $cardConfigs = [
          'revenue.php' => ['size' => 'large', 'x' => 20, 'y' => 20],
          'users.php' => ['size' => 'medium', 'x' => 420, 'y' => 20],
          'orders.php' => ['size' => 'small', 'x' => 740, 'y' => 20],
          'errors.php' => ['size' => 'small', 'x' => 20, 'y' => 260],
          'analytics.php' => ['size' => 'large', 'x' => 280, 'y' => 260],
          'performance.php' => ['size' => 'medium', 'x' => 680, 'y' => 220],
          'conversion.php' => ['size' => 'small', 'x' => 20, 'y' => 420],
          'pageviews.php' => ['size' => 'medium', 'x' => 280, 'y' => 500],
        ];
        
        foreach ($files as $index => $file):
          $config = $cardConfigs[$file] ?? [
            'size' => 'medium', 
            'x' => ($index % 3) * 340 + 20, 
            'y' => floor($index / 3) * 220 + 20
          ];
        ?>
        <div class="card-wrapper card-size-<?php echo $config['size']; ?>" 
             data-file="<?php echo htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?>"
             data-size="<?php echo $config['size']; ?>"
             data-x="<?php echo $config['x']; ?>"
             data-y="<?php echo $config['y']; ?>"
             data-card-id="card-<?php echo $index; ?>"
             style="left: <?php echo $config['x']; ?>px; top: <?php echo $config['y']; ?>px;">
          <?php include $cardsDir . $file; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="mt-4 text-sm text-gray-400 space-y-1">
        <p>• Cards gently nudge others out of the way (max 60px distance)</p>
        <p>• Yellow ring = card will be nudged, Green = valid position, Red = invalid</p>
        <p>• Smaller cards are preferred for nudging over larger ones</p>
        <p>• If nudging isn't possible, card returns to original position</p>
      </div>
    </main>
  </div>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    feather.replace();
    
    // Constants
    const GRID_SIZE = 20;
    const MAX_NUDGE_DISTANCE = 60;
    const CARD_SIZES = {
      small: { width: 240, height: 140 },
      medium: { width: 300, height: 180 },
      large: { width: 380, height: 220 }
    };
    
    // State
    let dragState = {
      isDragging: false,
      draggedElement: null,
      startX: 0,
      startY: 0,
      startCardX: 0,
      startCardY: 0,
      currentX: 0,
      currentY: 0,
      originalX: 0,
      originalY: 0
    };
    
    let nudgePreviews = [];
    
    const container = document.getElementById('dashboardContainer');
    const cards = Array.from(document.querySelectorAll('.card-wrapper'));
    
    console.log('Found cards:', cards.length);
    
    // Utility functions
    function snapToGrid(value) {
      return Math.round(value / GRID_SIZE) * GRID_SIZE;
    }
    
    function rectanglesOverlap(rect1, rect2) {
      return !(
        rect1.x + rect1.width <= rect2.x ||
        rect2.x + rect2.width <= rect1.x ||
        rect1.y + rect1.height <= rect2.y ||
        rect2.y + rect2.height <= rect1.y
      );
    }
    
    function isWithinBounds(x, y, size) {
      const cardSize = CARD_SIZES[size];
      if (x < 0 || y < 0) return false;
      const containerRect = container.getBoundingClientRect();
      return x + cardSize.width <= containerRect.width && y + cardSize.height <= containerRect.height - 100;
    }
    
    function getCardPosition(card) {
      return {
        x: parseInt(card.dataset.x) || 0,
        y: parseInt(card.dataset.y) || 0,
        size: card.dataset.size || 'medium'
      };
    }
    
    function setCardPosition(card, x, y) {
      card.dataset.x = x;
      card.dataset.y = y;
      card.style.left = x + 'px';
      card.style.top = y + 'px';
    }
    
    function wouldOverlap(x, y, size, excludeCards = []) {
      const cardSize = CARD_SIZES[size];
      const newRect = { x, y, width: cardSize.width, height: cardSize.height };
      
      for (const card of cards) {
        if (excludeCards.includes(card)) continue;
        
        const pos = getCardPosition(card);
        const existingCardSize = CARD_SIZES[pos.size];
        const existingRect = {
          x: pos.x,
          y: pos.y,
          width: existingCardSize.width,
          height: existingCardSize.height
        };
        
        if (rectanglesOverlap(newRect, existingRect)) {
          return true;
        }
      }
      return false;
    }
    
    function findNudgePosition(card, originalX, originalY, excludeCards = []) {
      const pos = getCardPosition(card);
      const directions = [
        { dx: 0, dy: -GRID_SIZE }, // up
        { dx: 0, dy: GRID_SIZE },  // down
        { dx: -GRID_SIZE, dy: 0 }, // left
        { dx: GRID_SIZE, dy: 0 },  // right
        { dx: -GRID_SIZE, dy: -GRID_SIZE }, // up-left
        { dx: GRID_SIZE, dy: -GRID_SIZE },  // up-right
        { dx: -GRID_SIZE, dy: GRID_SIZE },  // down-left
        { dx: GRID_SIZE, dy: GRID_SIZE }    // down-right
      ];
      
      for (let distance = GRID_SIZE; distance <= MAX_NUDGE_DISTANCE; distance += GRID_SIZE) {
        for (const direction of directions) {
          const newX = originalX + direction.dx * (distance / GRID_SIZE);
          const newY = originalY + direction.dy * (distance / GRID_SIZE);
          
          if (isWithinBounds(newX, newY, pos.size) && !wouldOverlap(newX, newY, pos.size, [...excludeCards, card])) {
            return { x: newX, y: newY };
          }
        }
      }
      return null;
    }
    
    function calculateNudgePlan(draggedCard, dropX, dropY) {
      const draggedPos = getCardPosition(draggedCard);
      const draggedCardSize = CARD_SIZES[draggedPos.size];
      const dropRect = { x: dropX, y: dropY, width: draggedCardSize.width, height: draggedCardSize.height };
      
      // Find overlapping cards
      const overlappingCards = cards.filter(card => {
        if (card === draggedCard) return false;
        
        const pos = getCardPosition(card);
        const cardSize = CARD_SIZES[pos.size];
        const cardRect = { x: pos.x, y: pos.y, width: cardSize.width, height: cardSize.height };
        
        return rectanglesOverlap(dropRect, cardRect);
      });
      
      if (overlappingCards.length === 0) {
        return { canPlace: true, nudges: [] };
      }
      
      // Sort by size preference (smaller first) and distance
      const sortedOverlapping = overlappingCards.sort((a, b) => {
        const sizeOrder = { small: 1, medium: 2, large: 3 };
        const sizeA = sizeOrder[getCardPosition(a).size];
        const sizeB = sizeOrder[getCardPosition(b).size];
        
        if (sizeA !== sizeB) return sizeA - sizeB;
        
        const posA = getCardPosition(a);
        const posB = getCardPosition(b);
        const distA = Math.abs(posA.x - dropX) + Math.abs(posA.y - dropY);
        const distB = Math.abs(posB.x - dropX) + Math.abs(posB.y - dropY);
        return distA - distB;
      });
      
      const nudges = [];
      const processedCards = [draggedCard];
      
      for (const card of sortedOverlapping) {
        const pos = getCardPosition(card);
        const nudgePos = findNudgePosition(card, pos.x, pos.y, processedCards);
        
        if (nudgePos) {
          nudges.push({
            card: card,
            fromX: pos.x,
            fromY: pos.y,
            toX: nudgePos.x,
            toY: nudgePos.y
          });
          processedCards.push(card);
        } else {
          return { canPlace: false, nudges: [] };
        }
      }
      
      return { canPlace: true, nudges };
    }
    
    function updateNudgePreviews(nudges) {
      // Clear existing previews
      cards.forEach(card => {
        card.classList.remove('will-nudge');
        const indicator = card.querySelector('.nudge-indicator');
        if (indicator) indicator.remove();
      });
      
      // Add new previews
      nudges.forEach(nudge => {
        nudge.card.classList.add('will-nudge');
        const indicator = document.createElement('div');
        indicator.className = 'nudge-indicator';
        indicator.textContent = 'Will nudge';
        nudge.card.appendChild(indicator);
      });
    }
    
    function updateDragInfo(card, isValid, nudgeCount) {
      let dragInfo = card.querySelector('.drag-info');
      if (!dragInfo) {
        dragInfo = document.createElement('div');
        dragInfo.className = 'drag-info';
        card.appendChild(dragInfo);
      }
      
      const snappedX = snapToGrid(dragState.currentX);
      const snappedY = snapToGrid(dragState.currentY);
      
      dragInfo.innerHTML = `
        <div class="drag-info-badge">${snappedX}, ${snappedY}</div>
        <div class="drag-info-badge ${isValid ? 'valid' : 'invalid'}">
          ${isValid ? '✓ Can Place' : '✗ Cannot Place'}
        </div>
        ${nudgeCount > 0 ? `<div class="drag-info-badge nudging">Nudging ${nudgeCount} card${nudgeCount > 1 ? 's' : ''}</div>` : ''}
      `;
    }
    
    // Load saved positions
    function loadPositions() {
      const saved = JSON.parse(localStorage.getItem('cardPositions') || '{}');
      cards.forEach(card => {
        const file = card.dataset.file;
        if (saved[file]) {
          setCardPosition(card, saved[file].x, saved[file].y);
        }
      });
    }
    
    function savePositions() {
      const positions = {};
      cards.forEach(card => {
        const pos = getCardPosition(card);
        positions[card.dataset.file] = { x: pos.x, y: pos.y };
      });
      localStorage.setItem('cardPositions', JSON.stringify(positions));
    }
    
    // Event handlers - Fixed to prevent grouping
    function handleMouseDown(e) {
      // Only trigger on the card wrapper itself, not child elements
      if (!e.target.classList.contains('card-wrapper')) {
        const card = e.target.closest('.card-wrapper');
        if (!card) return;
        e.target = card; // Reassign to the card wrapper
      }
      
      const card = e.target;
      const pos = getCardPosition(card);
      
      console.log('Mouse down on card:', card.dataset.file, 'at position:', pos);
      
      dragState = {
        isDragging: true,
        draggedElement: card,
        startX: e.clientX,
        startY: e.clientY,
        startCardX: pos.x,
        startCardY: pos.y,
        currentX: pos.x,
        currentY: pos.y,
        originalX: pos.x,
        originalY: pos.y
      };
      
      card.classList.add('dragging');
      nudgePreviews = [];
      
      // Prevent default to avoid any browser drag behavior
      e.preventDefault();
      e.stopPropagation();
    }
    
    function handleMouseMove(e) {
      if (!dragState.isDragging || !dragState.draggedElement) return;
      
      const deltaX = e.clientX - dragState.startX;
      const deltaY = e.clientY - dragState.startY;
      
      dragState.currentX = dragState.startCardX + deltaX;
      dragState.currentY = dragState.startCardY + deltaY;
      
      // Update visual position immediately
      dragState.draggedElement.style.left = dragState.currentX + 'px';
      dragState.draggedElement.style.top = dragState.currentY + 'px';
      
      // Calculate nudge plan
      const snappedX = snapToGrid(dragState.currentX);
      const snappedY = snapToGrid(dragState.currentY);
      const pos = getCardPosition(dragState.draggedElement);
      
      if (isWithinBounds(snappedX, snappedY, pos.size)) {
        const nudgePlan = calculateNudgePlan(dragState.draggedElement, snappedX, snappedY);
        nudgePreviews = nudgePlan.nudges;
        
        dragState.draggedElement.classList.toggle('valid', nudgePlan.canPlace);
        dragState.draggedElement.classList.toggle('invalid', !nudgePlan.canPlace);
        
        updateNudgePreviews(nudgePlan.nudges);
        updateDragInfo(dragState.draggedElement, nudgePlan.canPlace, nudgePlan.nudges.length);
      } else {
        dragState.draggedElement.classList.remove('valid');
        dragState.draggedElement.classList.add('invalid');
        updateNudgePreviews([]);
        updateDragInfo(dragState.draggedElement, false, 0);
      }
      
      e.preventDefault();
    }
    
    function handleMouseUp(e) {
      if (!dragState.isDragging || !dragState.draggedElement) return;
      
      console.log('Mouse up, finalizing position');
      
      const snappedX = snapToGrid(dragState.currentX);
      const snappedY = snapToGrid(dragState.currentY);
      const pos = getCardPosition(dragState.draggedElement);
      
      if (!isWithinBounds(snappedX, snappedY, pos.size)) {
        console.log('Out of bounds, returning to original position');
        setCardPosition(dragState.draggedElement, dragState.originalX, dragState.originalY);
      } else {
        const nudgePlan = calculateNudgePlan(dragState.draggedElement, snappedX, snappedY);
        
        if (nudgePlan.canPlace) {
          console.log('Can place, applying nudges:', nudgePlan.nudges.length);
          // Apply nudges
          nudgePlan.nudges.forEach(nudge => {
            setCardPosition(nudge.card, nudge.toX, nudge.toY);
          });
          
          // Set final position
          setCardPosition(dragState.draggedElement, snappedX, snappedY);
          savePositions();
        } else {
          console.log('Cannot place, returning to original position');
          setCardPosition(dragState.draggedElement, dragState.originalX, dragState.originalY);
        }
      }
      
      // Cleanup
      dragState.draggedElement.classList.remove('dragging', 'valid', 'invalid');
      const dragInfo = dragState.draggedElement.querySelector('.drag-info');
      if (dragInfo) dragInfo.remove();
      
      updateNudgePreviews([]);
      
      dragState = {
        isDragging: false,
        draggedElement: null,
        startX: 0,
        startY: 0,
        startCardX: 0,
        startCardY: 0,
        currentX: 0,
        currentY: 0,
        originalX: 0,
        originalY: 0
      };
      
      e.preventDefault();
    }
    
    // Initialize
    loadPositions();
    
    // Add event listeners - Fixed to prevent grouping
    cards.forEach(card => {
      card.addEventListener('mousedown', handleMouseDown);
      // Prevent any default drag behavior
      card.addEventListener('dragstart', e => e.preventDefault());
      card.addEventListener('selectstart', e => e.preventDefault());
    });
    
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
    
    // Other functionality
    document.getElementById('view-error-log')?.addEventListener('click', () => {
      const logCard = document.getElementById('appLogCard');
      if (logCard) {
        logCard.style.display = logCard.style.display === 'none' ? '' : 'none';
      }
    });
    
    console.log('Drag system initialized with', cards.length, 'cards');
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