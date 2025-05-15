// v1.0.2 [Add: Customer Dropdown Loader]
import './debug.js';
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import { loadToken } from '../modules/token.js';
import { loadCustomers } from '../modules/customers.js';

document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById("debug-toggle-float");
  if (toggle) {
    toggle.addEventListener("click", () => {
      const panel = document.getElementById("debug-panel");
      panel?.classList.toggle("open");
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  eventBus.emit("core:init", { version: "1.0.2", time: new Date().toISOString() });
  loadToken();
  loadCustomers();
});
