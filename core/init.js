// core/init.js
// v2.1.0 [Header version, debug off by default, boot core]
import './debug.js';
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import { loadToken } from '../modules/token.js';
import { loadCustomers } from '../modules/customers.js';
import '../modules/devices.js';

document.addEventListener('DOMContentLoaded', () => {
  const header = document.createElement('div');
  header.id = 'dashboard-header';
  header.innerHTML = '<span class="header-title">MPSM Dashboard</span><span class="header-version">v2.1.0</span>';
  document.body.prepend(header);

  const debugPanel = document.getElementById('debug-panel');
  debugPanel?.classList.remove('visible'); // start hidden

  eventBus.emit("core:init", { version: "2.1.0", time: new Date().toISOString() });
  loadToken();
  loadCustomers();
});
