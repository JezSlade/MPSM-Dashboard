/**
 * WIDGET DASHBOARD APPLICATION
 * * This file contains the service layer, which implements the business logic.
 * It coordinates between the repositories and the main application logic.
 */

import { Widget, WidgetArea } from './entities.js';

/**
 * Widget service providing business logic for widget operations
 * Implements business rules and coordinates between repositories
 * * @class WidgetService
 */
export class WidgetService {
    constructor(widgetRepository, areaRepository) {
        this.widgetRepository = widgetRepository;
        this.areaRepository = areaRepository;
        this.eventHandlers = new Map();
    }

    /**
     * Gets all widgets with sorting and filtering
     * @param {Object} options - Query options
     * @returns {Array<Widget>} Filtered and sorted widgets
     */
    getWidgets(options = {}) {
        let widgets = this.widgetRepository.findAll();

        // Apply filters
        if (options.category) {
            widgets = widgets.filter(w => w.category === options.category);
        }
        if (options.status) {
            widgets = widgets.filter(w => w.status === options.status);
        }
        if (options.activeOnly) {
            widgets = widgets.filter(w => w.isActive());
        }

        // Apply sorting
        if (options.sortBy) {
            widgets.sort((a, b) => {
                const aVal = a[options.sortBy];
                const bVal = b[options.sortBy];
                if (options.sortOrder === 'desc') {
                    return bVal > aVal ? 1 : -1;
                }
                return aVal > bVal ? 1 : -1;
            });
        } else {
            // Default sort by priority and updated date
            widgets.sort((a, b) => {
                if (a.priority !== b.priority) {
                    return b.priority - a.priority;
                }
                return new Date(b.updatedAt) - new Date(a.updatedAt);
            });
        }
        return widgets;
    }

    /**
     * Gets widgets for specific area
     * @param {string} areaId - Area ID
     * @returns {Array<Widget>} Widgets in area
     */
    getWidgetsForArea(areaId) {
        const area = this.areaRepository.findById(areaId);
        if (!area) return [];
        return area.widgetIds
            .map(id => this.widgetRepository.findById(id))
            .filter(Boolean)
            .filter(widget => widget.isActive());
    }

    /**
     * Gets widget statistics
     * @returns {Object} Widget statistics
     */
    getStatistics() {
        const widgets = this.widgetRepository.findAll();
        const areas = this.areaRepository.findAll();
        return {
            totalWidgets: widgets.length,
            activeWidgets: widgets.filter(w => w.isActive()).length,
            draftWidgets: widgets.filter(w => w.isDraft()).length,
            inactiveWidgets: widgets.filter(w => w.status === 'inactive').length,
            totalAreas: areas.length,
            activeAreas: areas.filter(a => a.isActive).length,
            categories: this.widgetRepository.getCategories().length,
            averageWidgetsPerArea: areas.length > 0 ? Math.round(widgets.length / areas.length) : 0
        };
    }

    /**
     * Validates widget placement in area
     * @param {string} widgetId - Widget ID
     * @param {string} areaId - Area ID
     * @returns {Object} Validation result
     */
    validateWidgetPlacement(widgetId, areaId) {
        const widget = this.widgetRepository.findById(widgetId);
        const area = this.areaRepository.findById(areaId);

        if (!widget) {
            return { valid: false, reason: 'Widget not found' };
        }
        if (!area) {
            return { valid: false, reason: 'Area not found' };
        }
        if (!widget.isActive()) {
            return { valid: false, reason: 'Widget is not active' };
        }
        if (!area.canAcceptWidgets()) {
            return { valid: false, reason: 'Area cannot accept more widgets' };
        }
        if (area.widgetIds.includes(widgetId)) {
            return { valid: false, reason: 'Widget already in area' };
        }
        return { valid: true };
    }

    /**
     * Adds event handler
     * @param {string} event - Event name
     * @param {Function} handler - Event handler
     */
    addEventListener(event, handler) {
        if (!this.eventHandlers.has(event)) {
            this.eventHandlers.set(event, new Set());
        }
        this.eventHandlers.get(event).add(handler);
    }

    /**
     * Removes event handler
     * @param {string} event - Event name
     * @param {Function} handler - Event handler
     */
    removeEventListener(event, handler) {
        if (this.eventHandlers.has(event)) {
            this.eventHandlers.get(event).delete(handler);
        }
    }

    /**
     * Emits event to handlers
     * @param {string} event - Event name
     * @param {*} data - Event data
     * @protected
     */
    emit(event, data) {
        if (this.eventHandlers.has(event)) {
            this.eventHandlers.get(event).forEach(handler => {
                try {
                    handler(data);
                } catch (error) {
                    console.error(`Error in ${event} handler:`, error);
                }
            });
        }
    }
}

/**
 * Widget area service for area management
 * Handles widget area business logic and operations
 * * @class WidgetAreaService
 */
export class WidgetAreaService {
    constructor(areaRepository, widgetRepository) {
        this.areaRepository = areaRepository;
        this.widgetRepository = widgetRepository;
    }

    /**
     * Creates new widget area
     * @param {Object} areaData - Area data
     * @returns {WidgetArea} Created area
     */
    createArea(areaData) {
        const area = new WidgetArea(areaData);
        return this.areaRepository.create(area);
    }

    /**
     * Gets all active areas
     * @returns {Array<WidgetArea>} Active areas
     */
    getActiveAreas() {
        return this.areaRepository.findActive();
    }

    /**
     * Assigns widget to area
     * @param {string} widgetId - Widget ID
     * @param {string} areaId - Area ID
     * @param {number} position - Position in area
     * @returns {boolean} Success status
     */
    assignWidgetToArea(widgetId, areaId, position = null) {
        const area = this.areaRepository.findById(areaId);
        const widget = this.widgetRepository.findById(widgetId);
        if (!area || !widget) return false;

        if (area.addWidget(widgetId, position)) {
            this.areaRepository.update(areaId, area);
            return true;
        }
        return false;
    }

    /**
     * Removes widget from area
     * @param {string} widgetId - Widget ID
     * @param {string} areaId - Area ID
     * @returns {boolean} Success status
     */
    removeWidgetFromArea(widgetId, areaId) {
        const area = this.areaRepository.findById(areaId);
        if (!area) return false;

        if (area.removeWidget(widgetId)) {
            this.areaRepository.update(areaId, area);
            return true;
        }
        return false;
    }

    /**
     * Reorders widgets in area
     * @param {string} areaId - Area ID
     * @param {Array<string>} newOrder - New widget order
     * @returns {boolean} Success status
     */
    reorderWidgets(areaId, newOrder) {
        const area = this.areaRepository.findById(areaId);
        if (!area) return false;
        area.widgetIds = newOrder;
        this.areaRepository.update(areaId, area);
        return true;
    }

    /**
     * Gets area layout configuration
     * @param {string} areaId - Area ID
     * @returns {Object} Layout configuration
     */
    getAreaLayout(areaId) {
        const area = this.areaRepository.findById(areaId);
        return area ? area.layoutSettings : null;
    }

    /**
     * Updates area layout
     * @param {string} areaId - Area ID
     * @param {Object} layoutSettings - New layout settings
     * @returns {boolean} Success status
     */
    updateAreaLayout(areaId, layoutSettings) {
        const area = this.areaRepository.findById(areaId);
        if (!area) return false;
        area.layoutSettings = { ...area.layoutSettings, ...layoutSettings };
        this.areaRepository.update(areaId, area);
        return true;
    }
}