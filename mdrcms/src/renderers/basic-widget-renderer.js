import { WidgetRenderer } from "./widget-renderer.js";
import { el } from "../utils/dom.js";
import { sanitizeText } from "../utils/sanitize.js";

/**
 * Basic card renderer for text content widgets
 */
export class BasicWidgetRenderer extends WidgetRenderer {
  supports(widget) { return widget.type === "basic"; }

  render(widget) {
    const title = sanitizeText(widget.title);
    const content = sanitizeText(widget.content);
    const root = el("article", { class: "card", role: "region", "aria-label": title });
    const header = el("div", { class: "card-header" }, [
      el("h2", { class: "card-title", text: title })
    ]);
    const body = el("div", { class: "card-body" }, [content]);
    root.append(header, body);
    return root;
  }
}