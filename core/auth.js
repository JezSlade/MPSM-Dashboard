// core/auth.js
import { debug } from './debug.js';

const BASE_URL = window.MPSM_BASE_URL || ''; // set this globally or adjust
let _token = null;
let _expiresAt = 0;

async function getToken() {
  if (_token && Date.now() < _expiresAt) {
    debug.log('Using cached token');
    return _token;
  }

  debug.log('Fetching new token');
  const url = `${BASE_URL}/token`;
  const params = new URLSearchParams({
    client_id: window.MPSM_CLIENT_ID,
    client_secret: window.MPSM_CLIENT_SECRET,
    grant_type: 'password',
    username: window.MPSM_USERNAME,
    password: window.MPSM_PASSWORD,
    scope: window.MPSM_SCOPE
  });

  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params
  });
  if (!res.ok) {
    const txt = await res.text().catch(() => res.statusText);
    debug.error(`Token fetch failed ${res.status}: ${txt}`);
    throw new Error(`Token fetch failed: ${res.status}`);
  }
  const data = await res.json();
  _token = data.access_token;
  _expiresAt = Date.now() + (data.expires_in - 30) * 1000;
  debug.log('New token acquired');
  return _token;
}

async function fetchWithAuth(input, init = {}) {
  const token = await getToken();
  init.headers = {
    ...(init.headers || {}),
    Authorization: `Bearer ${token}`
  };
  let res = await fetch(input, init);
  if (res.status === 401) {
    debug.warn('Received 401, refreshing token');
    const fresh = await getToken();
    init.headers.Authorization = `Bearer ${fresh}`;
    res = await fetch(input, init);
  }
  if (!res.ok) {
    const errTxt = await res.text().catch(() => res.statusText);
    debug.error(`Fetch ${input} failed ${res.status}: ${errTxt}`);
    throw new Error(`Fetch failed: ${res.status}`);
  }
  debug.log(`Fetch ${input} succeeded (${res.status})`);
  return res;
}

export const auth = { getToken, fetch: fetchWithAuth };
