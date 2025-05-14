// v1.0.1 [Fix: Ensure DebugPanel Loaded First]
import './debug.js'; // 🔥 must load BEFORE anything logs to it
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import { loadToken } from '../modules/token.js';

document.addEventListener('DOMContentLoaded', () => {
  eventBus.emit("core:init", { version: "1.0.0", time: new Date().toISOString() });
  loadToken();
});
