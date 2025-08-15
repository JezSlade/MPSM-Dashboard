/**
 * @typedef {Object} SettingsInit
 * @property {"light"|"dark"} [theme]
 * @property {"grid"|"list"} [layout]
 */

/**
 * Simple settings model
 */
export class Settings {
  /**
   * @param {SettingsInit} init
   */
  constructor(init = {}) {
    this.theme = init.theme === "dark" ? "dark" : "light";
    this.layout = init.layout === "list" ? "list" : "grid";
    Object.freeze(this);
  }
}