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

        /* Card-specific neumorphic adjustments */
        #ConsoleLogCard {
            /* Example initial position */
            left: 50px;
            top: 50px;
            z-index: 10;
        }

        /* Settings menu specific styles */
        .settings-menu {
            position: fixed; /* Use fixed for global menu */
            top: 20px;
            left: 20px;
            z-index: 50;
            padding: 15px;
            background: #2D3748; /* Darker background for distinction */
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        /* Basic form element styling */
        .form-checkbox {
            appearance: none;
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #6B7280;
            border-radius: 4px;
            background-color: #4B5563;
            cursor: pointer;
            position: relative;
            vertical-align: middle;
            margin-right: 8px;
            transition: all 0.2s ease;
        }

        .form-checkbox:checked {
            background-color: #3B82F6;
            border-color: #3B82F6;
        }

        .form-checkbox:checked::after {
            content: '✔';
            color: white;
            font-size: 10px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            line-height: 1;
        }

        .form-checkbox:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }

        /* Styles for the modal overlay and content (CardDrilldown) */
        #drillOverlay {
            /* flex utilities handled by JS for toggle */
            z-index: 1000; /* Ensure it's on top */
        }

        #drillOverlay > div { /* The actual modal content */
            max-width: 90%;
            max-height: 90%;
        }

        /* Hide specific cards initially if needed, managed by JS */
        /* .card-wrapper[data-card-hidden="true"] { display: none; } */

    </style>
</head>
<body class="overflow-hidden">
    <main class="relative h-screen w-screen overflow-hidden">
        <aside class="absolute top-0 left-0 p-4 z-50">
            <div class="neumorphic p-4 rounded-lg shadow-xl">
                <h2 class="text-lg font-semibold mb-3">Card Settings</h2>
                <div class="space-y-2" id="cardVisibilityCheckboxes">
                    <?php
                    // Dynamically include checkboxes for each card
                    $cardFiles = glob(__DIR__ . '/cards/Card*.php');
                    foreach ($cardFiles as $file) {
                        $cardId = basename($file, '.php');
                        // Skip CardTemplate and CardAppLog as requested
                        if ($cardId === 'CardTemplate' || $cardId === 'CardAppLog') {
                            continue;
                        }
                        echo '<label class="flex items-center space-x-2 text-sm">';
                        echo '<input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 rounded" data-card-target="' . htmlspecialchars($cardId) . '" checked>';
                        echo '<span>' . htmlspecialchars(preg_replace('/(?<!^)([A-Z])/', ' $1', $cardId)) . '</span>';
                        echo '</label>';
                    }
                    ?>
                </div>
                <div class="mt-4 border-t pt-4 border-gray-600">
                    <button id="showAllCards" class="neu-btn w-full mb-2">Show All</button>
                    <button id="hideAllCards" class="neu-btn w-full">Hide All</button>
                </div>
            </div>
        </aside>

        <div id="dashboardContainer" class="dashboard-container">
            <?php
            // Include all active cards
            foreach ($cardFiles as $file) {
                $cardId = basename($file, '.php');
                if ($cardId === 'CardTemplate' || $cardId === 'CardAppLog') {
                    continue; // Skip template and the duplicate log card
                }
                echo '<div class="card-wrapper" id="' . htmlspecialchars($cardId) . '-wrapper">';
                // Pass $cardId to ensure unique IDs for elements within the card if needed
                include $file;
                echo '</div>';
            }
            ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
    // Helper function to get a card's wrapper element
    function getCardWrapper(cardId) {
        return document.getElementById(cardId + '-wrapper');
    }

    // Toggles card visibility based on checkbox state
    function toggleCard(cardId, isChecked) {
        const cardWrapper = getCardWrapper(cardId);
        if (cardWrapper) {
            cardWrapper.style.display = isChecked ? 'block' : 'none';
        }
    }

    // Handles changes to card visibility checkboxes
    function handleCheckboxChange(event) {
        const checkbox = event.target;
        const cardId = checkbox.dataset.cardTarget;
        toggleCard(cardId, checkbox.checked);
    }

    // Handles clicks on card action buttons (minimize, settings, close)
    function handleCardButtonClick(event) {
        const target = event.target.closest('[data-action][data-card]');
        if (!target) return; // Not a card action button

        const action = target.dataset.action;
        const cardId = target.dataset.card;
        const cardWrapper = getCardWrapper(cardId);

        if (!cardWrapper) {
            console.warn(`Card wrapper not found for ID: ${cardId}`);
            return;
        }

        switch (action) {
            case 'minimize':
                // Implement minimize logic here, e.g., toggle a class for height/overflow
                // For now, let's just log
                console.log(`Minimize action for card: ${cardId}`);
                break;
            case 'settings':
                // Implement settings logic, e.g., open a modal
                console.log(`Settings action for card: ${cardId}`);
                break;
            case 'close':
                // Implement close logic, e.g., remove the card from DOM
                cardWrapper.remove();
                // Also uncheck the corresponding checkbox
                const checkbox = document.querySelector(`.settings-menu input[data-card-target="${cardId}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                }
                console.log(`Close action for card: ${cardId}`);
                break;
            default:
                console.warn(`Unknown action: ${action} for card: ${cardId}`);
        }
    }

    // Drag and Drop (from the provided context, assuming it's implemented elsewhere or globally)
    let activeItem = null;
    let initialX, initialY, currentX, currentY, xOffset = 0, yOffset = 0;

    function dragStart(e) {
        if (e.type === "touchstart") {
            initialX = e.touches[0].clientX - xOffset;
            initialY = e.touches[0].clientY - yOffset;
        } else {
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
        }

        const header = e.target.closest('.card-header');
        if (header && header.closest('.card-wrapper')) {
            activeItem = header.closest('.card-wrapper');
            activeItem.classList.add('dragging');
        }
    }

    function dragEnd(e) {
        if (activeItem) {
            activeItem.classList.remove('dragging');
            xOffset = currentX;
            yOffset = currentY;
        }
        activeItem = null;
    }

    function drag(e) {
        if (activeItem) {
            e.preventDefault();

            if (e.type === "touchmove") {
                currentX = e.touches[0].clientX - initialX;
                currentY = e.touches[0].clientY - initialY;
            } else {
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;
            }

            setTranslate(currentX, currentY, activeItem);
        }
    }

    function setTranslate(xPos, yPos, el) {
        el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
        el.style.left = ''; // Clear inline left/top if transform is used
        el.style.top = '';
    }

    // Event listeners for drag and drop
    const dashboardContainer = document.getElementById('dashboardContainer');
    dashboardContainer.addEventListener("touchstart", dragStart, false);
    dashboardContainer.addEventListener("touchend", dragEnd, false);
    dashboardContainer.addEventListener("touchmove", drag, false);

    dashboardContainer.addEventListener("mousedown", dragStart, false);
    dashboardContainer.addEventListener("mouseup", dragEnd, false);
    dashboardContainer.addEventListener("mousemove", drag, false);


    // CRITICAL: Add button click handler
    // Changed to delegate on dashboardContainer for better performance and scope
    document.getElementById('dashboardContainer').addEventListener('click', handleCardButtonClick);

    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.settings-menu input[type="checkbox"]');
        console.log('Found checkboxes:', checkboxes.length);

        checkboxes.forEach((checkbox, index) => {
            console.log('Setting up checkbox:', checkbox.id, 'targeting:', checkbox.dataset.cardTarget);
            // REMOVED: Redundant removeEventListener
            checkbox.addEventListener('change', handleCheckboxChange);
        });

        document.getElementById('showAllCards').addEventListener('click', function(e) {
            e.preventDefault();
            checkboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    checkbox.checked = true;
                    toggleCard(checkbox.dataset.cardTarget, true);
                }
            });
        });

        document.getElementById('hideAllCards').addEventListener('click', function(e) {
            e.preventDefault();
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    checkbox.checked = false;
                    toggleCard(checkbox.dataset.cardTarget, false);
                }
            });
        });

        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Helper function to get all card wrappers (can be useful for initial setup)
        function getAllCards() {
            return document.querySelectorAll('.card-wrapper');
        }

        // Example of initial card positioning (you might load this from storage)
        // This should be done after cards are in the DOM and feather.replace() has run
        const sampleCardWrapper = document.getElementById('SampleCard-wrapper');
        if (sampleCardWrapper) {
             sampleCardWrapper.style.left = '300px';
             sampleCardWrapper.style.top = '100px';
        }

        const cardKPIWrapper = document.getElementById('CardKPI-wrapper');
        if (cardKPIWrapper) {
            cardKPIWrapper.style.left = '650px';
            cardKPIWrapper.style.top = '50px';
        }

        const consoleLogCardWrapper = document.getElementById('ConsoleLogCard-wrapper');
        if (consoleLogCardWrapper) {
            consoleLogCardWrapper.style.left = '50px';
            consoleLogCardWrapper.style.top = '400px';
        }
        
        const cardExpandableWrapper = document.getElementById('CardExpandable-wrapper');
        if (cardExpandableWrapper) {
            cardExpandableWrapper.style.left = '450px';
            cardExpandableWrapper.style.top = '450px';
        }

        const cardChartWrapper = document.getElementById('CardChart-wrapper');
        if (cardChartWrapper) {
            cardChartWrapper.style.left = '900px';
            cardChartWrapper.style.top = '100px';
        }

        const cardLargeWrapper = document.getElementById('CardLarge-wrapper');
        if (cardLargeWrapper) {
            cardLargeWrapper.style.left = '900px';
            cardLargeWrapper.style.top = '400px';
        }

        const cardListWrapper = document.getElementById('CardList-wrapper');
        if (cardListWrapper) {
            cardListWrapper.style.left = '50px';
            cardListWrapper.style.top = '700px';
        }

        const cardDrilldownWrapper = document.getElementById('CardDrilldown-wrapper');
        if (cardDrilldownWrapper) {
            cardDrilldownWrapper.style.left = '600px';
            cardDrilldownWrapper.style.top = '700px';
        }


        console.log('Dashboard initialized with', getAllCards().length, 'cards');
    });
    </script>
</body>
</html>