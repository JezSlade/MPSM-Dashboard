// core/dom.js
// v1.0.0 [DOM Utility]
// Provides a single entrypoint for looking up elements by ID, with built-in debug logging.

export const dom = {
  /**
   * Retrieve a DOM element by its ID.
   * @param {string} id — The element’s ID.
   * @returns {HTMLElement|null}
   */
  get(id) {
    const el = document.getElementById(id);
    if (!el) {
      window.DebugPanel.warn(`DOM.get: no element found with id="${id}"`);
    } else {
      window.DebugPanel.log(`DOM.get: found element id="${id}"`);
    }
    return el;
  }
};

// For convenience, also expose a global alias:
window.core = window.core || {};
window.core.dom = dom;
