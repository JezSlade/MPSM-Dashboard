// public/js/dashboard.js

let activeCard = null;
let offsetX, offsetY;
const maxIterations = 100; // For collision resolution

/**
 * Helper to get all card-wrapper elements.
 * @returns {HTMLElement[]} An array of card elements.
 */
function getAllCards() {
    return Array.from(document.querySelectorAll('.card-wrapper'));
}

/**
 * Toggles the display of a specific card and updates its settings checkbox.
 * Also attempts to resolve collisions if the card is made visible.
 * @param {string} cardId - The ID of the card to toggle.
 * @param {boolean} show - True to show the card, false to hide.
 */
function toggleCard(cardId, show) {
    const card = document.getElementById(cardId);
    const checkbox = document.getElementById(`${cardId}-toggle`);
    if (card) {
        card.style.display = show ? 'block' : 'none';
        if (checkbox) {
            checkbox.checked = show;
        }
        if (show) {
            // Only check collisions if the card is being shown
            checkAndResolveCollisions(card);
        }
    }
}

/**
 * Gets the bounding rectangle of a card including its position
 * @param {HTMLElement} card - The card element
 * @returns {Object} Rectangle with left, top, width, height properties
 */
function getCardRect(card) {
    const rect = card.getBoundingClientRect();
    return {
        left: parseInt(card.style.left) || 0,
        top: parseInt(card.style.top) || 0,
        width: rect.width,
        height: rect.height
    };
}

/**
 * Checks if two rectangles overlap
 * @param {Object} rect1 - First rectangle
 * @param {Object} rect2 - Second rectangle
 * @returns {boolean} True if rectangles overlap
 */
function isOverlapping(rect1, rect2) {
    return (
        rect1.left < rect2.left + rect2.width &&
        rect1.left + rect1.width > rect2.left &&
        rect1.top < rect2.top + rect2.height &&
        rect1.top + rect1.height > rect2.top
    );
}

/**
 * Detects and resolves overlaps between cards on the dashboard.
 * Uses a force-based approach similar to dashlogic.html
 * @param {HTMLElement} movedCard - The card that was just moved or made visible.
 * @param {number} iterations - Current iteration count (for recursion)
 */
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
            // Calculate direction to push
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
        
        // Constrain to viewport
        const clampedLeft = Math.max(0, Math.min(newLeft, window.innerWidth - movedCard.offsetWidth));
        const clampedTop = Math.max(0, Math.min(newTop, window.innerHeight - movedCard.offsetHeight));
        
        movedCard.style.left = `${clampedLeft}px`;
        movedCard.style.top = `${clampedTop}px`;
        
        // Continue resolving recursively with a small delay
        setTimeout(() => checkAndResolveCollisions(movedCard, iterations + 1), 50);
    }
}

// Rest of your existing functions (handleCardButtonClick, handleCheckboxChange) remain the same...

// === Main Initialization Logic ===
document.addEventListener('DOMContentLoaded', () => {
    // 1. Setup Drag-and-Drop with improved positioning
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

        // Constrain to viewport
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

    // Rest of your initialization code remains the same...
    
    // Initialize card positions
    const cards = getAllCards();
    cards.forEach(card => {
        if (!card.style.left && !card.style.top) {
            // Set random initial positions if none are set
            const maxX = window.innerWidth - card.offsetWidth;
            const maxY = window.innerHeight - card.offsetHeight;
            card.style.left = `${Math.floor(Math.random() * maxX * 0.7)}px`;
            card.style.top = `${Math.floor(Math.random() * maxY * 0.7)}px`;
        }
    });
});