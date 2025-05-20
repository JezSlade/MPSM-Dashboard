// core/init.js
// v1.0.4 [Fix: Ensure debug import matches debug.js export]

import { debug } from './debug.js';
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import { loadToken } from '../modules/token.js';

document.addEventListener('DOMContentLoaded', async () => {
  try {
    debug.log('Bootstrapping application…');
    eventBus.emit('core:init', { version: '1.0.4', time: new Date().toISOString() });
    await loadToken();
    debug.log('Token loaded successfully.');
  } catch (e) {
    debug.error(`Bootstrap error: ${e.message}`);
  }
});