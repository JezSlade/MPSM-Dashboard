/**
 * @typedef {Object} WidgetInit
 * @property {string} id
 * @property {string} title
 * @property {string} type
 * @property {string} content
 */

/**
 * Immutable-ish widget model
 */
export class Widget {
  /**
   * @param {WidgetInit} init
   */
  constructor(init) {
    this.id = String(init.id);
    this.title = String(init.title ?? "Untitled");
    this.type = String(init.type ?? "basic");
    this.content = String(init.content ?? "");
    Object.freeze(this);
  }
}