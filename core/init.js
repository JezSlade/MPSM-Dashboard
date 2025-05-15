// v1.0.3 [Add: Device Module Load]
import './debug.js';
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import { loadToken } from '../modules/token.js';
import { loadCustomers } from '../modules/customers.js';
import '../modules/devices.js';

document.addEventListener('DOMContentLoaded', () => {
  eventBus.emit("core:init", { version: "1.0.3", time: new Date().toISOString() });
  loadToken();
  loadCustomers();
});
