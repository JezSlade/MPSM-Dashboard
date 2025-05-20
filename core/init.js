// core/init.js
// v1.0.4 [Fix: remove duplicate imports, proper init sequence]

import { eventBus } from './event-bus.js';
import { store }    from './store.js';
import { dom }      from './dom.js';
import { debug }    from './debug.js';
import { getToken } from './auth.js';       // your auth wrapper
import { loadToken } from '../modules/token.js'; // module that actually fetches the token

// Set your current version here so it's available everywhere
store.set('appVersion', '1.0.4');

document.addEventListener('DOMContentLoaded', async () => {
  // 1) Initialize DOM utilities (if needed)
  dom.init();

  // 2) Initialize debug panel & toggle
  debug.init({              // assume debug.init wires up the toggle and panel
    toggleSelector: '#debug-toggle',
    panelSelector:  '#debug-panel'
  });

  // 3) Emit core:init so modules can start listening
  eventBus.emit('core:init', {
    version: store.get('appVersion'),
    time:    new Date().toISOString()
  });

  // 4) Kick off token loading
  try {
    // If you just want the token, call getToken(),
    // but if your token module has its own loader use loadToken()
    await loadToken();
    eventBus.emit('core:tokenAcquired', {});
  } catch (err) {
    debug.error(`Token acquisition failed: ${err.message || err}`);
  }
});
