// public/js/dashboard.js

// Variables for drag-and-drop
let activeCard = null;
let offsetX, offsetY;

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
 * Detects and resolves overlaps between cards on the dashboard.
 * Simple nudging algorithm.
 * @param {HTMLElement} movedCard - The card that was just moved or made visible.
 */
function checkAndResolveCollisions(movedCard) {
    // Filter to include only visible cards for collision checks
    const cards = getAllCards().filter(card => card.style.display !== 'none');
    const movedRect = movedCard.getBoundingClientRect();
    const gridGap = 12; // Matches --grid-gap from styles.css

    cards.forEach(otherCard => {
        // Skip self and hidden cards
        if (otherCard === movedCard || otherCard.style.display === 'none') return;

        const otherRect = otherCard.getBoundingClientRect();

        // Check for overlap using bounding rectangles
        const isOverlapping = !(movedRect.right < otherRect.left ||
                                movedRect.left > otherRect.right ||
                                movedRect.bottom < otherRect.top ||
                                movedRect.top > otherRect.bottom);

        if (isOverlapping) {
            // Calculate overlap dimensions
            const overlapX = Math.max(0, Math.min(movedRect.right, otherRect.right) - Math.max(movedRect.left, otherRect.left));
            const overlapY = Math.max(0, Math.min(movedRect.bottom, otherRect.bottom) - Math.max(movedRect.top, otherRect.top));

            // Determine direction to nudge based on minimal overlap axis
            if (overlapX < overlapY) { // Resolve horizontally
                const currentOtherLeft = parseFloat(otherCard.style.left) || 0;
                if (movedRect.left < otherRect.left) {
                    // Nudge otherCard to the right
                    otherCard.style.left = `${currentOtherLeft + overlapX + gridGap}px`;
                } else {
                    // Nudge otherCard to the left
                    otherCard.style.left = `${currentOtherLeft - (overlapX + gridGap)}px`;
                }
            } else { // Resolve vertically
                const currentOtherTop = parseFloat(otherCard.style.top) || 0;
                if (movedRect.top < otherRect.top) {
                    // Nudge otherCard downwards
                    otherCard.style.top = `${currentOtherTop + overlapY + gridGap}px`;
                } else {
                    // Nudge otherCard upwards
                    otherCard.style.top = `${currentOtherTop - (overlapY + gridGap)}px`;
                }
            }
        }
    });
}

/**
 * Handles clicks on card header buttons (minimize, close, settings).
 * @param {Event} e - The click event object.
 */
function handleCardButtonClick(e) {
    const button = e.target.closest('.neu-btn');
    if (!button) return; // Not a button related to cards

    const action = button.dataset.action;
    const cardId = button.dataset.card; // Card ID is usually stored in data-card attribute
    const card = document.getElementById(cardId);

    if (!card) return;

    switch (action) {
        case 'minimize':
            const content = card.querySelector('.neumorphic.p-4:not(.expandable-content)'); // Target the main content area
            if (content) {
                const isHidden = content.style.display === 'none';
                content.style.display = isHidden ? 'block' : 'none';
                const icon = button.querySelector('i');
                if (icon) {
                    icon.setAttribute('data-feather', isHidden ? 'chevron-down' : 'chevron-up');
                    feather.replace(); // Re-render icon to show correct state
                }
            }
            break;
        case 'close':
            card.style.display = 'none';
            // Also uncheck the corresponding checkbox in settings menu
            const checkbox = document.getElementById(`${cardId}-toggle`);
            if (checkbox) {
                checkbox.checked = false;
            }
            break;
        case 'settings':
            // Show the global settings modal
            const settingsModal = document.getElementById('cardSettingsModal');
            if (settingsModal) {
                settingsModal.classList.remove('hidden');
                settingsModal.classList.add('flex'); // Assumes flex display for modal
                // Optional: Scroll to the corresponding card's checkbox in the modal
                const currentCardToggle = document.getElementById(`${cardId}-toggle`);
                if (currentCardToggle) {
                    const label = currentCardToggle.closest('label'); // Assuming checkbox is inside a label
                    if (label) {
                        label.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }
            break;
    }
}

/**
 * Handles changes on checkboxes in the settings menu to show/hide cards.
 * @param {Event} e - The change event object.
 */
function handleCheckboxChange(e) {
    if (e.target.type === 'checkbox') {
        const cardId = e.target.dataset.cardTarget; // Get card ID from data-card-target
        toggleCard(cardId, e.target.checked);
    }
}

// === Main Initialization Logic ===
document.addEventListener('DOMContentLoaded', () => {
    // 1. Setup Drag-and-Drop
    document.addEventListener('mousedown', (e) => {
        // Only start drag if clicking directly on a card header and not on a button within it
        const cardHeader = e.target.closest('.card-header');
        if (cardHeader && !e.target.closest('.neu-btn')) {
            activeCard = cardHeader.closest('.card-wrapper');
            if (activeCard) {
                activeCard.classList.add('dragging');
                offsetX = e.clientX - activeCard.getBoundingClientRect().left;
                offsetY = e.clientY - activeCard.getBoundingClientRect().top;
                activeCard.style.zIndex = 1000; // Bring to front
            }
        }
    });

    document.addEventListener('mousemove', (e) => {
        if (!activeCard) return;

        e.preventDefault(); // Prevent text selection during drag

        let newX = e.clientX - offsetX;
        let newY = e.clientY - offsetY;

        // Constrain card movement within the viewport
        newX = Math.max(0, Math.min(newX, window.innerWidth - activeCard.offsetWidth));
        newY = Math.max(0, Math.min(newY, window.innerHeight - activeCard.offsetHeight));

        activeCard.style.left = `${newX}px`;
        activeCard.style.top = `${newY}px`;
    });

    document.addEventListener('mouseup', () => {
        if (activeCard) {
            activeCard.classList.remove('dragging');
            checkAndResolveCollisions(activeCard); // Check collisions after dropping
            activeCard.style.zIndex = 1; // Reset z-index
            activeCard = null;
        }
    });

    // 2. Setup Card Button Click Handler (Minimize, Close, Settings)
    document.addEventListener('click', handleCardButtonClick);

    // 3. Setup Settings Menu Checkboxes
    const settingsCheckboxes = document.querySelectorAll('.settings-menu input[type="checkbox"]');
    settingsCheckboxes.forEach(checkbox => {
        // Ensure event listener is added only once to prevent duplicates on hot reloads/multiple DOMContentLoaded
        checkbox.removeEventListener('change', handleCheckboxChange);
        checkbox.addEventListener('change', handleCheckboxChange);
    });

    // 4. Setup "Show All Cards" and "Hide All Cards" buttons
    const showAllCardsBtn = document.getElementById('showAllCards');
    const hideAllCardsBtn = document.getElementById('hideAllCards');

    if (showAllCardsBtn) {
        showAllCardsBtn.removeEventListener('click', (e) => { e.preventDefault(); }); // Prevent duplicates
        showAllCardsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            settingsCheckboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    checkbox.checked = true;
                    toggleCard(checkbox.dataset.cardTarget, true);
                }
            });
        });
    }

    if (hideAllCardsBtn) {
        hideAllCardsBtn.removeEventListener('click', (e) => { e.preventDefault(); }); // Prevent duplicates
        hideAllCardsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            settingsCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    checkbox.checked = false;
                    toggleCard(checkbox.dataset.cardTarget, false);
                }
            });
        });
    }

    // 5. Initialize Feather Icons
    // Ensure feather-icons library is loaded before calling replace()
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // 6. Setup Settings Modal close logic
    const settingsModal = document.getElementById('cardSettingsModal');
    if (settingsModal) {
        // Close button inside modal
        const closeModalBtn = settingsModal.querySelector('[data-action="close-modal"]');
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => {
                settingsModal.classList.add('hidden');
                settingsModal.classList.remove('flex');
            });
        }
        // Close when clicking outside the modal content
        settingsModal.addEventListener('click', (e) => {
            if (e.target === settingsModal) { // Only if direct click on overlay
                settingsModal.classList.add('hidden');
                settingsModal.classList.remove('flex');
            }
        });
    }
    console.log('Dashboard core JavaScript initialized.');
});