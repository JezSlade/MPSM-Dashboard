// v1.0.0 [Init: Core Loader]
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import './debug.js';

document.addEventListener('DOMContentLoaded', () => {
  eventBus.emit("core:init", { version: "1.0.0", time: new Date().toISOString() });
});
