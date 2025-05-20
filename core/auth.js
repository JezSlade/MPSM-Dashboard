// core/auth.js
// v1.0.2 [Fix: import debug correctly]
import { debug } from './debug.js';

const CLIENT_ID     = '9AT9j4UoU2BgLEqmiYCz';
const CLIENT_SECRET = '9gTbAKBCZe1ftYQbLbq9';
const USERNAME      = 'dashboard';
const PASSWORD      = 'd@$hpa$$2024';
const SCOPE         = 'account';
const TOKEN_URL     = 'https://api.abassetmanagement.com/api3/token';

let tokenCache  = null;
let expiresAt   = 0;

export async function getToken() {
  if (tokenCache && Date.now() < expiresAt) {
    debug.log('Returning cached token');
    return tokenCache;
  }
  debug.log('Fetching new token');
  try {
    const params = new URLSearchParams({
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
      body:    params
    });
    if (!res.ok) {
      const text = await res.text();
      debug.error(`Token request failed ${res.status}: ${text}`);
      throw new Error(`Token request failed ${res.status}`);
    }

    const data = await res.json();
    tokenCache = data.access_token;
    // subtract 60s to refresh early
    expiresAt = Date.now() + (data.expires_in * 1000) - 60000;
    debug.log('Token fetched and cached');
    return tokenCache;
  } catch (e) {
    debug.error(`getToken error: ${e.message}`);
    throw e;
  }
}

export async function authFetch(url, opts = {}) {
  const token = await getToken();
  debug.log(`authFetch ${opts.method||'GET'} ${url}`);
  const headers = new Headers(opts.headers || {});
  headers.set('Authorization', `Bearer ${token}`);
  opts.headers = headers;

  try {
    const res = await fetch(url, opts);
    if (!res.ok) {
      const text = await res.text();
      debug.error(`authFetch failed ${res.status}: ${text}`);
      throw new Error(`Fetch failed ${res.status}`);
    }
    return res;
  } catch (e) {
    debug.error(`authFetch error: ${e.message}`);
    throw e;
  }
}
