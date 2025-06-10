/*!
 * js/app.js
 * ------------------------------------------------------
 * Renders cards by role, fetches OAuth token,
 * proxies API calls via api-proxy.php (no CORS),
 * and logs every detail into the enhanced Debug Panel:
 * toggleable, clearable, with colored icons.
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // DOM refs
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

  // Styled log
  function jsLog(msg, type='info') {
    if (!debugContent) return;
    const line = document.createElement('div');
    line.className = `debug-log-line ${type}`;
    const ts = `<span class="debug-timestamp">[${new Date().toLocaleTimeString()}]</span>`;
    const icon = {
      error:'❌', warning:'⚠️', success:'✅',
      info:'ℹ️', request:'📤', response:'📥'
    }[type] || 'ℹ️';
    line.innerHTML = `${ts}${icon} ${msg}`;
    debugContent.appendChild(line);
    debugContent.scrollTop = debugContent.scrollHeight;
    while(debugContent.children.length > 200) {
      debugContent.removeChild(debugContent.firstChild);
    }
  }

  // Global errors
  window.addEventListener('error', e => jsLog(`${e.message} at ${e.filename}:${e.lineno}`, 'error'));
  window.addEventListener('unhandledrejection', e => jsLog(`Rejection: ${e.reason}`, 'error'));
  const origErr = console.error;
  console.error = (...args) => { jsLog('Console.error: '+args.join(' '),'error'); origErr(...args); };
  const origWarn = console.warn;
  console.warn  = (...args) => { jsLog('Console.warn: '+args.join(' '),'warning'); origWarn(...args); };

  document.addEventListener('DOMContentLoaded', () => {
    jsLog('App initialized', 'success');

    // Populate role dropdown
    const roleMappings = window.roleMappings || {};
    Object.keys(roleMappings).forEach(role => {
      const o = document.createElement('option');
      o.value = o.textContent = role;
      roleSelect.appendChild(o);
    });
    roleSelect.value = Object.keys(roleMappings)[0] || '';
    roleSelect.addEventListener('change', () => {
      renderByRole(roleSelect.value);
      jsLog(`Role switched to ${roleSelect.value}`, 'info');
    });

    // Initial render
    renderByRole(roleSelect.value);

    // Fetch token
    jsLog('Fetching API token…', 'request');
    fetch('get-token.php')
      .then(r => r.json())
      .then(json => {
        if (json.access_token) {
          window.apiToken = json.access_token;
          jsLog('API token acquired', 'success');
        } else {
          jsLog('Token error: ' + (json.error||'unknown'), 'error');
        }
      })
      .catch(err => jsLog('Token fetch failed: '+err.message,'error'));

    // Health checks
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // Debug panel toggle
    toggleBtn.addEventListener('click', () => {
      const hidden = debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden ? 'Show Debug' : 'Hide Debug';
      toggleBtn.classList.toggle('panel-hidden', hidden);
      document.body.style.paddingBottom = hidden ? '0' : '220px';
      jsLog(`Debug panel ${hidden?'hidden':'shown'}`, 'info');
    });

    // Clear debug log
    clearBtn.addEventListener('click', () => {
      debugContent.innerHTML = '';
      jsLog('Debug log cleared', 'info');
    });

    // Modal close handlers
    modalClose.addEventListener('click', ()=> modal.style.display='none');
    modal.addEventListener('click', e => { if(e.target===modal) modal.style.display='none'; });
  });

  // Render cards for one role
  function renderByRole(role) {
    cardsView.innerHTML = '';
    const paths = window.roleMappings[role] || [];
    jsLog(`Rendering ${paths.length} endpoints for ${role}`, 'info');
    paths.forEach(path => {
      const ep = window.allEndpoints.find(e => e.path === path);
      if (!ep) {
        jsLog(`Missing endpoint ${path}`, 'warning');
        return;
      }
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

  // Open modal + Try It
  function openModal(ep) {
    modalBody.innerHTML = `
      <h2>${ep.method} ${ep.path}</h2>
      <p><strong>Summary:</strong> ${ep.summary}</p>
      <p><strong>Description:</strong> ${ep.description}</p>
      <button id="tryBtn">Try It</button><pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn').addEventListener('click', () => tryIt(ep));
    modal.style.display = 'flex';
    jsLog(`Opened modal for ${ep.method} ${ep.path}`, 'info');
  }

  // Proxy call
  function tryIt(ep) {
    const resEl = document.getElementById('tryResult');
    if (!window.apiToken) {
      jsLog('No API token', 'error');
      return void (resEl.textContent='No token');
    }
    const url = `api-proxy.php?method=${encodeURIComponent(ep.method)}&path=${encodeURIComponent(ep.path)}`;
    jsLog(`[Request] ${ep.method} ${ep.path}`, 'request');
    jsLog(`[Proxy] ${url}`, 'request');

    fetch(url, {
      method: ep.method,
      headers: { 'Accept':'application/json','Content-Type':'application/json' },
      body: ep.method==='POST'?JSON.stringify({/*payload*/},null,2):undefined
    })
      .then(r => {
        jsLog(`[Status] ${r.status} ${r.statusText}`, 'response');
        const hdrs = {}; r.headers.forEach((v,k)=>hdrs[k]=v);
        jsLog(`[Headers] ${JSON.stringify(hdrs,null,2)}`, 'response');
        return r.text().then(b=>({body:b}));
      })
      .then(obj => {
        jsLog('[Body]', 'response');
        jsLog(obj.body, 'response');
        try { resEl.textContent = JSON.stringify(JSON.parse(obj.body), null, 2); }
        catch { resEl.textContent = obj.body; }
      })
      .catch(err => {
        jsLog(`Network error: ${err.message}`, 'error');
        resEl.textContent = `Error: ${err.message}`;
      });
  }

  // Health-check
  function checkConn(url, dot, name) {
    jsLog(`Checking ${name}`, 'info');
    fetch(url, { method:'HEAD' })
      .then(r => {
        if (r.ok) { dot.classList.add('ok'); jsLog(`${name} OK`, 'success'); }
        else throw new Error(`HTTP ${r.status}`);
      })
      .catch(err => { dot.classList.add('error'); jsLog(`${name} ERROR: ${err.message}`, 'error'); });
  }

  // Expose for console
  window.jsLog = jsLog;
})();
