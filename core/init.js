// core/init.js
// v1.0.4 [Fix: defer loading & attach debug toggle]
import { debug } from './debug.js';
import { getToken } from './auth.js';
import './event-bus.js';
import './store.js';
import './dom.js'; // your utility for core.dom.get()

document.addEventListener('DOMContentLoaded', () => {
  // Wire up the debug toggle button
  const btn = document.getElementById('debug-toggle');
  btn.addEventListener('click', () => {
    const turnOn = !btn.classList.toggle('active');
    debug.toggleDebug(turnOn);
    btn.textContent = `Debug: ${turnOn ? 'On' : 'Off'}`;
  });

  debug.log('Application initialized – DOMContentLoaded');

  // Pre-fetch token to fail fast if misconfigured
  getToken().catch(err => {
    debug.error(`Bootstrap token error: ${err.message}`);
  });
});