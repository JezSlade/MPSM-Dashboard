// src/utils/storage.js
export function saveToStorage(key, value) {
  try {
    localStorage.setItem(key, JSON.stringify(value));
  } catch (e) {
    console.error(`Storage: failed to save key "${key}": ${e.message}`);
  }
}

export function loadFromStorage(key, defaultValue = null) {
  try {
    const stored = localStorage.getItem(key);
    if (!stored) return defaultValue;
    return JSON.parse(stored);
  } catch (e) {
    console.error(`Storage: failed to load key "${key}": ${e.message}`);
    return defaultValue;
  }
}

export function removeFromStorage(key) {
  try {
    localStorage.removeItem(key);
  } catch (e) {
    console.error(`Storage: failed to remove key "${key}": ${e.message}`);
  }
}
