/*!
 * js/app.js
 * ------------------------------------------------------
 * Renders cards, fetches OAuth token, proxies all API
 * calls via api-proxy.php (no CORS), and logs every
 * request, response, and JavaScript error into the
 * Debug Panel, which can now be toggled on/off.
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // DOM refs
  const debugPanel = document.getElementById('debug-panel');
  const dbDot      = document.getElementById('dbStatus');
  const apiDot     = document.getElementById('apiStatus');
  const cardsView  = document.getElementById('cardsViewport');
  const modal      = document.getElementById('modal');
  const modalBody  = document.getElementById('modalBody');
  const modalClose = document.getElementById('modalClose');
  const toggleBtn  = document.getElementById('toggleDebug');

  // Utility: log into Debug Panel
  function jsLog(msg) {
    if (!debugPanel) return;
    const line = document.createElement('div');
    line.className = 'debug-log-line';
    line.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
    debugPanel.appendChild(line);
    debugPanel.scrollTop = debugPanel.scrollHeight;
  }

  // Global JS error handlers
  window.addEventListener('error', event => {
    jsLog(`Global Error: ${event.message} at ${event.filename}:${event.lineno}`);
  });
  window.addEventListener('unhandledrejection', event => {
    jsLog(`Unhandled Promise Rejection: ${event.reason}`);
  });
  const origErr = console.error;
  console.error = function(...args) {
    jsLog('Console.error: ' + args.join(' '));
    origErr.apply(console, args);
  };

  // On DOM ready
  document.addEventListener('DOMContentLoaded', () => {
    // Toggle debug panel
    toggleBtn.addEventListener('click', () => {
      const hidden = debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden ? 'Show Debug' : 'Hide Debug';
      jsLog(`Debug panel ${hidden ? 'hidden' : 'visible'}`);
    });

    // Fetch OAuth token
    fetch('get-token.php')
      .then(r => r.json())
      .then(json => {
        if (json.access_token) {
          window.apiToken = json.access_token;
          jsLog('API token acquired');
        } else {
          jsLog('Token error: ' + (json.error || 'unknown'));
        }
      })
      .catch(err => {
        jsLog('Token fetch failed: ' + err.message);
      });

    // Health checks
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // Render cards
    renderAllCards();

    // Modal close
    modalClose.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => {
      if (e.target === modal) modal.style.display = 'none';
    });
  });

  // Render one card per endpoint
  function renderAllCards() {
    cardsView.innerHTML = '';
    const eps = window.allEndpoints || [];
    jsLog(`Rendering ${eps.length} endpoint cards`);
    eps.forEach(ep => {
      const card = document.createElement('div');
      card.className = 'card';
      card.innerHTML = `
        <h3>${ep.method} ${ep.path}</h3>
        <p class="summary">${ep.summary || ''}</p>
      `;
      card.addEventListener('click', () => openModal(ep));
      cardsView.appendChild(card);
    });
  }

  // Show drill-down modal with “Try It”
  function openModal(ep) {
    modalBody.innerHTML = `
      <h2>${ep.method} ${ep.path}</h2>
      <p><strong>Summary:</strong> ${ep.summary || '(none)'}</p>
      <p><strong>Description:</strong> ${ep.description || '(none)'}</p>
      <button id="tryBtn">Try It</button>
      <pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn')
      .addEventListener('click', () => tryIt(ep));
    modal.style.display = 'flex';
    jsLog(`Opened modal for ${ep.method} ${ep.path}`);
  }

  // Invoke the endpoint via our PHP proxy (no CORS)
  function tryIt(ep) {
    const resEl = document.getElementById('tryResult');
    if (!window.apiToken) {
      jsLog('Cannot call API: no token');
      resEl.textContent = 'No API token available.';
      return;
    }
    const method   = ep.method;
    const path     = ep.path;
    const proxyUrl = `api-proxy.php?method=${encodeURIComponent(method)}&path=${encodeURIComponent(path)}`;

    // Log request
    jsLog(`[Request] ${method} ${path}`);
    jsLog(`[Proxy URL] ${proxyUrl}`);
    jsLog(`[Request Headers] ${JSON.stringify({
      'Authorization': `Bearer ${window.apiToken}`,
      'Accept':        'application/json',
      'Content-Type':  'application/json'
    }, null, 2)}`);

    // Fetch via proxy
    fetch(proxyUrl, {
      method: method,
      headers: {'Accept':'application/json','Content-Type':'application/json'},
      body: method === 'POST'
            ? JSON.stringify({ /* TODO: payload */ }, null, 2)
            : undefined
    })
      .then(r => {
        jsLog(`[Response Status] ${r.status} ${r.statusText}`);
        const hdrs = {};
        r.headers.forEach((v,k) => hdrs[k]=v);
        jsLog(`[Response Headers] ${JSON.stringify(hdrs, null, 2)}`);
        return r.text().then(text => ({ status: r.status, body: text }));
      })
      .then(obj => {
        jsLog('[Response Body]');
        jsLog(obj.body);
        try {
          resEl.textContent = JSON.stringify(JSON.parse(obj.body), null, 2);
        } catch {
          resEl.textContent = obj.body;
        }
      })
      .catch(err => {
        jsLog(`Proxy error: ${err.message}`);
        resEl.textContent = `Error: ${err.message}`;
      });
  }

  // Generic HEAD-request health-check
  function checkConn(url, dotEl, name) {
    jsLog(`Checking ${name} → ${url}`);
    fetch(url, { method: 'HEAD' })
      .then(r => {
        if (r.ok) {
          dotEl.classList.add('ok');
          jsLog(`${name} HEAD OK`);
        } else {
          throw new Error(`HTTP ${r.status}`);
        }
      })
      .catch(err => {
        dotEl.classList.add('error');
        jsLog(`${name} HEAD ERROR: ${err.message}`);
      });
  }
})();
