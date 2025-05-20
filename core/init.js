// core/init.js
// v1.0.1 [Initialize Core & Debug Panel]
// Ensures debug panel is ready, version is displayed, and core:init fires correctly.

import './debug.js';                              // must load first to catch all logs
import { eventBus } from './event-bus.js';
import { store } from './store.js';
import './dom.js';
import '../modules/token.js';
import { loadToken } from '../modules/token.js';
// import other modules here, e.g.:
// import '../modules/customers.js';
// import '../modules/devices.js';

const APP_VERSION = '1.0.1';

document.addEventListener('DOMContentLoaded', () => {
  // Display version in header if the element exists
  const versionEl = document.getElementById('app-version');
  if (versionEl) {
    versionEl.textContent = `v${APP_VERSION}`;
    window.DebugPanel.log(`App version set to ${APP_VERSION}`);
  }

  // Emit core:init so all modules can react
  eventBus.emit('core:init', {
    version: APP_VERSION,
    time: new Date().toISOString()
  });

  // Trigger token load and log outcome
  loadToken()
    .then(token => {
      window.DebugPanel.log('Token acquired successfully');
    })
    .catch(err => {
      window.DebugPanel.error('Token acquisition failed');
      window.DebugPanel.logError('loadToken error', err);
    });
});
