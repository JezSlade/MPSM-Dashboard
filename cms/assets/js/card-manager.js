/**
 * Card Manager - Handles card visibility, ordering, and rendering
 * Persists user preferences to backend
 */

const CardManager = (function() {
    'use strict';

    let preferences = {
        cards: {},
        order: []
    };

    let currentParams = {};

    /**
     * Initialize card manager with user preferences
     */
    async function init() {
        try {
            // Load preferences from backend
            const response = await fetch('api/card-preferences.php');
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.preferences) {
                    preferences = data.preferences;
                    console.log('[CardManager] Loaded preferences from backend:', preferences);
                }
            }
        } catch (error) {
            console.warn('[CardManager] Failed to load card preferences, using defaults:', error);
        }

        // If no saved preferences, use defaults from registry
        if (Object.keys(preferences.cards).length === 0 || !preferences.order || preferences.order.length === 0) {
            console.log('[CardManager] No valid preferences found, resetting to defaults');
            resetToDefaults();
            // Try to save defaults (but don't fail if save fails)
            await savePreferences().catch(err => {
                console.warn('[CardManager] Could not save default preferences:', err);
            });
        }

        console.log('[CardManager] Final preferences:', preferences);
    }

    /**
     * Reset preferences to default values from card registry
     */
    function resetToDefaults() {
        preferences = {
            cards: {},
            order: []
        };

        const allCards = CardRegistry.getAll();
        allCards.forEach(card => {
            preferences.cards[card.id] = {
                visible: card.defaultVisible,
                order: card.defaultOrder
            };
        });

        // Sort by default order
        preferences.order = allCards
            .sort((a, b) => a.defaultOrder - b.defaultOrder)
            .map(card => card.id);
    }

    /**
     * Get visible cards in order
     */
    function getVisibleCards() {
        console.log('[CardManager] Getting visible cards. preferences.order:', preferences.order);
        console.log('[CardManager] preferences.cards:', preferences.cards);

        const visibleCards = preferences.order
            .filter(cardId => {
                const isVisible = preferences.cards[cardId]?.visible !== false;
                console.log(`[CardManager] Card ${cardId} visible:`, isVisible);
                return isVisible;
            })
            .map(cardId => {
                const card = CardRegistry.get(cardId);
                console.log(`[CardManager] Card ${cardId} from registry:`, card ? 'found' : 'NOT FOUND');
                return card;
            })
            .filter(card => card !== undefined);

        console.log('[CardManager] Final visible cards:', visibleCards.length);
        return visibleCards;
    }

    /**
     * Set card visibility
     */
    function setCardVisibility(cardId, visible) {
        if (!preferences.cards[cardId]) {
            preferences.cards[cardId] = { visible, order: 999 };
        } else {
            preferences.cards[cardId].visible = visible;
        }
        return savePreferences();
    }

    /**
     * Set card order
     */
    function setCardOrder(newOrder) {
        preferences.order = newOrder;
        return savePreferences();
    }

    /**
     * Save preferences to backend
     */
    async function savePreferences() {
        try {
            const response = await fetch('api/card-preferences.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ preferences })
            });

            if (!response.ok) {
                throw new Error('Failed to save preferences');
            }

            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error('Error saving card preferences:', error);
            return false;
        }
    }

    /**
     * Set current parameters for card data fetching
     */
    function setParams(params) {
        currentParams = { ...currentParams, ...params };
    }

    /**
     * Render all visible cards to the dashboard
     */
    async function renderDashboard(containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) {
            console.error('Dashboard container not found');
            return;
        }

        container.innerHTML = '<div class="loading">Loading dashboard...</div>';

        const visibleCards = getVisibleCards();

        if (visibleCards.length === 0) {
            container.innerHTML = '<div class="empty">No cards configured. Go to Admin to enable cards.</div>';
            return;
        }

        // Clear container
        container.innerHTML = '';

        // Render each card
        for (const cardDef of visibleCards) {
            const cardElement = createCardElement(cardDef);
            container.appendChild(cardElement);

            // Load card data
            try {
                await loadCardData(cardDef.id, cardElement);
            } catch (error) {
                console.error(`Error loading card ${cardDef.id}:`, error);
                const cardBody = cardElement.querySelector('.card-body');
                if (cardBody) {
                    cardBody.innerHTML = `<div class="error">Error loading data: ${error.message}</div>`;
                }
            }
        }
    }

    /**
     * Create card HTML element
     */
    function createCardElement(cardDef) {
        const card = document.createElement('div');
        card.className = `card card-${cardDef.id} card-clickable`;
        card.dataset.cardId = cardDef.id;

        card.innerHTML = `
            <div class="card-header">
                <div class="card-title">
                    <span class="card-icon">${cardDef.icon}</span>
                    <h3>${cardDef.name}</h3>
                </div>
                <div class="card-actions">
                    <button class="btn-icon btn-refresh" title="Refresh" data-action="refresh">
                        <span>🔄</span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="loading">Loading...</div>
            </div>
        `;

        // Add click listener to open modal
        card.addEventListener('click', (e) => {
            // Don't open modal if clicking on action buttons
            if (e.target.closest('[data-action]')) {
                return;
            }
            openCardModal(cardDef.id, card);
        });

        // Add event listeners
        const refreshBtn = card.querySelector('.btn-refresh');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', async (e) => {
                e.stopPropagation(); // Prevent modal from opening
                refreshBtn.classList.add('spinning');
                await loadCardData(cardDef.id, card);
                refreshBtn.classList.remove('spinning');
            });
        }

        return card;
    }

    /**
     * Load data for a specific card
     */
    async function loadCardData(cardId, cardElement) {
        const cardDef = CardRegistry.get(cardId);
        if (!cardDef) {
            throw new Error(`Card ${cardId} not found in registry`);
        }

        const cardBody = cardElement.querySelector('.card-body');
        if (!cardBody) {
            throw new Error('Card body element not found');
        }

        // Check required parameters
        const missingParams = cardDef.requiresParams?.filter(param => !currentParams[param]) || [];
        if (missingParams.length > 0) {
            cardBody.innerHTML = `<div class="warning">Missing required parameters: ${missingParams.join(', ')}</div>`;
            return;
        }

        // Fetch data
        cardBody.innerHTML = '<div class="loading">Loading data...</div>';

        try {
            const data = await cardDef.fetchData(currentParams);

            // Check if response has data property
            const responseData = data?.data !== undefined ? data.data : data;

            // Store data on card element for modal use
            cardElement.dataset.cardData = JSON.stringify(responseData);

            // Render snapshot (compact view)
            if (cardDef.renderSnapshot) {
                cardDef.renderSnapshot(responseData, cardBody);
            } else if (cardDef.render) {
                // Fallback to old render method if renderSnapshot not available
                cardDef.render(responseData, cardBody);
            }
        } catch (error) {
            console.error(`Error fetching data for card ${cardId}:`, error);
            cardBody.innerHTML = `<div class="error">Failed to load data: ${error.message}</div>`;
        }
    }

    /**
     * Open modal with full card details
     */
    function openCardModal(cardId, cardElement) {
        const cardDef = CardRegistry.get(cardId);
        if (!cardDef) {
            console.error(`Card ${cardId} not found in registry`);
            return;
        }

        // Get stored card data
        const dataStr = cardElement.dataset.cardData;
        if (!dataStr) {
            console.warn('No data available for modal');
            return;
        }

        let cardData;
        try {
            cardData = JSON.parse(dataStr);
        } catch (error) {
            console.error('Failed to parse card data:', error);
            return;
        }

        // Create modal
        const modal = document.createElement('div');
        modal.className = 'card-modal';
        modal.innerHTML = `
            <div class="card-modal-backdrop"></div>
            <div class="card-modal-content">
                <div class="card-modal-header">
                    <div class="card-modal-title">
                        <span class="card-icon">${cardDef.icon}</span>
                        <h2>${cardDef.name}</h2>
                    </div>
                    <button class="card-modal-close" title="Close">×</button>
                </div>
                <div class="card-modal-body">
                    <div class="loading">Loading details...</div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';

        // Render modal content
        const modalBody = modal.querySelector('.card-modal-body');
        if (cardDef.renderModal) {
            cardDef.renderModal(cardData, modalBody);
        } else if (cardDef.render) {
            // Fallback to old render method
            cardDef.render(cardData, modalBody);
        }

        // Close handlers
        const closeModal = () => {
            document.body.style.overflow = '';
            modal.remove();
        };

        modal.querySelector('.card-modal-close').addEventListener('click', closeModal);
        modal.querySelector('.card-modal-backdrop').addEventListener('click', closeModal);

        // ESC key to close
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                closeModal();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }

    /**
     * Refresh a specific card
     */
    async function refreshCard(cardId) {
        const cardElement = document.querySelector(`[data-card-id="${cardId}"]`);
        if (cardElement) {
            await loadCardData(cardId, cardElement);
        }
    }

    /**
     * Refresh all visible cards
     */
    async function refreshAll() {
        const visibleCards = getVisibleCards();
        for (const cardDef of visibleCards) {
            await refreshCard(cardDef.id);
        }
    }

    /**
     * Render card management UI in admin panel
     */
    function renderAdminPanel(containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) {
            console.error('Admin container not found');
            return;
        }

        const allCards = CardRegistry.getAll();
        const categories = CardRegistry.getCategories();

        let html = `
            <div class="card-management-compact">
                <div class="card-mgmt-actions">
                    <button id="save-card-order" class="btn btn-primary btn-sm">Save</button>
                    <button id="reset-card-defaults" class="btn btn-secondary btn-sm">Reset</button>
                </div>
                <div class="card-mgmt-grid">
        `;

        // Render all cards as compact tiles
        allCards.sort((a, b) => {
            const orderA = preferences.cards[a.id]?.order ?? a.defaultOrder ?? 999;
            const orderB = preferences.cards[b.id]?.order ?? b.defaultOrder ?? 999;
            return orderA - orderB;
        }).forEach(card => {
            const isVisible = preferences.cards[card.id]?.visible !== false;
            const visibilityClass = isVisible ? 'visible' : 'hidden';

            html += `
                <div class="card-mgmt-tile ${visibilityClass}" data-card-id="${card.id}" draggable="true">
                    <div class="tile-drag">≡</div>
                    <div class="tile-icon">${card.icon}</div>
                    <div class="tile-content">
                        <div class="tile-name">${card.name}</div>
                        <div class="tile-category">${card.category}</div>
                    </div>
                    <button class="tile-toggle" data-card-id="${card.id}" title="${isVisible ? 'Hide' : 'Show'}">
                        ${isVisible ? 'Hide' : 'Show'}
                    </button>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;

        container.innerHTML = html;

        // Add event listeners
        setupAdminEventListeners();
    }

    /**
     * Setup event listeners for admin panel
     */
    function setupAdminEventListeners() {
        // Visibility toggle
        document.querySelectorAll('.tile-toggle').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const cardId = this.dataset.cardId;
                const currentVisibility = preferences.cards[cardId]?.visible !== false;
                setCardVisibility(cardId, !currentVisibility);

                // Update icon and tile class
                this.textContent = !currentVisibility ? '👁️' : '🚫';
                this.title = !currentVisibility ? 'Hide' : 'Show';
                const tile = this.closest('.card-mgmt-tile');
                tile.classList.toggle('visible', !currentVisibility);
                tile.classList.toggle('hidden', currentVisibility);
            });
        });

        // Save button
        const saveBtn = document.getElementById('save-card-order');
        if (saveBtn) {
            saveBtn.addEventListener('click', async function() {
                // Collect new order from DOM
                const newOrder = [];
                document.querySelectorAll('.card-mgmt-tile').forEach(item => {
                    newOrder.push(item.dataset.cardId);
                });

                await setCardOrder(newOrder);
                alert('✅ Card order saved!');
            });
        }

        // Reset button
        const resetBtn = document.getElementById('reset-card-defaults');
        if (resetBtn) {
            resetBtn.addEventListener('click', async function() {
                if (confirm('Reset all card settings to defaults?')) {
                    resetToDefaults();
                    await savePreferences();
                    renderAdminPanel('.card-management-container');
                    alert('Card settings reset to defaults!');
                }
            });
        }

        // Drag and drop
        setupDragAndDrop();
    }

    /**
     * Setup drag and drop for card reordering
     */
    function setupDragAndDrop() {
        const items = document.querySelectorAll('.card-mgmt-tile');
        let draggedElement = null;

        items.forEach(item => {
            item.addEventListener('dragstart', function(e) {
                draggedElement = this;
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });

            item.addEventListener('dragend', function() {
                this.classList.remove('dragging');
            });

            item.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                const afterElement = getDragAfterElement(this.parentElement, e.clientX, e.clientY);
                if (afterElement == null) {
                    this.parentElement.appendChild(draggedElement);
                } else {
                    this.parentElement.insertBefore(draggedElement, afterElement);
                }
            });
        });
    }

    /**
     * Get element that should be after dragged element
     */
    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.card-config-item:not(.dragging)')];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    // Public API
    return {
        init,
        getVisibleCards,
        setCardVisibility,
        setCardOrder,
        setParams,
        renderDashboard,
        renderAdminPanel,
        refreshCard,
        refreshAll,
        resetToDefaults,
        getPreferences: () => preferences
    };
})();

// Make globally available
window.CardManager = CardManager;
