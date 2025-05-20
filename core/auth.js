// core/auth.js
import { debug } from './debug.js';

// ─── CONFIGURE YOUR CREDENTIALS HERE ─────────────────────────────────────────
const CLIENT_ID     = '9AT9j4UoU2BgLEqmiYCz';
const CLIENT_SECRET = '9gTbAKBCZe1ftYQbLbq9';
const USERNAME      = 'dashboard';
const PASSWORD      = 'd@$hpa$$2024';
const SCOPE         = 'account';
const TOKEN_URL     = 'https://api.abassetmanagement.com/api3/token';
// ──────────────────────────────────────────────────────────────────────────────

let _token     = null;
let _expiresAt = 0;

/**
 * Actually POSTs to /token and caches the result in memory.
 */
async function fetchNewToken() {
  debug.log('🔑 Fetching new OAuth token…');
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
    body:     body.toString()
  });

  if (!res.ok) {
    const txt = await res.text();
    debug.error(`❌ Token endpoint returned ${res.status}: ${txt}`);
    throw new Error(`Token fetch failed: HTTP ${res.status}`);
  }

  const data = await res.json();
  if (!data.access_token) {
    debug.error('❌ No access_token in response', data);
    throw new Error('Token fetch failed: no access_token in payload');
  }

  // cache it, expire 1 minute before actual expiry
  _token     = data.access_token;
  _expiresAt = Date.now() + (data.expires_in * 1000) - 60000;

  debug.log(`✅ Token acquired; expires at ${new Date(_expiresAt).toISOString()}`);
  return _token;
}

/**
 * Returns a valid token, fetching a new one if needed.
 */
export async function getToken() {
  if (_token && Date.now() < _expiresAt) {
    return _token;
  }
  return fetchNewToken();
}

/**
 * A drop-in replacement for fetch() that adds the Bearer token header.
 */
export async function authFetch(url, options = {}) {
  const token = await getToken();
  const headers = {
    ...(options.headers || {}),
    Authorization: `Bearer ${token}`
  };
  return fetch(url, { ...options, headers });
}
