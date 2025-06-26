<?php
// index.php — Fixed dashboard with proper card isolation
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/public/css/styles.css">
    <style>
        body {
            background: #1f2937;
            color: white;
            margin: 0;
            padding: 0;
        }
        
        .dashboard-container {
            position: relative;
            width: 100%;
            height: 100vh;
            background: #1f2937;
            background-image: radial-gradient(circle, #4b5563 1px, transparent 1px);
            background-size: 20px 20px;
            overflow: hidden;
        }
        
        .card-wrapper {
            position: absolute;
            cursor: grab;
            user-select: none;
            transition: box-shadow 0.15s ease;
            border-radius: 8px;
            background: var(--bg-accent, #374151);
            /* CRITICAL: Prevent grouping */
            isolation: isolate;
            contain: layout style;
        }
        
        .card-wrapper:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }
        
        .card-wrapper.dragging {
            z-index: 1000;
            transform: scale(1.05);
            cursor: grabbing;
        }
        
        .card-wrapper.dragging.valid {
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.5);
        }
        
        .card-wrapper.dragging.invalid {
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5);
        }
        
        .card-wrapper.will-nudge {
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.5);
            z-index: 100;
        }
        
        .card-size-small { width: 240px; height: 140px; }
        .card-size-medium { width: 300px; height: 180px; }
        .card-size-large { width: 380px; height: 220px; }
        
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
        }
        
        .settings-menu {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1001;
            background: #374151;
            padding: 12px;
            border-radius: 8px;
            color: white;
        }
        
        /* CRITICAL: Prevent event bubbling issues */
        .card-wrapper .card-header {
            pointer-events: auto;
            position: relative;
            z-index: 1;
        }
        
        .card-wrapper .card-content {
            pointer-events: none;
            position: relative;
        }
        
        /* Allow specific interactive elements */
        .card-wrapper button,
        .card-wrapper input,
        .card-wrapper select,
        .card-wrapper textarea,
        .card-wrapper a {
            pointer-events: auto;
        }
    </style>
</head>
<body>
<main>
    <div class="settings-menu">
        <h2 style="margin-top: 0;">Card Visibility</h2>
        <?php
        $cardFiles = glob(__DIR__ . '/cards/*.php');
        foreach ($cardFiles as $index => $cardPath) {
            $cardId = 'card' . $index;
            $cardName = basename($cardPath, '.php');
            echo "<label><input type='checkbox' id='{$cardId}-toggle' data-card-id='{$cardId}'> {$cardName}</label><br>\n";
        }
        ?>
    </div>

    <div class="dashboard-container" id="dashboardContainer">
        <?php
        // Define card sizes and positions
        $cardConfigs = [
            0 => ['size' => 'large', 'x' => 20, 'y' => 20],
            1 => ['size' => 'medium', 'x' => 420, 'y' => 20],
            2 => ['size' => 'small', 'x' => 740, 'y' => 20],
            3 => ['size' => 'small', 'x' => 20, 'y' => 260],
            4 => ['size' => 'large', 'x' => 280, 'y' => 260],
            5 => ['size' => 'medium', 'x' => 680, 'y' => 220],
            6 => ['size' => 'small', 'x' => 20, 'y' => 420],
            7 => ['size' => 'medium', 'x' => 280, 'y' => 500],
        ];
        
        foreach ($cardFiles as $index => $cardPath) {
            $cardId = 'card' . $index;
            $title = basename($cardPath, '.php');
            $config = $cardConfigs[$index] ?? ['size' => 'medium', 'x' => ($index % 3) * 340 + 20, 'y' => floor($index / 3) * 220 + 20];
            
            echo "<div class='card-wrapper card-size-{$config['size']}' 
                       id='{$cardId}' 
                       data-size='{$config['size']}' 
                       data-x='{$config['x']}' 
                       data-y='{$config['y']}'
                       data-card-index='{$index}'
                       style='left: {$config['x']}px; top: {$config['y']}px; display: none;'>\n";
            
            $allowMinimize = true;
            $allowSettings = true;
            include __DIR__ . '/includes/card_header.php';
            echo "<div class='card-content neumorphic glow'>\n";
            include $cardPath;
            echo "</div></div>\n";
        }
        ?>
    </div>
</main>

<script>
// Constants
const GRID_SIZE = 20;
const MAX_NUDGE_DISTANCE = 60;
const CARD_SIZES = {
    small: { width: 240, height: 140 },
    medium: { width: 300, height: 180 },
    large: { width: 380, height: 220 }
};

// State - CRITICAL: Use WeakMap to prevent cross-card interference
const cardStates = new WeakMap();
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

const container = document.getElementById('dashboardContainer');

// CRITICAL: Get cards fresh each time to avoid stale references
function getVisibleCards() {
    return Array.from(document.querySelectorAll('.card-wrapper')).filter(card => 
        card.style.display !== 'none'
    );
}

function getAllCards() {
    return Array.from(document.querySelectorAll('.card-wrapper'));
}

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
    return x + cardSize.width <= window.innerWidth && y + cardSize.height <= window.innerHeight - 100;
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
    
    const visibleCards = getVisibleCards();
    for (const card of visibleCards) {
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
        { dx: 0, dy: -GRID_SIZE }, { dx: 0, dy: GRID_SIZE },
        { dx: -GRID_SIZE, dy: 0 }, { dx: GRID_SIZE, dy: 0 },
        { dx: -GRID_SIZE, dy: -GRID_SIZE }, { dx: GRID_SIZE, dy: -GRID_SIZE },
        { dx: -GRID_SIZE, dy: GRID_SIZE }, { dx: GRID_SIZE, dy: GRID_SIZE }
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
    
    const visibleCards = getVisibleCards();
    const overlappingCards = visibleCards.filter(card => {
        if (card === draggedCard) return false;
        
        const pos = getCardPosition(card);
        const cardSize = CARD_SIZES[pos.size];
        const cardRect = { x: pos.x, y: pos.y, width: cardSize.width, height: cardSize.height };
        
        return rectanglesOverlap(dropRect, cardRect);
    });
    
    if (overlappingCards.length === 0) {
        return { canPlace: true, nudges: [] };
    }
    
    const sortedOverlapping = overlappingCards.sort((a, b) => {
        const sizeOrder = { small: 1, medium: 2, large: 3 };
        const sizeA = sizeOrder[getCardPosition(a).size];
        const sizeB = sizeOrder[getCardPosition(b).size];
        return sizeA - sizeB;
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
    const allCards = getAllCards();
    allCards.forEach(card => {
        card.classList.remove('will-nudge');
        const indicator = card.querySelector('.nudge-indicator');
        if (indicator) indicator.remove();
    });
    
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
        ${nudgeCount > 0 ? `<div class="drag-info-badge nudging">Nudging ${nudgeCount}</div>` : ''}
    `;
}

// CRITICAL: Fixed event handlers to prevent grouping
function handleMouseDown(e) {
    // Only handle mousedown on card headers or card wrappers directly
    const cardWrapper = e.target.closest('.card-wrapper');
    if (!cardWrapper) return;
    
    // Don't drag if clicking on interactive elements
    if (e.target.matches('button, input, select, textarea, a, [data-action]')) {
        return;
    }
    
    // Only allow dragging from card header
    const cardHeader = e.target.closest('.card-header');
    if (!cardHeader) return;
    
    const pos = getCardPosition(cardWrapper);
    console.log('Starting drag for card:', cardWrapper.id, 'at position:', pos);
    
    dragState = {
        isDragging: true,
        draggedElement: cardWrapper,
        startX: e.clientX,
        startY: e.clientY,
        startCardX: pos.x,
        startCardY: pos.y,
        currentX: pos.x,
        currentY: pos.y,
        originalX: pos.x,
        originalY: pos.y
    };
    
    cardWrapper.classList.add('dragging');
    e.preventDefault();
    e.stopPropagation();
}

function handleMouseMove(e) {
    if (!dragState.isDragging || !dragState.draggedElement) return;
    
    const deltaX = e.clientX - dragState.startX;
    const deltaY = e.clientY - dragState.startY;
    
    dragState.currentX = dragState.startCardX + deltaX;
    dragState.currentY = dragState.startCardY + deltaY;
    
    dragState.draggedElement.style.left = dragState.currentX + 'px';
    dragState.draggedElement.style.top = dragState.currentY + 'px';
    
    const snappedX = snapToGrid(dragState.currentX);
    const snappedY = snapToGrid(dragState.currentY);
    const pos = getCardPosition(dragState.draggedElement);
    
    if (isWithinBounds(snappedX, snappedY, pos.size)) {
        const nudgePlan = calculateNudgePlan(dragState.draggedElement, snappedX, snappedY);
        
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
    
    console.log('Ending drag for card:', dragState.draggedElement.id);
    
    const snappedX = snapToGrid(dragState.currentX);
    const snappedY = snapToGrid(dragState.currentY);
    const pos = getCardPosition(dragState.draggedElement);
    
    if (!isWithinBounds(snappedX, snappedY, pos.size)) {
        setCardPosition(dragState.draggedElement, dragState.originalX, dragState.originalY);
    } else {
        const nudgePlan = calculateNudgePlan(dragState.draggedElement, snappedX, snappedY);
        
        if (nudgePlan.canPlace) {
            nudgePlan.nudges.forEach(nudge => {
                setCardPosition(nudge.card, nudge.toX, nudge.toY);
            });
            setCardPosition(dragState.draggedElement, snappedX, snappedY);
        } else {
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

// CRITICAL: Fixed card visibility toggles to prevent syncing
function handleCardToggle(e) {
    const checkbox = e.target;
    const cardId = checkbox.dataset.cardId;
    const card = document.getElementById(cardId);
    
    if (!card) {
        console.error('Card not found:', cardId);
        return;
    }
    
    console.log('Toggling card:', cardId, 'to', checkbox.checked ? 'visible' : 'hidden');
    
    if (checkbox.checked) {
        card.style.display = 'block';
        // Force a reflow to ensure the card is properly rendered
        card.offsetHeight;
    } else {
        card.style.display = 'none';
    }
    
    e.stopPropagation();
}

// Initialize event listeners
document.addEventListener('mousedown', handleMouseDown);
document.addEventListener('mousemove', handleMouseMove);
document.addEventListener('mouseup', handleMouseUp);

// CRITICAL: Fixed checkbox event listeners
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.settings-menu input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleCardToggle);
    });
    
    console.log('Dashboard initialized with', getAllCards().length, 'cards');
});
</script>
</body>
</html>