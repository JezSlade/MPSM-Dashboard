/**
 * State Manager Module
 * Centralized application state with reactive updates
 */

export class StateManager {
    constructor() {
        this.state = {};
        this.listeners = new Map();
    }

    /**
     * Get state value
     */
    get(key) {
        return this.state[key];
    }

    /**
     * Set state value and notify listeners
     */
    set(key, value) {
        const oldValue = this.state[key];
        this.state[key] = value;

        // Notify listeners if value changed
        if (oldValue !== value) {
            this._notify(key, value, oldValue);
        }
    }

    /**
     * Update multiple state values
     */
    update(updates) {
        Object.entries(updates).forEach(([key, value]) => {
            this.set(key, value);
        });
    }

    /**
     * Subscribe to state changes
     */
    subscribe(key, callback) {
        if (!this.listeners.has(key)) {
            this.listeners.set(key, []);
        }

        this.listeners.get(key).push(callback);

        // Return unsubscribe function
        return () => {
            const callbacks = this.listeners.get(key);
            const index = callbacks.indexOf(callback);
            if (index > -1) {
                callbacks.splice(index, 1);
            }
        };
    }

    /**
     * Notify listeners of state change
     */
    _notify(key, newValue, oldValue) {
        const callbacks = this.listeners.get(key) || [];
        callbacks.forEach(callback => {
            try {
                callback(newValue, oldValue);
            } catch (error) {
                console.error(`[State] Listener error for "${key}":`, error);
            }
        });
    }

    /**
     * Clear all state
     */
    clear() {
        this.state = {};
        this.listeners.clear();
    }

    /**
     * Get all state
     */
    getAll() {
        return { ...this.state };
    }
}

// Singleton instance
export const state = new StateManager();
