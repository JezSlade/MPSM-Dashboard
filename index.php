<?php
// index.php — Updated with Center Cards functionality
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
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
        }

        main {
            position: relative;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }

        .dashboard-container {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, #4b5563 1px, transparent 1px);
            background-size: 20px 20px;
            overflow: hidden;
        }

        .card-wrapper {
            position: absolute;
            min-width: var(--card-width, 280px);
            max-width: 500px;
            z-index: 1;
            transition: left 0.5s ease, top 0.5s ease;
            touch-action: none;
            cursor: default;
        }

        .card-wrapper.dragging {
            opacity: 0.9;
            transform: scale(1.02);
            z-index: 1000;
            box-shadow: 0 12px 32px rgba(0,0,0,0.25), 0 0 12px rgba(0,240,255,0.3);
            transition: none;
        }

        .card-header {
            cursor: grab;
            user-select: none;
        }

        .settings-menu {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 50;
            padding: 15px;
            background: #2D3748;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

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
    </style>
</head>
<body>
    <?php
    $cardFiles = glob(__DIR__ . '/cards/Card*.php');
    ?>

    <main>
        <aside class="settings-menu">
            <div class="neumorphic p-4 rounded-lg shadow-xl">
                <h2 class="text-lg font-semibold mb-3">Card Settings</h2>
                <div class="space-y-2" id="cardVisibilityCheckboxes">
                    <?php
                    foreach ($cardFiles as $file) {
                        $cardId = basename($file, '.php');
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
                    <button id="hideAllCards" class="neu-btn w-full mb-2">Hide All</button>
                    <button id="centerCards" class="neu-btn w-full">Center Cards</button>
                </div>
            </div>
        </aside>

        <div id="dashboardContainer" class="dashboard-container">
            <?php
            foreach ($cardFiles as $file) {
                $cardId = basename($file, '.php');
                if ($cardId === 'CardTemplate' || $cardId === 'CardAppLog') {
                    continue;
                }
                echo '<div class="card-wrapper" id="' . htmlspecialchars($cardId) . '">';
                include $file;
                echo '</div>';
            }
            ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
    // Dashboard functionality
    let activeCard = null;
    let offsetX, offsetY;
    const maxIterations = 100;

    function getAllCards() {
        return Array.from(document.querySelectorAll('.card-wrapper'));
    }

    function getCardRect(card) {
        const rect = card.getBoundingClientRect();
        return {
            left: parseInt(card.style.left) || 0,
            top: parseInt(card.style.top) || 0,
            width: rect.width,
            height: rect.height
        };
    }

    function isOverlapping(rect1, rect2) {
        return (
            rect1.left < rect2.left + rect2.width &&
            rect1.left + rect1.width > rect2.left &&
            rect1.top < rect2.top + rect2.height &&
            rect1.top + rect1.height > rect2.top
        );
    }

    function checkAndResolveCollisions(movedCard, iterations = 0) {
        if (iterations >= maxIterations) {
            console.warn('Max iterations reached in collision resolution');
            return;
        }

        let hasOverlaps = false;
        let dx = 0;
        let dy = 0;

        const movedRect = getCardRect(movedCard);
        const cards = getAllCards().filter(card => card !== movedCard && card.style.display !== 'none');

        cards.forEach(otherCard => {
            const otherRect = getCardRect(otherCard);
            if (isOverlapping(movedRect, otherRect)) {
                hasOverlaps = true;
                const cx1 = movedRect.left + movedRect.width / 2;
                const cx2 = otherRect.left + otherRect.width / 2;
                const cy1 = movedRect.top + movedRect.height / 2;
                const cy2 = otherRect.top + otherRect.height / 2;
                const angle = Math.atan2(cy2 - cy1, cx2 - cx1);
                dx += -Math.cos(angle) * 5;
                dy += -Math.sin(angle) * 5;
            }
        });

        if (hasOverlaps) {
            const newLeft = parseInt(movedCard.style.left || '0') + dx;
            const newTop = parseInt(movedCard.style.top || '0') + dy;
            
            const clampedLeft = Math.max(0, Math.min(newLeft, window.innerWidth - movedCard.offsetWidth));
            const clampedTop = Math.max(0, Math.min(newTop, window.innerHeight - movedCard.offsetHeight));
            
            movedCard.style.left = `${clampedLeft}px`;
            movedCard.style.top = `${clampedTop}px`;
            
            setTimeout(() => checkAndResolveCollisions(movedCard, iterations + 1), 50);
        }
    }

    function toggleCard(cardId, show) {
        const card = document.getElementById(cardId);
        const checkbox = document.querySelector(`input[data-card-target="${cardId}"]`);
        if (card) {
            card.style.display = show ? 'block' : 'none';
            if (checkbox) {
                checkbox.checked = show;
            }
            if (show) {
                checkAndResolveCollisions(card);
            }
        }
    }

    function centerAllCards() {
        const cards = getAllCards().filter(card => card.style.display !== 'none');
        if (cards.length === 0) return;

        const container = document.getElementById('dashboardContainer');
        const containerWidth = container.clientWidth;
        const containerHeight = container.clientHeight;
        
        // Get the dimensions of the first visible card (assuming all cards are same size)
        const cardWidth = cards[0].offsetWidth;
        const cardHeight = cards[0].offsetHeight;
        const gap = 20; // pixels between cards
        
        // Calculate how many cards fit per row
        const maxCardsPerRow = Math.max(1, Math.floor(containerWidth / (cardWidth + gap)));
        
        // Calculate total grid width and starting position
        const gridWidth = Math.min(maxCardsPerRow, cards.length) * (cardWidth + gap) - gap;
        const gridHeight = Math.ceil(cards.length / maxCardsPerRow) * (cardHeight + gap) - gap;
        
        // Center the grid in the container
        const startX = Math.max(0, (containerWidth - gridWidth) / 2);
        const startY = Math.max(0, (containerHeight - gridHeight) / 2);
        
        // Position each card
        cards.forEach((card, index) => {
            const row = Math.floor(index / maxCardsPerRow);
            const col = index % maxCardsPerRow;
            
            const x = startX + col * (cardWidth + gap);
            const y = startY + row * (cardHeight + gap);
            
            card.style.left = `${x}px`;
            card.style.top = `${y}px`;
            card.style.transition = 'left 0.5s ease, top 0.5s ease';
            
            // Remove transition after positioning is done
            setTimeout(() => {
                card.style.transition = '';
            }, 500);
        });
    }

    // Initialize drag and drop
    document.addEventListener('mousedown', (e) => {
        const cardHeader = e.target.closest('.card-header');
        if (cardHeader && !e.target.closest('.neu-btn')) {
            activeCard = cardHeader.closest('.card-wrapper');
            if (activeCard) {
                activeCard.classList.add('dragging');
                const rect = activeCard.getBoundingClientRect();
                offsetX = e.clientX - rect.left;
                offsetY = e.clientY - rect.top;
                activeCard.style.zIndex = 1000;
            }
        }
    });

    document.addEventListener('mousemove', (e) => {
        if (!activeCard) return;
        e.preventDefault();

        let newX = e.clientX - offsetX;
        let newY = e.clientY - offsetY;

        newX = Math.max(0, Math.min(newX, window.innerWidth - activeCard.offsetWidth));
        newY = Math.max(0, Math.min(newY, window.innerHeight - activeCard.offsetHeight));

        activeCard.style.left = `${newX}px`;
        activeCard.style.top = `${newY}px`;
    });

    document.addEventListener('mouseup', () => {
        if (activeCard) {
            activeCard.classList.remove('dragging');
            checkAndResolveCollisions(activeCard);
            activeCard.style.zIndex = 1;
            activeCard = null;
        }
    });

    // Initialize card visibility toggles
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Set up checkbox handlers
        document.querySelectorAll('.settings-menu input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                toggleCard(e.target.dataset.cardTarget, e.target.checked);
            });
        });

        // Show all/hide all buttons
        document.getElementById('showAllCards').addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.settings-menu input[type="checkbox"]').forEach(checkbox => {
                if (!checkbox.checked) {
                    checkbox.checked = true;
                    toggleCard(checkbox.dataset.cardTarget, true);
                }
            });
        });

        document.getElementById('hideAllCards').addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.settings-menu input[type="checkbox"]').forEach(checkbox => {
                if (checkbox.checked) {
                    checkbox.checked = false;
                    toggleCard(checkbox.dataset.cardTarget, false);
                }
            });
        });

        // Center cards button
        document.getElementById('centerCards').addEventListener('click', (e) => {
            e.preventDefault();
            centerAllCards();
        });

        // Set initial positions for cards that don't have them
        const cards = getAllCards();
        cards.forEach(card => {
            if (!card.style.left && !card.style.top) {
                const maxX = window.innerWidth - card.offsetWidth;
                const maxY = window.innerHeight - card.offsetHeight;
                card.style.left = `${Math.floor(Math.random() * maxX * 0.7)}px`;
                card.style.top = `${Math.floor(Math.random() * maxY * 0.7)}px`;
            }
        });

        console.log('Dashboard initialized with', cards.length, 'cards');
    });
    </script>
</body>
</html>