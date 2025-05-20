// core/init.js
// v1.2.1 [Fix: Initialize customer & device modules]

import debug from './debug.js';
import { get } from './dom.js';
import { getToken } from './auth.js';
import { eventBus } from './event-bus.js';
import { loadCustomers } from '../modules/customers.js';
import { initDevices } from '../modules/devices.js'; // assume this exists

document.addEventListener('DOMContentLoaded', async () => {
  // Wire up debug toggle
  const dbgToggle = get('debug-toggle');
  dbgToggle?.addEventListener('change', () => {
    debug.toggle(); 
    debug.log('Debug mode ' + (dbgToggle.checked ? 'enabled' : 'disabled'));
  });

  debug.log('Bootstrap: DOMContentLoaded');

  try {
    // 1) Acquire token
    await getToken();
    debug.log('Bootstrap: Token acquired');

    // 2) Notify core:init
    const versionEl = get('version');
    const version = versionEl ? versionEl.textContent : 'unknown';
    eventBus.emit('core:init', { version, time: new Date().toISOString() });
    debug.log(`Bootstrap: core:init emitted (v${version})`);

    // 3) Initialize modules
    await loadCustomers();
    debug.log('Bootstrap: Customers module loaded');

    initDevices();  
    debug.log('Bootstrap: Devices module initialized');
  } catch (err) {
    debug.error(`Bootstrap error: ${err.message}`);
  }
});
