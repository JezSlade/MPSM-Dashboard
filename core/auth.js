// core/auth.js
// v1.0.3 [Fix: Import debug correctly]

import { debug } from './debug.js';

export async function fetchToken() {
  try {
    debug.log('Requesting token from API…');
    // ... existing token fetch logic ...
    debug.log('Token received.');
  } catch (err) {
    debug.error(`Token fetch failed: ${err.message}`);
    throw err;
  }
}
