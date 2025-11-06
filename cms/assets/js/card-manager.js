/**
 * CardManager - orchestrates dashboard cards from CardRegistry.
 */

const CardManager = (function () {
    'use strict';

    const CACHE_TTL_MS = 5 * 60 * 1000; // 5 minutes

    const state = {
        container: null,
        cards: [],
        enabled: [],
        context: null,
        cardSnapshots: {},
        cardDataCache: {}, // { cardId: { data: snapshot, timestamp: Date.now(), context: contextHash } }
        preferencesProvider: null,
        onCardData: null
    };

    function init(options) {
        state.container = document.querySelector(options.container);
        if (!state.container) {
            throw new Error('Card container element not found');
        }

        state.preferencesProvider = options.preferencesProvider || (() => ({}));
        state.onCardData = options.onCardData || (() => {});

        state.cards = CardRegistry.getAll();
        const prefs = state.preferencesProvider();
        const enabledFromPrefs = Array.isArray(prefs.cards) ? prefs.cards : null;

        state.enabled = enabledFromPrefs && enabledFromPrefs.length
            ? enabledFromPrefs
            : state.cards.filter(card => card.defaultVisible).map(card => card.id);

        renderSkeleton();
    }

    function setContext(context) {
        state.context = Object.assign({}, context);
    }

    function getContextHash(context) {
        // Create a simple hash of the context to detect changes
        return JSON.stringify(context || {});
    }

    function getCachedData(cardId) {
        const cached = state.cardDataCache[cardId];
        if (!cached) return null;

        const age = Date.now() - cached.timestamp;
        const contextHash = getContextHash(state.context);

        // Invalidate if TTL expired or context changed
        if (age > CACHE_TTL_MS || cached.context !== contextHash) {
            delete state.cardDataCache[cardId];
            return null;
        }

        return cached.data;
    }

    function setCachedData(cardId, snapshot) {
        state.cardDataCache[cardId] = {
            data: snapshot,
            timestamp: Date.now(),
            context: getContextHash(state.context)
        };
    }

    function clearCache(cardId = null) {
        if (cardId) {
            delete state.cardDataCache[cardId];
        } else {
            state.cardDataCache = {};
        }
    }

    function renderSkeleton() {
        state.container.innerHTML = '';
        state.enabled.forEach(cardId => {
            const definition = state.cards.find(card => card.id === cardId);
            if (!definition) return;
            const cardEl = document.createElement('article');
            cardEl.className = 'dashboard-card';
            cardEl.dataset.cardId = definition.id;
            cardEl.innerHTML = `
                <header class="card-header">
                    <div class="card-icon"><i class="${definition.icon}"></i></div>
                    <div class="card-title">${definition.title}</div>
                </header>
                <div class="card-body">
                    <div class="card-loading"><i class="fas fa-spinner fa-spin"></i></div>
                </div>
                <footer class="card-footer">${definition.description || ''}</footer>
            `;

            cardEl.addEventListener('click', () => openModal(definition.id));
            state.container.appendChild(cardEl);
        });
    }

    async function refreshAll(force = false) {
        if (!state.context) {
            throw new Error('CardManager context not set');
        }

        if (force) {
            clearCache();
        }

        await Promise.all(state.enabled.map(id => refreshCard(id, force)));
    }

    async function refreshCard(cardId, force = false) {
        const definition = state.cards.find(card => card.id === cardId);
        if (!definition) return;

        const cardEl = state.container.querySelector(`.dashboard-card[data-card-id="${cardId}"]`);
        if (!cardEl) return;

        const body = cardEl.querySelector('.card-body');

        // Check cache first (unless forced refresh)
        if (!force) {
            const cachedSnapshot = getCachedData(cardId);
            if (cachedSnapshot) {
                state.cardSnapshots[cardId] = cachedSnapshot;
                renderSnapshot(cardEl, definition, cachedSnapshot);
                state.onCardData(cardId, cachedSnapshot);
                return; // Use cached data, skip API call
            }
        }

        // No cache or forced refresh - show loading and fetch
        body.innerHTML = '<div class="card-loading"><i class="fas fa-spinner fa-spin"></i></div>';

        try {
            const snapshot = await definition.load(createHelpers(), state.context);
            state.cardSnapshots[cardId] = snapshot;
            setCachedData(cardId, snapshot); // Cache the result
            renderSnapshot(cardEl, definition, snapshot);
            state.onCardData(cardId, snapshot);
        } catch (error) {
            console.error(`Failed to load card ${cardId}`, error);
            body.innerHTML = `
                <div class="card-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${error.message}</p>
                </div>
            `;
        }
    }

    function renderSnapshot(cardEl, definition, snapshot) {
        const body = cardEl.querySelector('.card-body');

        if (definition.renderSnapshot) {
            definition.renderSnapshot(createHelpers(), state.context, snapshot, body);
            return;
        }

        const headline = snapshot.headline || { value: '--', label: '' };
        const metrics = Array.isArray(snapshot.metrics) ? snapshot.metrics : [];

        body.innerHTML = `
            <div class="card-headline">
                <div class="headline-value">${formatNumber(headline.value)}</div>
                <div class="headline-label">${headline.label || ''}</div>
            </div>
            <div class="card-metrics">
                ${metrics.map(metric => `
                    <div class="metric-badge metric-${metric.tone || 'neutral'}">
                        <span class="metric-value">${formatNumber(metric.value)}</span>
                        <span class="metric-label">${metric.label}</span>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function openModal(cardId) {
        const definition = state.cards.find(card => card.id === cardId);
        if (!definition) return;

        const snapshot = state.cardSnapshots[cardId];
        if (!snapshot) {
            showModalMessage('No data', 'This card has not loaded data yet.');
            return;
        }

        if (typeof definition.renderModal === 'function') {
            definition.renderModal(createHelpers(), state.context, snapshot);
        } else {
            showModalMessage('No Details Available', 'This card does not expose additional drill-down information yet.');
        }
    }

    function showModalMessage(title, message) {
        const modalBody = createHelpers().createModal(title);
        modalBody.innerHTML = `<div class="empty-state"><i class="fas fa-info-circle"></i><p>${message}</p></div>`;
    }

    function createHelpers() {
        return {
            fetchJson,
            renderTable: (container, options) => TableUtils.renderTable(container, options),
            createModal,
            escape: escapeHtml,
            formatNumber
        };
    }

    async function fetchJson(path, params = {}) {
        const url = new URL(path, window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/'));
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                url.searchParams.append(key, value);
            }
        });

        const response = await fetch(url.toString());
        if (!response.ok) {
            throw new Error(`Request failed (${response.status})`);
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || 'Request failed');
        }

        return data;
    }

    function createModal(title) {
        const modal = document.getElementById('device-modal');
        const modalTitle = document.getElementById('modal-device-name');
        const modalBody = document.getElementById('modal-device-body');

        modalTitle.textContent = title;
        modalBody.innerHTML = '';
        modal.classList.add('active');
        return modalBody;
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatNumber(value) {
        if (value === null || value === undefined || value === '') {
            return '0';
        }

        const num = Number(value);
        if (Number.isNaN(num)) {
            return escapeHtml(value);
        }

        return num.toLocaleString();
    }

    function getAvailableCards() {
        return state.cards.slice();
    }

    function getEnabledCards() {
        return state.enabled.slice();
    }

    function setEnabledCards(enabledIds) {
        state.enabled = enabledIds.slice();
        renderSkeleton();
    }

    return {
        init,
        setContext,
        refreshAll,
        refreshCard,
        clearCache,
        getAvailableCards,
        getEnabledCards,
        setEnabledCards
    };
})();

