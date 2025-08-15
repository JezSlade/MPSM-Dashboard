import { STORAGE_KEYS } from "../constants/storage-keys.js";
import { Settings } from "../models/settings-model.js";

/**
 * Repository for settings with basic validation
 */
export class SettingsRepository {
  /**
   * @param {{ load: Function, save: Function }} storage
   */
  constructor(storage) {
    this.storage = storage;
  }

  async get() {
    const raw = await this.storage.load(STORAGE_KEYS.settings);
    if (!raw || typeof raw !== "object") return new Settings();
    return new Settings(raw);
  }

  /**
   * @param {Settings} settings
   */
  async save(settings) {
    await this.storage.save(STORAGE_KEYS.settings, { theme: settings.theme, layout: settings.layout });
  }
}