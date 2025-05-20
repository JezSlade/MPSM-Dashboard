/**
 * v1.1.0 [Fix: Init & Module Load Order]
 */
import debug from './debug.js';
import { get } from './dom.js';
import { loadToken } from '../modules/auth.js';

document.addEventListener('DOMContentLoaded', async () => {
  debug.log('DOM fully loaded and parsed');
  try {
    await loadToken();
    debug.log('Token successfully loaded');
    // You can now emit your core:init event or bootstrap modules here
  } catch (err) {
    debug.error(`Init error: ${err.message || err}`);
  }
});
