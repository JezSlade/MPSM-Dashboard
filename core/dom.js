// core/dom.js
// v1.0.0 — Provides a tiny wrapper around document.getElementById for consistency.

export const dom = {
  init() {
    // No global DOM setup needed right now
  },

  /**
   * @param {string} id
   * @returns {HTMLElement|null}
   */
  get(id) {
    return document.getElementById(id);
  }
};
