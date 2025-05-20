// v1.1.1 [Fix: import auth from core/auth.js instead of modules/auth.js]
import debug from './debug.js';
import { get } from './dom.js';
import { getToken } from './auth.js';      // ← correct path
import { eventBus } from './event-bus.js';

// Wire up the debug toggle
document.addEventListener('DOMContentLoaded', async () => {
  const dbgToggle = get('debug-toggle');
  if (dbgToggle) {
    dbgToggle.addEventListener('change', () => debug.toggle());
  }

  debug.log('DOM loaded, starting bootstrap');
  try {
    await getToken();
    debug.log('Token acquired successfully');
    eventBus.emit('core:init', {
      version: document.getElementById('version').textContent,
      time: new Date().toISOString()
    });
    // … initialize your modules here …
  } catch (err) {
    debug.error(`Bootstrap error: ${err.message}`);
  }
});
