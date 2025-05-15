// v1.0.3 [Fix: Add Devices Module + Debug Toggle Behavior]
import './debug.js';
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import { loadToken } from '../modules/token.js';
import { loadCustomers } from '../modules/customers.js';
import '../modules/devices.js'; // ⬅️ this enables device fetching

document.addEventListener('DOMContentLoaded', () => {
  eventBus.emit("core:init", { version: "1.0.3", time: new Date().toISOString() });
  loadToken();
  loadCustomers();

  const toggle = document.getElementById("debug-toggle-float");
  if (toggle) {
    toggle.addEventListener("click", () => {
      const panel = document.getElementById("debug-panel");
      panel?.classList.toggle("hidden");
    });
  }
});
