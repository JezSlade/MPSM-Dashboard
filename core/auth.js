/**
 * core/auth.js
 * v1.0.1  [Fixed: import debug as default]
 */

import debug from './debug.js';

const auth = (() => {
  let _token = null;
  let _expiry = 0;

  async function fetchToken() {
    debug.log('Auth: requesting new token');
    // replace with your real token URL and payload
    const resp = await fetch('/token', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        client_id: CLIENT_ID,
        client_secret: CLIENT_SECRET,
        grant_type: 'password',
        username: USERNAME,
        password: PASSWORD,
        scope: SCOPE
      })
    });

    if (!resp.ok) {
      const txt = await resp.text();
      debug.error(`Auth: token fetch failed ${resp.status}: ${txt}`);
      throw new Error('Auth: token fetch failed');
    }

    const data = await resp.json();
    _token  = data.access_token;
    _expiry = Date.now() + (data.expires_in * 1000) - 60000; // refresh 1min early
    debug.log(`Auth: token acquired, expires at ${new Date(_expiry).toISOString()}`);
  }

  return {
    async getToken() {
      if (!_token || Date.now() >= _expiry) {
        await fetchToken();
      }
      return _token;
    },

    async fetch(url, options = {}) {
      const t = await this.getToken();
      options.headers = {
        ...(options.headers || {}),
        'Authorization': `Bearer ${t}`
      };
      debug.log(`Auth.fetch → ${options.method||'GET'} ${url}`);
      const res = await fetch(url, options);
      if (res.status === 401) {
        debug.warn('Auth.fetch → 401, refreshing token and retrying');
        _token = null;
        return this.fetch(url, options);
      }
      return res;
    }
  };
})();

export default auth;
