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
                }
            }
        } catch (error) {
            console.warn('Failed to load card preferences, using defaults:', error);
        }

        // If no saved preferences, use defaults from registry
        if (Object.keys(preferences.cards).length === 0) {
            resetToDefaults();
        }
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
        return preferences.order
            .filter(cardId => preferences.cards[cardId]?.visible !== false)
            .map(cardId => CardRegistry.get(cardId))
            .filter(card => card !== undefined);
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
            <div class="card-management">
                <div class="card-management-header">
                    <h3>Dashboard Card Management</h3>
                    <div class="card-management-actions">
                        <button id="save-card-order" class="btn btn-primary">Save Changes</button>
                        <button id="reset-card-defaults" class="btn btn-secondary">Reset to Defaults</button>
                    </div>
                </div>
                <p class="help-text">Drag cards to reorder them. Toggle visibility with the eye icon.</p>
        `;

        // Group cards by category
        categories.forEach(category => {
            const categoryCards = allCards.filter(card => card.category === category);

            html += `
                <div class="card-category">
                    <h4>${category}</h4>
                    <div class="card-list sortable" data-category="${category}">
            `;

            categoryCards.forEach(card => {
                const isVisible = preferences.cards[card.id]?.visible !== false;
                const visibilityIcon = isVisible ? '👁️' : '🚫';

                html += `
                    <div class="card-config-item" data-card-id="${card.id}" draggable="true">
                        <div class="card-drag-handle">⋮⋮</div>
                        <div class="card-info">
                            <span class="card-icon">${card.icon}</span>
                            <div class="card-details">
                                <div class="card-name">${card.name}</div>
                                <div class="card-description">${card.description}</div>
                                <div class="card-meta">
                                    <span class="card-endpoint">📡 ${card.endpoint}</span>
                                    ${card.requiresParams?.length > 0 ? `<span class="card-params">⚙️ Requires: ${card.requiresParams.join(', ')}</span>` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="card-controls">
                            <button class="btn-icon btn-toggle-visibility" data-card-id="${card.id}" title="${isVisible ? 'Hide' : 'Show'} card">
                                <span>${visibilityIcon}</span>
                            </button>
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        });

        html += '</div>';

        container.innerHTML = html;

        // Add event listeners
        setupAdminEventListeners();
    }

    /**
     * Setup event listeners for admin panel
     */
    function setupAdminEventListeners() {
        // Visibility toggle
        document.querySelectorAll('.btn-toggle-visibility').forEach(btn => {
            btn.addEventListener('click', function() {
                const cardId = this.dataset.cardId;
                const currentVisibility = preferences.cards[cardId]?.visible !== false;
                setCardVisibility(cardId, !currentVisibility);

                // Update icon
                const icon = this.querySelector('span');
                icon.textContent = !currentVisibility ? '👁️' : '🚫';
                this.title = !currentVisibility ? 'Hide card' : 'Show card';
            });
        });

        // Save button
        const saveBtn = document.getElementById('save-card-order');
        if (saveBtn) {
            saveBtn.addEventListener('click', async function() {
                // Collect new order from DOM
                const newOrder = [];
                document.querySelectorAll('.card-config-item').forEach(item => {
                    newOrder.push(item.dataset.cardId);
                });

                await setCardOrder(newOrder);
                alert('Card order saved successfully!');
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
        const items = document.querySelectorAll('.card-config-item');
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

                const afterElement = getDragAfterElement(this.parentElement, e.clientY);
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
