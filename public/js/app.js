/*!
 * public/js/app.js
 * ------------------------------------------------------
 * Groups endpoints by role, renders a beautiful selector,
 * displays cards, powers the Try-It proxy, and logs everything
 * into an enhanced Debug Panel with toggle & clear.
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // DOM references
  const debugPanel   = document.getElementById('debug-panel');
  const debugContent = document.getElementById('debug-content');
  const toggleBtn    = document.getElementById('toggleDebug');
  const clearBtn     = document.getElementById('debugClear');
  const roleSelect   = document.getElementById('roleSelect');
  const dbDot        = document.getElementById('dbStatus');
  const apiDot       = document.getElementById('apiStatus');
  const cardsView    = document.getElementById('cardsViewport');
  const modal        = document.getElementById('modal');
  const modalBody    = document.getElementById('modalBody');
  const modalClose   = document.getElementById('modalClose');

  // Styled logger
  function jsLog(msg, type='info') {
    if (!debugContent) return;
    const line = document.createElement('div');
    line.className = `debug-log-line ${type}`;
    const ts = `<span class="debug-timestamp">[${new Date().toLocaleTimeString()}]</span>`;
    const icons = {
      error:    '❌',
      warning:  '⚠️',
      success:  '✅',
      info:     'ℹ️',
      request:  '📤',
      response: '📥'
    };
    line.innerHTML = `${ts}${icons[type]||'ℹ️'} ${msg}`;
    debugContent.appendChild(line);
    debugContent.scrollTop = debugContent.scrollHeight;
    // cap to 200 lines
    while (debugContent.children.length > 200) {
      debugContent.removeChild(debugContent.firstChild);
    }
  }

  // Global JS error capture
  window.addEventListener('error', e => jsLog(`${e.message} at ${e.filename}:${e.lineno}`, 'error'));
  window.addEventListener('unhandledrejection', e => jsLog(`Promise Rejection: ${e.reason}`, 'error'));
  const origConsoleError = console.error;
  console.error = function(...args) {
    jsLog('Console.error: ' + args.join(' '), 'error');
    origConsoleError.apply(console, args);
  };

  // Precompute roleGroups from mappings + endpoints
  const mappings = window.roleMappings || {};
  const endpoints = window.allEndpoints || [];
  const roleGroups = {};
  Object.entries(mappings).forEach(([role, paths]) => {
    roleGroups[role] = endpoints.filter(ep => paths.includes(ep.path));
  });

  document.addEventListener('DOMContentLoaded', () => {
    jsLog('Application initialized', 'success');

    // Populate the role dropdown
    const roles = Object.keys(roleGroups);
    roles.forEach(role => {
      const option = document.createElement('option');
      option.value = option.textContent = role;
      roleSelect.appendChild(option);
    });
    // Initial render
    roleSelect.value = roles[0];
    renderRole(roles[0]);
    roleSelect.addEventListener('change', () => {
      renderRole(roleSelect.value);
      jsLog(`Role switched to ${roleSelect.value}`, 'info');
    });

    // Fetch API token
    jsLog('Fetching API token…', 'request');
    fetch('get-token.php')
      .then(r => r.json())
      .then(json => {
        if (json.access_token) {
          window.apiToken = json.access_token;
          jsLog('API token acquired', 'success');
        } else {
          jsLog('Token error: ' + (json.error || 'unknown'), 'error');
        }
      })
      .catch(err => jsLog('Token fetch failed: ' + err.message, 'error'));

    // Health checks
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // Debug panel toggle & clear
    toggleBtn.addEventListener('click', () => {
      const hidden = debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden ? 'Show Debug' : 'Hide Debug';
      toggleBtn.classList.toggle('panel-hidden', hidden);
      document.body.style.paddingBottom = hidden ? '0' : '220px';
      jsLog(`Debug panel ${hidden ? 'hidden' : 'shown'}`, 'info');
    });
    clearBtn.addEventListener('click', () => {
      debugContent.innerHTML = '';
      jsLog('Cleared debug log', 'info');
    });

    // Modal close handlers
    modalClose.addEventListener('click', () => { modal.style.display = 'none'; });
    modal.addEventListener('click', e => {
      if (e.target === modal) modal.style.display = 'none';
    });
  });

  // Render all cards for the given role
  function renderRole(role) {
    cardsView.innerHTML = '';
    const group = roleGroups[role] || [];
    jsLog(`Rendering ${group.length} cards for ${role}`, 'success');
    group.forEach(ep => {
      const card = document.createElement('div');
      card.className = 'card';
      card.innerHTML = `
        <h3>${ep.method} ${ep.path}</h3>
        <p class="summary">${ep.summary}</p>
      `;
      card.addEventListener('click', () => openModal(ep));
      cardsView.appendChild(card);
    });
  }

  // Open modal and attach Try It button
  function openModal(ep) {
    modalBody.innerHTML = `
      <h2>${ep.method} ${ep.path}</h2>
      <p><strong>Summary:</strong> ${ep.summary}</p>
      <p><strong>Description:</strong> ${ep.description}</p>
      <button id="tryBtn">Try It</button>
      <pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn').addEventListener('click', () => tryIt(ep));
    modal.style.display = 'flex';
    jsLog(`Opened modal for ${ep.method} ${ep.path}`, 'info');
  }

  // Invoke the endpoint via our PHP proxy
  function tryIt(ep) {
    const resEl = document.getElementById('tryResult');
    if (!window.apiToken) {
      jsLog('Cannot call API: no token', 'error');
      return void (resEl.textContent = 'No API token available.');
    }
    const url = `api-proxy.php?method=${encodeURIComponent(ep.method)}&path=${encodeURIComponent(ep.path)}`;
    jsLog(`[Request] ${ep.method} ${ep.path}`, 'request');
    jsLog(`[Proxy URL] ${url}`, 'request');

    fetch(url, {
      method: ep.method,
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: ep.method === 'POST' ? JSON.stringify({}, null, 2) : undefined
    })
    .then(r => {
      jsLog(`[Response Status] ${r.status} ${r.statusText}`, 'response');
      const hdrs = {};
      r.headers.forEach((v, k) => hdrs[k] = v);
      jsLog(`[Response Headers] ${JSON.stringify(hdrs, null, 2)}`, 'response');
      return r.text().then(body => ({ body }));
    })
    .then(obj => {
      jsLog('[Response Body]', 'response');
      jsLog(obj.body, 'response');
      try {
        resEl.textContent = JSON.stringify(JSON.parse(obj.body), null, 2);
      } catch {
        resEl.textContent = obj.body;
      }
    })
    .catch(err => {
      jsLog(`Proxy error: ${err.message}`, 'error');
      resEl.textContent = `Error: ${err.message}`;
    });
  }

  // Generic HEAD-request health check
  function checkConn(url, dot, name) {
    jsLog(`Checking ${name}`, 'info');
    fetch(url, { method: 'HEAD' })
      .then(r => {
        if (r.ok) {
          dot.classList.add('ok');
          jsLog(`${name} OK`, 'success');
        } else {
          throw new Error(`HTTP ${r.status}`);
        }
      })
      .catch(err => {
        dot.classList.add('error');
        jsLog(`${name} ERROR: ${err.message}`, 'error');
      });
  }

  // Expose jsLog for manual use
  window.jsLog = jsLog;
})();
