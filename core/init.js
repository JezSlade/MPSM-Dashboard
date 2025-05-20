// core/init.js
// v1.2.0 [Verify: correct getToken import]

import debug from './debug.js';
import { getToken } from './auth.js';       // now matches named export
import { eventBus } from './event-bus.js';

document.addEventListener('DOMContentLoaded', async () => {
  debug.log('Bootstrap: DOMContentLoaded');
  try {
    await getToken();
    debug.log('Bootstrap: getToken() succeeded');
    eventBus.emit('core:init', { time: new Date().toISOString() });
  } catch (err) {
    debug.error(`Bootstrap error: ${err.message}`);
  }
});
