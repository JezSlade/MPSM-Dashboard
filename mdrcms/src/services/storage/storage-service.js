/**
 * Async storage contract to future proof remote backends
 */
export class StorageService {
  /** @param {string} key @param {any} value */
  async save(key, value) { throw new Error("Not implemented"); }
  /** @param {string} key */
  async load(key) { throw new Error("Not implemented"); }
}