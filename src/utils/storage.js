/**
 * storage.js
 * v1.0.0
 * LocalStorage helper with JSON parse/stringify and error handling.
 */

import { debug } from '../contexts/DebugContext';

export function saveToStorage(key, value) {
  try {
    localStorage.setItem(key, JSON.stringify(value));
    debug.log(`Storage: saved key "${key}"`);
  } catch (e) {
    debug.error(`Storage: failed to save key "${key}": ${e.message}`);
  }
}

export function loadFromStorage(key, defaultValue = null) {
  try {
    const stored = localStorage.getItem(key);
    if (!stored) return defaultValue;
    return JSON.parse(stored);
  } catch (e) {
    debug.error(`Storage: failed to load key "${key}": ${e.message}`);
    return defaultValue;
  }
}

export function removeFromStorage(key) {
  try {
    localStorage.removeItem(key);
    debug.log(`Storage: removed key "${key}"`);
  } catch (e) {
    debug.error(`Storage: failed to remove key "${key}": ${e.message}`);
  }
}
