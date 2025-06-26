<?php
// index.php — Fixed with proper card ID consistency
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
            isolation: isolate;
            contain: layout style;
        }
        
        .card-wrapper:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }
        
        .card-wrapper.dragging {
            opacity: 0.9;
            transform: scale(1.02);
            z-index: 1000;
            box-shadow: 0 12px 32px rgba(0,0,0,0.25), 0 0 12px rgba(0,240,255,0.3);
            transition: none;
        }
        
        .card-wrapper.minimized {
            height: 3rem; /* Adjust to match header height */
            overflow: hidden;
            /* Optional: visually indicate minimized state */
            opacity: 0.8;
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* Styles for the "cannot place" feedback */
        .card-wrapper.cannot-place {
            box-shadow: 0 0 15px 5px rgba(255, 0, 0, 0.5) !important; /* Red glow */
            border: 2px solid red;
        }

        .card-header {
            cursor: grab;
        }

        .settings-menu {
            position: absolute;
            background: var(--bg-accent, #374151);
            border-radius: 8px;
            padding: 10px;
            right: 10px;
            top: 40px; /* Adjust based on header height */
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            min-width: 150px;
        }
        .settings-menu label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
            font-size: 0.9em;
        }
        .settings-menu button {
            width: 100%;
            padding: 5px;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <main class="dashboard-container" id="dashboardContainer">
        <header class="fixed top-0 left-0 right-0 p-4 flex justify-between items-center z-50">
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <div class="relative">
                <button id="settingsToggle" class="neu-btn" aria-label="Open settings">
                    <i data-feather="menu"></i>
                </button>
                <div id="settingsMenu" class="settings-menu hidden">
                    <h3 class="font-semibold mb-2">Card Visibility</h3>
                    <div id="cardCheckboxes"></div>
                    <button id="showAllCards" class="neu-btn text-blue-300">Show All</button>
                    <button id="hideAllCards" class="neu-btn text-red-300">Hide All</button>
                </div>
            </div>
        </header>

        <div class="absolute inset-0 pt-16"> <?php include __DIR__ . '/cards/CardAppLog.php'; ?>
            <?php include __DIR__ . '/cards/ConsoleLogCard.php'; ?>
            <?php include __DIR__ . '/cards/CardChart.php'; ?>
            <?php include __DIR__ . '/cards/CardDrilldown.php'; ?>
            <?php include __DIR__ . '/cards/CardExpandable.php'; ?>
            <?php include __DIR__ . '/cards/CardKPI.php'; ?>
            <?php include __DIR__ . '/cards/CardLarge.php'; ?>
            <?php include __DIR__ . '/cards/CardList.php'; ?>
            <?php include __DIR__ . '/cards/CardSmall.php'; ?>
            <?php include __DIR__ . '/cards/SampleCard.php'; ?>
            <?php include __DIR__ . '/cards/CardTemplate.php'; ?>
            
        </div>
    </main>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
    // Theme toggle (if needed, otherwise remove)
    document.documentElement.setAttribute('data-theme', 'dark');

    // Grid and card dimensions
    const GRID_SIZE = 10; // pixels
    const CARD_WIDTH = 280; // pixels, from CSS variable --card-width
    const CARD_HEIGHT = 180; // approximate height, will vary by content

    let activeCard = null;
    let initialMouseX, initialMouseY;
    let initialCardX, initialCardY;
    let currentGridX = 0, currentGridY = 0;
    let isDragging = false;
    let lastMoveTime = 0;
    const THROTTLE_INTERVAL = 16; // ms (approx 60fps)

    // Debounce for saveLayout
    let saveLayoutTimeout;

    // This set will store the IDs of all currently visible cards
    let visibleCardIds = new Set();

    // Store card visibility state globally (or via localStorage)
    // Initialize from localStorage if available, otherwise default to all true
    window.cardVisibility = JSON.parse(localStorage.getItem('cardVisibility')) || {};

    // Function to get all card wrappers
    function getAllCards() {
        return Array.from(document.querySelectorAll('.card-wrapper'));
    }

    // Function to calculate grid position from pixel coordinates
    function toGrid(pixel) {
        return Math.round(pixel / GRID_SIZE);
    }

    // Function to calculate pixel coordinates from grid position
    function fromGrid(grid) {
        return grid * GRID_SIZE;
    }

    // Function to update card position
    function updateCardPosition(cardElement, x, y) {
        cardElement.style.left = `${x}px`;
        cardElement.style.top = `${y}px`;
        cardElement.dataset.gridX = toGrid(x);
        cardElement.dataset.gridY = toGrid(y);
    }

    // Save layout to local storage
    function saveLayout() {
        clearTimeout(saveLayoutTimeout);
        saveLayoutTimeout = setTimeout(() => {
            const layout = getAllCards().map(card => ({
                id: card.id,
                x: parseInt(card.style.left, 10),
                y: parseInt(card.style.top, 10),
                gridX: parseInt(card.dataset.gridX, 10),
                gridY: parseInt(card.dataset.gridY, 10),
                minimized: card.classList.contains('minimized')
            }));
            localStorage.setItem('dashboardLayout', JSON.stringify(layout));
            console.log('Layout saved:', layout);
        }, 500); // Debounce by 500ms
    }

    // Load layout from local storage
    function loadLayout() {
        const layout = JSON.parse(localStorage.getItem('dashboardLayout'));
        if (layout) {
            layout.forEach(savedCard => {
                const card = document.getElementById(savedCard.id);
                if (card) {
                    updateCardPosition(card, savedCard.x, savedCard.y);
                    if (savedCard.minimized) {
                        card.classList.add('minimized');
                    } else {
                        card.classList.remove('minimized');
                    }
                    // Ensure visibility state is correctly set after loading layout
                    // If window.cardVisibility is not explicitly false for a card, show it.
                    // If cardVisibility is not yet defined for a card, default to visible.
                    if (window.cardVisibility[savedCard.id] !== false) {
                        card.style.display = ''; // Show card
                        visibleCardIds.add(savedCard.id);
                        // Also ensure the checkbox reflects this state
                        const checkbox = document.querySelector(`.settings-menu input[data-card-target="${savedCard.id}"]`);
                        if (checkbox) checkbox.checked = true;
                    } else {
                        card.style.display = 'none'; // Hide card
                        visibleCardIds.delete(savedCard.id);
                        const checkbox = document.querySelector(`.settings-menu input[data-card-target="${savedCard.id}"]`);
                        if (checkbox) checkbox.checked = false;
                    }
                }
            });
            console.log('Layout loaded.');
        } else {
            console.log('No saved layout found. Initializing default positions.');
            // If no layout is found, initialize cards in a default flow
            initializeDefaultCardPositions();
        }
        // Update settings checkboxes based on loaded visibility
        updateSettingsCheckboxes();
    }

    function initializeDefaultCardPositions() {
        const container = document.querySelector('.dashboard-container');
        const containerRect = container.getBoundingClientRect();
        const cards = getAllCards();
        let xOffset = GRID_SIZE * 2; // Starting X with some padding
        let yOffset = GRID_SIZE * 2; // Starting Y with some padding
        let rowHeight = 0;

        cards.forEach(card => {
            // Only position cards that are not explicitly hidden by initial settings
            // This check ensures we don't calculate positions for cards that are meant to be hidden
            if (card.style.display !== 'none') {
                const cardRect = card.getBoundingClientRect();
                // If card would overflow container width, move to next row
                if (xOffset + cardRect.width + GRID_SIZE * 2 > containerRect.width && xOffset !== GRID_SIZE * 2) {
                    xOffset = GRID_SIZE * 2;
                    yOffset += rowHeight + GRID_SIZE * 2;
                    rowHeight = 0;
                }

                updateCardPosition(card, xOffset, yOffset);
                xOffset += cardRect.width + GRID_SIZE * 2;
                rowHeight = Math.max(rowHeight, cardRect.height);
            }
        });
        saveLayout(); // Save these initial positions
    }

    // Settings menu logic
    const settingsToggle = document.getElementById('settingsToggle');
    const settingsMenu = document.getElementById('settingsMenu');
    const cardCheckboxesContainer = document.getElementById('cardCheckboxes');

    settingsToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        settingsMenu.classList.toggle('hidden');
        if (!settingsMenu.classList.contains('hidden')) {
            feather.replace(); // Ensure icons are visible when menu opens
        }
    });

    document.addEventListener('click', (e) => {
        if (!settingsMenu.contains(e.target) && !settingsToggle.contains(e.target)) {
            settingsMenu.classList.add('hidden');
        }
    });

    // Function to dynamically populate settings checkboxes
    function populateSettingsCheckboxes() {
        cardCheckboxesContainer.innerHTML = ''; // Clear existing
        getAllCards().forEach(card => {
            if (card.id) { // Ensure card has an ID
                const isVisible = window.cardVisibility[card.id] !== false; // Default to true if not set
                cardCheckboxesContainer.insertAdjacentHTML('beforeend', `
                    <label>
                        <input type="checkbox" data-card-target="${card.id}" ${isVisible ? 'checked' : ''}>
                        ${card.querySelector('h2').textContent || card.id}
                    </label>
                `);
            }
        });
        // Re-attach event listeners after populating
        attachCheckboxListeners();
    }

    function attachCheckboxListeners() {
        document.querySelectorAll('.settings-menu input[type="checkbox"]').forEach(checkbox => {
            checkbox.removeEventListener('change', handleCheckboxChange); // Prevent duplicate listeners
            checkbox.addEventListener('change', handleCheckboxChange);
        });
    }

    function handleCheckboxChange(e) {
        const cardId = e.target.dataset.cardTarget;
        const isChecked = e.target.checked;
        toggleCard(cardId, isChecked);
    }

    function toggleCard(cardId, show) {
        const card = document.getElementById(cardId);
        if (card) {
            card.style.display = show ? '' : 'none';
            window.cardVisibility[cardId] = show; // Update global state
            if (show) {
                visibleCardIds.add(cardId);
            } else {
                visibleCardIds.delete(cardId);
            }
            localStorage.setItem('cardVisibility', JSON.stringify(window.cardVisibility));
            saveLayout(); // Save layout after changing visibility
        }
    }

    function updateSettingsCheckboxes() {
        document.querySelectorAll('.settings-menu input[type="checkbox"]').forEach(checkbox => {
            const cardId = checkbox.dataset.cardTarget;
            checkbox.checked = window.cardVisibility[cardId] !== false;
        });
    }


    // Mouse down event handler for dragging
    document.addEventListener('mousedown', (e) => {
        // Only drag if it's a card header and not a button within it
        const cardHeader = e.target.closest('.card-header');
        if (cardHeader) {
            // Ensure the click wasn't on a button within the header (like minimize/settings)
            if (e.target.closest('button')) {
                return; 
            }

            activeCard = cardHeader.closest('.card-wrapper');
            if (activeCard) {
                isDragging = true;
                activeCard.classList.add('dragging');
                
                // Bring the dragged card to the front by increasing its z-index
                activeCard.style.zIndex = 1000;

                initialMouseX = e.clientX;
                initialMouseY = e.clientY;
                initialCardX = activeCard.offsetLeft;
                initialCardY = activeCard.offsetTop;

                // Prevent default to avoid image dragging, text selection etc.
                e.preventDefault(); 
            }
        }
    });

    // Mouse move event handler for dragging
    document.addEventListener('mousemove', (e) => {
        if (!isDragging || !activeCard) return;

        // Throttle mousemove events to improve performance
        const now = Date.now();
        if (now - lastMoveTime < THROTTLE_INTERVAL) {
            return;
        }
        lastMoveTime = now;

        const dx = e.clientX - initialMouseX;
        const dy = e.clientY - initialMouseY;

        let newX = initialCardX + dx;
        let newY = initialCardY + dy;

        // Get dashboard container dimensions for clamping
        const dashboardContainer = document.querySelector('.dashboard-container');
        const containerRect = dashboardContainer.getBoundingClientRect();
        const cardRect = activeCard.getBoundingClientRect(); // Get current size of active card

        // Clamp card position within dashboard boundaries
        newX = Math.max(0, Math.min(newX, containerRect.width - cardRect.width));
        newY = Math.max(0, Math.min(newY, containerRect.height - cardRect.height));

        // Snap to grid
        currentGridX = toGrid(newX);
        currentGridY = toGrid(newY);

        updateCardPosition(activeCard, fromGrid(currentGridX), fromGrid(currentGridY));

        // Check for overlaps with other cards and update "cannot place" feedback
        checkOverlap(activeCard);
    });

    // Mouse up event handler for dropping
    document.addEventListener('mouseup', () => {
        if (isDragging && activeCard) {
            isDragging = false;
            activeCard.classList.remove('dragging');
            activeCard.style.zIndex = 1; // Reset z-index to default

            // Check if the current position is valid after dropping
            const canPlace = !activeCard.classList.contains('cannot-place');
            if (canPlace) {
                saveLayout(); // Save the new, valid position
                console.log('Card dropped at valid position:', activeCard.id, activeCard.style.left, activeCard.style.top);
            } else {
                // If the position is invalid, ideally revert to the last valid position.
                // For this implementation, we'll save the layout as is but log a warning.
                // A more robust solution would store the last valid position and revert to it here.
                console.warn('Card dropped at invalid position. Reverting not implemented yet, saving current position.');
                saveLayout(); // Still save, but note the warning.
            }
            
            // Remove 'cannot-place' class after drop, regardless of validity, 
            // to clear visual feedback for the next drag operation.
            activeCard.classList.remove('cannot-place');
            activeCard = null; // Clear active card
        }
    });

    // New constant for collision tolerance
    const COLLISION_TOLERANCE = 5; // Pixels, e.g., half of GRID_SIZE

    // Function to check for overlaps with other cards
    function checkOverlap(draggedCard) {
        const cards = getAllCards();
        const draggedRect = draggedCard.getBoundingClientRect();
        let overlap = false;

        cards.forEach(card => {
            // Skip checking against itself, minimized cards, and hidden cards
            if (card === draggedCard || card.classList.contains('minimized') || card.style.display === 'none') {
                return;
            }

            const cardRect = card.getBoundingClientRect();

            // AABB (Axis-Aligned Bounding Box) collision detection with tolerance
            // 'colliding' is true if the rectangles overlap
            const colliding = !(
                draggedRect.right <= cardRect.left + COLLISION_TOLERANCE ||
                draggedRect.left >= cardRect.right - COLLISION_TOLERANCE ||
                draggedRect.bottom <= cardRect.top + COLLISION_TOLERANCE ||
                draggedRect.top >= cardRect.bottom - COLLISION_TOLERANCE
            );

            if (colliding) {
                overlap = true;
            }
        });

        // Add or remove 'cannot-place' class based on overlap status
        if (overlap) {
            draggedCard.classList.add('cannot-place');
        } else {
            draggedCard.classList.remove('cannot-place');
        }
    }

    // CRITICAL: Handle minimize/settings/close button clicks using event delegation
    // This allows buttons inside cards to be interactive without interfering with drag
    function handleCardButtonClick(e) {
        const button = e.target.closest('button.neu-btn');
        if (!button) return;

        const action = button.dataset.action;
        const cardId = button.dataset.card;
        const cardWrapper = button.closest('.card-wrapper');

        if (!cardWrapper) return; // Should not happen if button.dataset.card is correctly set

        switch (action) {
            case 'minimize':
                cardWrapper.classList.toggle('minimized');
                // Persist minimized state (optional, but good for layout saving)
                saveLayout();
                // Update feather icon based on state
                const icon = button.querySelector('i');
                if (icon) {
                    icon.setAttribute('data-feather', cardWrapper.classList.contains('minimized') ? 'chevron-up' : 'chevron-down');
                    feather.replace(); // Re-render icon
                }
                break;
            case 'settings':
                // Implement card-specific settings modal or dropdown here
                console.log(`Settings clicked for card: ${cardId}`);
                alert(`Settings for ${cardId} (not implemented)`);
                break;
            case 'close':
                // Remove card from DOM
                cardWrapper.remove();
                // Update visibility and save layout
                if (window.cardVisibility) {
                    window.cardVisibility[cardId] = false;
                    localStorage.setItem('cardVisibility', JSON.stringify(window.cardVisibility));
                    // Also update the checkbox in settings menu
                    const checkbox = document.querySelector(`.settings-menu input[data-card-target="${cardId}"]`);
                    if (checkbox) checkbox.checked = false;
                }
                visibleCardIds.delete(cardId);
                saveLayout();
                break;
            case 'expand': // For CardExpandable
                // The expandable card has its own JS to handle this
                break;
            case 'drilldown': // For CardDrilldown
                // The drilldown card has its own JS to handle this
                break;
        }
    }

    // Attach delegated click listener to dashboard container
    document.addEventListener('click', handleCardButtonClick);

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Populate settings checkboxes for the first time
        populateSettingsCheckboxes();
        // Load layout after checkboxes are populated and initial card states are set
        loadLayout();

        // Attach listeners for "Show All" and "Hide All" buttons
        document.getElementById('showAllCards').addEventListener('click', function(e) {
            e.preventDefault();
            getAllCards().forEach(card => {
                toggleCard(card.id, true);
            });
            updateSettingsCheckboxes(); // Ensure checkboxes reflect the change
        });
        
        document.getElementById('hideAllCards').addEventListener('click', function(e) {
            e.preventDefault();
            getAllCards().forEach(card => {
                toggleCard(card.id, false);
            });
            updateSettingsCheckboxes(); // Ensure checkboxes reflect the change
        });
        
        console.log('Dashboard initialized with', getAllCards().length, 'cards');
    });
    </script>
</body>
</html>