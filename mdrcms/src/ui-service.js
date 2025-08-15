import { BasicWidgetRenderer } from "./renderers/basic-widget-renderer.js";

/**
 * UI coordinator that delegates to widget renderers
 */
export class UIService {
  constructor() {
    /** @type {Array<{supports: Function, render: Function}>} */
    this.renderers = [ new BasicWidgetRenderer() ];
  }

  /**
   * Register an additional renderer
   * @param {{supports: Function, render: Function}} renderer
   */
  registerRenderer(renderer) {
    this.renderers.push(renderer);
  }

  /**
   * Render a set of widgets into the dashboard
   * @param {import('./models/widget-model.js').Widget[]} widgets
   * @param {HTMLElement} mount
   */
  renderWidgets(widgets, mount) {
    if (!mount) throw new Error("Mount element missing");
    mount.innerHTML = "";
    for (const w of widgets) {
      const renderer = this.renderers.find(r => r.supports(w));
      if (!renderer) continue;
      const node = renderer.render(w);
      mount.appendChild(node);
    }
  }
}