import { STORAGE_KEYS } from "../constants/storage-keys.js";
import { Widget } from "../models/widget-model.js";

/**
 * Repository for widgets with basic validation and mapping
 */
export class WidgetRepository {
  /**
   * @param {{ load: Function, save: Function }} storage
   */
  constructor(storage) {
    this.storage = storage;
  }

  async getAll() {
    const list = await this.storage.load(STORAGE_KEYS.widgets);
    if (!Array.isArray(list)) return [];
    return list
      .filter(w => w && typeof w === "object")
      .map(w => new Widget({
        id: w.id ?? crypto.randomUUID?.() ?? String(Date.now()),
        title: w.title ?? "Untitled",
        type: w.type ?? "basic",
        content: w.content ?? ""
      }));
  }

  /**
   * @param {Widget[]} widgets
   */
  async saveAll(widgets) {
    const plain = widgets.map(w => ({ id: w.id, title: w.title, type: w.type, content: w.content }));
    await this.storage.save(STORAGE_KEYS.widgets, plain);
  }
}