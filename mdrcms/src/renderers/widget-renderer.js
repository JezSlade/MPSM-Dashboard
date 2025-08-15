/**
 * Interface for widget renderers
 */
export class WidgetRenderer {
  /**
   * Determine if renderer supports the given widget
   * @param {import('../models/widget-model.js').Widget} widget
   * @returns {boolean}
   */
  supports(widget) { throw new Error("Not implemented"); }

  /**
   * Render a widget into a DOM element
   * @param {import('../models/widget-model.js').Widget} widget
   * @returns {HTMLElement}
   */
  render(widget) { throw new Error("Not implemented"); }
}