/**
 * WIDGET DASHBOARD APPLICATION
 * * This file contains the data entities for the dashboard system.
 * It defines the core data models for widgets and widget areas.
 */

// ===== CORE INTERFACES AND ABSTRACT CLASSES =====

/**
 * Abstract base class for all entities
 * Provides common functionality and enforces consistent structure
 * * @abstract
 * @class BaseEntity
 */
export class BaseEntity {
    constructor(data = {}) {
        this.id = data.id || this.generateId();
        this.createdAt = data.createdAt || new Date().toISOString();
        this.updatedAt = data.updatedAt || new Date().toISOString();
        this.metadata = data.metadata || {};
    }

    /**
     * Generates a unique identifier
     * @returns {string} Unique ID
     */
    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    /**
     * Updates entity properties and timestamp
     * @param {Object} data - Data to update
     * @returns {BaseEntity} Updated entity instance
     */
    update(data) {
        Object.assign(this, data);
        this.updatedAt = new Date().toISOString();
        return this;
    }

    /**
     * Serializes entity to JSON
     * @returns {Object} JSON representation
     */
    toJSON() {
        return { ...this };
    }

    /**
     * Validates entity data
     * @returns {boolean} Validation result
     */
    validate() {
        return this.id && this.createdAt && this.updatedAt;
    }
}

// ===== DOMAIN ENTITIES =====

/**
 * Widget entity representing individual widgets
 * Extends BaseEntity with widget-specific properties and methods
 * * @class Widget
 * @extends BaseEntity
 */
export class Widget extends BaseEntity {
    constructor(data = {}) {
        super(data);
        this.title = data.title || '';
        this.type = data.type || 'text';
        this.content = data.content || '';
        this.description = data.description || '';
        this.category = data.category || '';
        this.tags = data.tags || '';
        this.status = data.status || 'draft';
        this.priority = data.priority || 0;
        this.settings = data.settings || '{}';
    }

    /**
     * Gets tags as array
     * @returns {Array<string>} Array of tags
     */
    getTagsArray() {
        return this.tags ? this.tags.split(',').map(tag => tag.trim()).filter(Boolean) : [];
    }

    /**
     * Gets metadata as object
     * @returns {Object} Metadata object
     */
    getMetadataObject() {
        try {
            return typeof this.metadata === 'string' ? JSON.parse(this.metadata) : this.metadata;
        } catch (e) {
            console.warn('Invalid metadata JSON:', this.metadata);
            return {};
        }
    }

    /**
     * Gets settings as object
     * @returns {Object} Settings object
     */
    getSettingsObject() {
        try {
            return typeof this.settings === 'string' ? JSON.parse(this.settings) : this.settings;
        } catch (e) {
            console.warn('Invalid settings JSON:', this.settings);
            return {};
        }
    }

    /**
     * Checks if widget is active
     * @returns {boolean} Active status
     */
    isActive() {
        return this.status === 'active';
    }

    /**
     * Checks if widget is draft
     * @returns {boolean} Draft status
     */
    isDraft() {
        return this.status === 'draft';
    }

    /**
     * Gets display-ready widget data
     * @returns {Object} Display data
     */
    getDisplayData() {
        return {
            id: this.id,
            title: this.title,
            type: this.type,
            content: this.content,
            description: this.description,
            category: this.category,
            tags: this.getTagsArray(),
            status: this.status,
            priority: this.priority,
            metadata: this.getMetadataObject(),
            settings: this.getSettingsObject(),
            createdAt: this.createdAt,
            updatedAt: this.updatedAt
        };
    }
}

/**
 * Widget Area entity for organizing widgets
 * Represents distinct areas where widgets can be placed
 * * @class WidgetArea
 * @extends BaseEntity
 */
export class WidgetArea extends BaseEntity {
    constructor(data = {}) {
        super(data);
        this.name = data.name || '';
        this.slug = data.slug || this.generateSlug(this.name);
        this.description = data.description || '';
        this.type = data.type || 'content'; // header, sidebar, content, footer, custom
        this.maxWidgets = data.maxWidgets || null; // null = unlimited
        this.widgetIds = data.widgetIds || [];
        this.layoutSettings = data.layoutSettings || {
            columns: 1,
            gap: 20,
            alignment: 'start'
        };
        this.permissions = data.permissions || {
            canAdd: true,
            canEdit: true,
            canDelete: true,
            canReorder: true
        };
        this.isActive = data.isActive !== undefined ? data.isActive : true;
    }

    /**
     * Generates URL-friendly slug from name
     * @param {string} name - Area name
     * @returns {string} Generated slug
     */
    generateSlug(name) {
        return name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
    }

    /**
     * Adds widget to area
     * @param {string} widgetId - Widget ID to add
     * @param {number} position - Position to insert at (optional)
     * @returns {boolean} Success status
     */
    addWidget(widgetId, position = null) {
        if (this.maxWidgets && this.widgetIds.length >= this.maxWidgets) {
            return false;
        }
        
        if (this.widgetIds.includes(widgetId)) {
            return false;
        }

        if (position === null || position >= this.widgetIds.length) {
            this.widgetIds.push(widgetId);
        } else {
            this.widgetIds.splice(position, 0, widgetId);
        }

        this.updatedAt = new Date().toISOString();
        return true;
    }

    /**
     * Removes widget from area
     * @param {string} widgetId - Widget ID to remove
     * @returns {boolean} Success status
     */
    removeWidget(widgetId) {
        const index = this.widgetIds.indexOf(widgetId);
        if (index !== -1) {
            this.widgetIds.splice(index, 1);
            this.updatedAt = new Date().toISOString();
            return true;
        }
        return false;
    }

    /**
     * Reorders widgets in area
     * @param {string} widgetId - Widget ID to move
     * @param {number} newPosition - New position
     * @returns {boolean} Success status
     */
    reorderWidget(widgetId, newPosition) {
        if (!this.permissions.canReorder) return false;
        
        const currentIndex = this.widgetIds.indexOf(widgetId);
        if (currentIndex === -1) return false;

        this.widgetIds.splice(currentIndex, 1);
        this.widgetIds.splice(newPosition, 0, widgetId);
        this.updatedAt = new Date().toISOString();
        return true;
    }

    /**
     * Gets widget count
     * @returns {number} Number of widgets
     */
    getWidgetCount() {
        return this.widgetIds.length;
    }

    /**
     * Checks if area can accept more widgets
     * @returns {boolean} Can accept status
     */
    canAcceptWidgets() {
        return this.isActive && 
               this.permissions.canAdd && 
               (this.maxWidgets === null || this.widgetIds.length < this.maxWidgets);
    }
}