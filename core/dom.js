// core/dom.js
export const dom = {
  get(id) {
    return document.getElementById(id);
  },
  query(selector) {
    return document.querySelector(selector);
  },
  queryAll(selector) {
    return Array.from(document.querySelectorAll(selector));
  }
};
