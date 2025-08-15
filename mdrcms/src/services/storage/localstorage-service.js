import { StorageService } from "./storage-service.js";

/**
 * LocalStorage implementation of StorageService
 */
export class LocalStorageService extends StorageService {
  async save(key, value) {
    const json = JSON.stringify(value);
    localStorage.setItem(key, json);
  }
  async load(key) {
    const raw = localStorage.getItem(key);
    if (!raw) return null;
    try { return JSON.parse(raw); }
    catch { return null; }
  }
}