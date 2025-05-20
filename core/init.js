// core/init.js
// v2.1.1 [Debug fix, header version only]
import './debug.js';
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import { loadToken } from '../modules/token.js';
import { loadCustomers } from '../modules/customers.js';
import '../modules/devices.js';
import '../modules/overlay.js';

document.addEventListener('DOMContentLoaded', () => {
  const header = document.createElement('div');
  header.id = 'dashboard-header';
  header.innerHTML = `
    <span class="header-title">MPSM Dashboard</span>
    <span class="header-version">v2.1.1</span>
  `;
  document.body.prepend(header);

  const debugPanel = document.getElementById('debug-panel');
  debugPanel?.classList.remove('visible');

  eventBus.emit("core:init", { version: "2.1.1", time: new Date().toISOString() });
  window.DebugPanel?.logEvent("core:init", { version: "2.1.1" });

  loadToken();
  loadCustomers();
});
