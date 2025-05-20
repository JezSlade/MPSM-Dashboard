// core/init.js
import { dom } from './dom.js';
import { debug } from './debug.js';
import { auth } from './auth.js';
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import '../app.js'; // or however you bootstrap your modules

document.addEventListener('DOMContentLoaded', () => {
  // Wire up the debug toggle switch
  const toggle = dom.get('debug-toggle');
  if (toggle) {
    toggle.addEventListener('change', e => debug.toggle(e.target.checked));
  }

  // Emit core:init so modules can know the version/time
  const version = store.get('version') || 'v1.0.0';
  eventBus.emit('core:init', {
    version,
    time: new Date().toISOString()
  });

  // Pre-fetch a token to unlock the queue
  auth.getToken()
    .then(() => debug.log('Initial token ready'))
    .catch(err => debug.error('Token initialization failed: ' + err.message));
});
// core/init.js
import { dom } from './dom.js';
import { debug } from './debug.js';
import { auth } from './auth.js';
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import '../app.js'; // or however you bootstrap your modules

document.addEventListener('DOMContentLoaded', () => {
  // Wire up the debug toggle switch
  const toggle = dom.get('debug-toggle');
  if (toggle) {
    toggle.addEventListener('change', e => debug.toggle(e.target.checked));
  }

  // Emit core:init so modules can know the version/time
  const version = store.get('version') || 'v1.0.0';
  eventBus.emit('core:init', {
    version,
    time: new Date().toISOString()
  });

  // Pre-fetch a token to unlock the queue
  auth.getToken()
    .then(() => debug.log('Initial token ready'))
    .catch(err => debug.error('Token initialization failed: ' + err.message));
});
