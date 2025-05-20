// core/auth.js
// v1.2.1 [Fix: export named getToken]

import { debug } from './debug.js';

const CLIENT_ID     = '9AT9j4UoU2BgLEqmiYCz';
const CLIENT_SECRET = '9gTbAKBCZe1ftYQbLbq9';
const USERNAME      = 'dashboard';
const PASSWORD      = 'd@$hpa$$2024';
const SCOPE         = 'account';
const TOKEN_URL     = 'https://api.abassetmanagement.com/api3/token';

let _token     = null;
let _expiresAt = 0;

/**
 * Fetch and cache a new token.
 */
async function fetchNewToken() {
  debug.log('Auth: requesting new token');
  const body = new URLSearchParams({
    client_id:     CLIENT_ID,
    client_secret: CLIENT_SECRET,
    grant_type:    'password',
    username:      USERNAME,
    password:      PASSWORD,
    scope:         SCOPE
  });

  const res = await fetch(TOKEN_URL, {
    method:  'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body:    body.toString()
  });

  if (!res.ok) {
    const errTxt = await res.text().catch(() => res.statusText);
    debug.error(`Auth: token endpoint returned ${res.status}: ${errTxt}`);
    throw new Error(`Token fetch failed: HTTP ${res.status}`);
  }

  const data = await res.json();
  if (!data.access_token) {
    debug.error('Auth: no access_token in response', data);
    throw new Error('Token fetch failed: missing access_token');
  }

  _token     = data.access_token;
  _expiresAt = Date.now() + (data.expires_in * 1000) - 60000; // refresh 1m early
  debug.log(`Auth: token acquired, expires at ${new Date(_expiresAt).toISOString()}`);
  return _token;
}

/**
 * Return a valid token, fetching if expired or missing.
 */
export async function getToken() {
  if (_token && Date.now() < _expiresAt) {
    debug.log('Auth: using cached token');
    return _token;
  }
  return fetchNewToken();
}

// preserve default export if other code uses it
export default { getToken };
