/**
 * core/dom.js
 * v1.0.0 [DOM: Utility for safe element access & creation]
 */

export default {
  get(id) {
    return document.getElementById(id);
  },
  create(tag, attrs = {}) {
    const el = document.createElement(tag);
    Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
    return el;
  }
};
