// core/init.js
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import '../modules/token.js';
import { loadToken } from '../modules/token.js';

document.addEventListener('DOMContentLoaded', () => {
  eventBus.emit("core:init", { version: "1.0.0", time: new Date().toISOString() });
  loadToken(); // trigger token fetch
});
