/*!
 * js/app.js
 * ------------------------------------------------------
 * Renders one card per endpoint (window.allEndpoints),
 * fetches OAuth token, handles connectivity checks,
 * drill-down modal, and JS Debug logging.
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // === JS Debug Setup ===
  const debugMode  = window.debugMode === true || window.debugMode === 'true';
  const debugPanel = debugMode ? document.getElementById('debug-panel') : null;

  function jsLog(msg) {
    if (!debugPanel) return;
    const line = document.createElement('div');
    line.className = 'debug-log-line';
    line.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
    debugPanel.appendChild(line);
    debugPanel.scrollTop = debugPanel.scrollHeight;
  }

  if (debugMode) {
    window.onerror = (msg, src, ln, col) => jsLog(`Error: ${msg} at ${src}:${ln}:${col}`);
    const origErr = console.error;
    console.error = (...args) => {
      jsLog('Console.error: ' + args.join(' '));
      origErr.apply(console, args);
    };
  }

  // === DOM refs ===
  const dbDot      = document.getElementById('dbStatus');
  const apiDot     = document.getElementById('apiStatus');
  const cardsView  = document.getElementById('cardsViewport');
  const modal      = document.getElementById('modal');
  const modalBody  = document.getElementById('modalBody');
  const modalClose = document.getElementById('modalClose');

  document.addEventListener('DOMContentLoaded', () => {
    // 1) Fetch the OAuth2 token
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
      .catch(err => jsLog('Token fetch failed: ' + err.message));

    // 2) Health checks
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // 3) Render cards
    renderAllCards();

    // 4) Modal close
    modalClose.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => {
      if (e.target === modal) modal.style.display = 'none';
    });
  });

  /**
   * Render one Glass-morphic card per endpoint.
   */
  function renderAllCards() {
    cardsView.innerHTML = '';
    const endpoints = window.allEndpoints || [];
    jsLog(`Rendering ${endpoints.length} endpoint cards`);
    endpoints.forEach(ep => {
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

  /**
   * Open drill-down modal showing details and a Try-It stub.
   */
  function openModal(ep) {
    modalBody.innerHTML = `
      <h2>${ep.method} ${ep.path}</h2>
      <p><strong>Summary:</strong> ${ep.summary || '(none)'}</p>
      <p><strong>Description:</strong> ${ep.description || '(none)'}</p>
      <button id="tryBtn">Try It</button>
      <pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn').addEventListener('click', () => tryIt(ep));
    modal.style.display = 'flex';
    jsLog(`Opened modal for ${ep.method} ${ep.path}`);
  }

  /**
   * Stub for invoking the live endpoint.
   */
  function tryIt(ep) {
    const resEl = document.getElementById('tryResult');
    if (!window.apiToken) {
      jsLog('Cannot call API: no token');
      resEl.textContent = 'No API token available.';
      return;
    }
    const url = window.apiBaseUrl.replace(/\/$/, '') + ep.path;
    jsLog(`Trying endpoint: ${ep.method} ${url}`);
    fetch(url, {
      method: ep.method,
      headers: {
        'Authorization': `Bearer ${window.apiToken}`,
        'Accept':        'application/json',
        'Content-Type':  'application/json'
      }
      // TODO: add request body for POST calls
    })
      .then(r => r.json().then(data => ({ status: r.status, data })))
      .then(obj => {
        resEl.textContent = JSON.stringify(obj, null, 2);
        jsLog(`TryIt success: ${ep.method} ${ep.path}`);
      })
      .catch(err => {
        resEl.textContent = 'Error: ' + err.message;
        jsLog(`TryIt error: ${err.message}`);
      });
  }

  /**
   * Generic HEAD-request connectivity check.
   */
  function checkConn(url, dotEl, name) {
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
