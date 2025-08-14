/**
 * WIDGET DASHBOARD APPLICATION
 * * This file contains the repository layer for data persistence.
 * It defines the classes for managing data access to local storage.
 */

import { BaseEntity, Widget, WidgetArea } from './entities.js';

/**
 * Abstract repository class defining data access interface
 * Implements Repository pattern for data persistence
 * * @abstract
 * @class BaseRepository
 */
export class BaseRepository {
    constructor(storageKey) {
        this.storageKey = storageKey;
        this.cache = new Map();
        this.observers = new Set();
        this.loadFromStorage();
    }

    /**
     * Loads data from storage
     * @protected
     */
    loadFromStorage() {
        try {
            const data = localStorage.getItem(this.storageKey);
            if (data) {
                const items = JSON.parse(data);
                items.forEach(item => this.cache.set(item.id, item));
            }
        } catch (error) {
            console.error(`Error loading ${this.storageKey} from storage:`, error);
        }
    }

    /**
     * Saves data to storage
     * @protected
     */
    saveToStorage() {
        try {
            const items = Array.from(this.cache.values());
            localStorage.setItem(this.storageKey, JSON.stringify(items));
            this.notifyObservers('dataChanged', items);
        } catch (error) {
            console.error(`Error saving ${this.storageKey} to storage:`, error);
        }
    }

    /**
     * Adds observer for data changes
     * @param {Function} observer - Observer function
     */
    addObserver(observer) {
        this.observers.add(observer);
    }

    /**
     * Removes observer
     * @param {Function} observer - Observer function
     */
    removeObserver(observer) {
        this.observers.delete(observer);
    }

    /**
     * Notifies all observers of changes
     * @param {string} event - Event type
     * @param {*} data - Event data
     * @protected
     */
    notifyObservers(event, data) {
        this.observers.forEach(observer => {
            try {
                observer(event, data);
            } catch (error) {
                console.error('Observer error:', error);
            }
        });
    }

    /**
     * Creates new entity
     * @param {BaseEntity} entity - Entity to create
     * @returns {BaseEntity} Created entity
     */
    create(entity) {
        this.cache.set(entity.id, entity.toJSON());
        this.saveToStorage();
        this.notifyObservers('entityCreated', entity);
        return entity;
    }

    /**
     * Finds entity by ID
     * @param {string} id - Entity ID
     * @returns {BaseEntity|null} Found entity or null
     */
    findById(id) {
        const data = this.cache.get(id);
        return data ? this.createEntityFromData(data) : null;
    }

    /**
     * Finds all entities
     * @returns {Array<BaseEntity>} All entities
     */
    findAll() {
        return Array.from(this.cache.values()).map(data => this.createEntityFromData(data));
    }

    /**
     * Updates entity
     * @param {string} id - Entity ID
     * @param {Object} updateData - Update data
     * @returns {BaseEntity|null} Updated entity or null
     */
    update(id, updateData) {
        const existing = this.cache.get(id);
        if (existing) {
            const entity = this.createEntityFromData(existing);
            const updated = entity.update(updateData);
            this.cache.set(id, updated.toJSON());
            this.saveToStorage();
            this.notifyObservers('entityUpdated', updated);
            return updated;
        }
        return null;
    }

    /**
     * Deletes entity
     * @param {string} id - Entity ID
     * @returns {boolean} Success status
     */
    delete(id) {
        const existing = this.cache.get(id);
        if (existing) {
            const deleted = this.cache.delete(id);
            if (deleted) {
                this.saveToStorage();
                this.notifyObservers('entityDeleted', { id, entity: existing });
            }
            return deleted;
        }
        return false;
    }

    /**
     * Deletes all entities
     */
    deleteAll() {
        this.cache.clear();
        this.saveToStorage();
        this.notifyObservers('allEntitiesDeleted', {});
    }

    /**
     * Gets entity count
     * @returns {number} Entity count
     */
    count() {
        return this.cache.size;
    }

    /**
     * Creates entity instance from data
     * @abstract
     * @param {Object} data - Entity data
     * @returns {BaseEntity} Entity instance
     */
    createEntityFromData(data) {
        throw new Error('createEntityFromData() must be implemented by subclass');
    }
}

/**
 * Widget repository implementation
 * Manages widget data persistence and retrieval
 * * @class WidgetRepository
 * @extends BaseRepository
 */
export class WidgetRepository extends BaseRepository {
    constructor() {
        super('widget-dashboard-widgets');
    }

    /**
     * Creates Widget entity from data
     * @param {Object} data - Widget data
     * @returns {Widget} Widget instance
     */
    createEntityFromData(data) {
        return new Widget(data);
    }

    /**
     * Finds widgets by category
     * @param {string} category - Category name
     * @returns {Array<Widget>} Widgets in category
     */
    findByCategory(category) {
        return this.findAll().filter(widget => widget.category === category);
    }

    /**
     * Finds active widgets
     * @returns {Array<Widget>} Active widgets
     */
    findActive() {
        return this.findAll().filter(widget => widget.isActive());
    }

    /**
     * Finds widgets by status
     * @param {string} status - Widget status
     * @returns {Array<Widget>} Widgets with status
     */
    findByStatus(status) {
        return this.findAll().filter(widget => widget.status === status);
    }

    /**
     * Gets all categories
     * @returns {Array<string>} Unique categories
     */
    getCategories() {
        const categories = this.findAll()
            .map(widget => widget.category)
            .filter(Boolean);
        return [...new Set(categories)];
    }
}

/**
 * Widget Area repository implementation
 * Manages widget area data persistence
 * * @class WidgetAreaRepository
 * @extends BaseRepository
 */
export class WidgetAreaRepository extends BaseRepository {
    constructor() {
        super('widget-dashboard-areas');
    }

    /**
     * Creates WidgetArea entity from data
     * @param {Object} data - Widget area data
     * @returns {WidgetArea} Widget area instance
     */
    createEntityFromData(data) {
        return new WidgetArea(data);
    }

    /**
     * Finds area by slug
     * @param {string} slug - Area slug
     * @returns {WidgetArea|null} Found area or null
     */
    findBySlug(slug) {
        return this.findAll().find(area => area.slug === slug) || null;
    }

    /**
     * Finds active areas
     * @returns {Array<WidgetArea>} Active areas
     */
    findActive() {
        return this.findAll().filter(area => area.isActive);
    }

    /**
     * Finds areas by type
     * @param {string} type - Area type
     * @returns {Array<WidgetArea>} Areas of type
     */
    findByType(type) {
        return this.findAll().filter(area => area.type === type);
    }
}