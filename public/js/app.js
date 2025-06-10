/*!
 * public/js/app.js
 * ------------------------------------------------------
 * Groups endpoints by role, renders a styled dropdown &
 * uniform buttons, displays cards, powers Try-It proxy,
 * and logs into the enhanced Debug Panel (toggle/clear).
 * Modal closes via X, backdrop click, and Escape.
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
  const versionEl    = document.getElementById('versionDisplay');
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
    const icons = { error:'❌', warning:'⚠️', success:'✅', info:'ℹ️', request:'📤', response:'📥' };
    line.innerHTML = `${ts}${icons[type]||'ℹ️'} ${msg}`;
    debugContent.appendChild(line);
    debugContent.scrollTop = debugContent.scrollHeight;
    if (debugContent.children.length > 200) {
      debugContent.removeChild(debugContent.firstChild);
    }
  }

  // Global errors
  window.addEventListener('error', e => jsLog(`${e.message} at ${e.filename}:${e.lineno}`, 'error'));
  window.addEventListener('unhandledrejection', e => jsLog(`Promise Rejection: ${e.reason}`, 'error'));
  console.error = (...args) => jsLog('Console.error: '+args.join(' '), 'error');

  // Build roleGroups
  const mappings  = window.roleMappings || {};
  const endpoints = window.allEndpoints || [];
  const roleGroups= {};
  Object.entries(mappings).forEach(([role, paths]) => {
    roleGroups[role] = endpoints.filter(ep => paths.includes(ep.path));
  });

  document.addEventListener('DOMContentLoaded', () => {
    jsLog('App initialized','success');

    // Populate role selector
    Object.keys(roleGroups).forEach(role => {
      const o = document.createElement('option');
      o.value = o.textContent = role;
      roleSelect.appendChild(o);
    });
    roleSelect.value = Object.keys(roleGroups)[0];
    renderRole(roleSelect.value);
    roleSelect.addEventListener('change', () => {
      renderRole(roleSelect.value);
      jsLog(`Role switched to ${roleSelect.value}`,'info');
    });

    // Fetch token
    jsLog('Fetching API token…','request');
    fetch('get-token.php')
      .then(r => r.json())
      .then(json => {
        if (json.access_token) {
          window.apiToken = json.access_token;
          jsLog('Token acquired','success');
        } else {
          jsLog('Token error: ' + (json.error||'unknown'),'error');
        }
      })
      .catch(err => jsLog('Token fetch failed: ' + err.message,'error'));

    // Health checks
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // Debug panel toggle & clear
    toggleBtn.addEventListener('click', () => {
      const hidden = debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden ? 'Show Debug' : 'Hide Debug';
      toggleBtn.classList.toggle('panel-hidden', hidden);
      document.body.style.paddingBottom = hidden ? '0' : '220px';
      jsLog(`Debug panel ${hidden?'hidden':'shown'}`,'info');
    });
    clearBtn.addEventListener('click', () => {
      debugContent.innerHTML = '';
      jsLog('Cleared debug log','info');
    });

    // Modal close
    modalClose.addEventListener('click', ()=> modal.style.display='none');
    modal.addEventListener('click', e => {
      if (e.target === modal) modal.style.display = 'none';
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        modal.style.display = 'none';
        jsLog('Modal closed via Escape','info');
      }
    });
  });

  function renderRole(role) {
    cardsView.innerHTML = '';
    const group = roleGroups[role] || [];
    jsLog(`Rendering ${group.length} cards for ${role}`,'success');
    group.forEach(ep => {
      const card = document.createElement('div');
      card.className = 'card';
      card.innerHTML = `
        <h3>${ep.method} ${ep.path}</h3>
        <p class="summary">${ep.summary}</p>
      `;
      card.addEventListener('click', ()=> openModal(ep));
      cardsView.appendChild(card);
    });
  }

  function openModal(ep) {
    modalBody.innerHTML = `
      <h2>${ep.method} ${ep.path}</h2>
      <p><strong>Summary:</strong> ${ep.summary}</p>
      <p><strong>Description:</strong> ${ep.description}</p>
      <button id="tryBtn" class="btn">Try It</button>
      <pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn').addEventListener('click', ()=> tryIt(ep));
    modal.style.display = 'flex';
    jsLog(`Modal opened for ${ep.method} ${ep.path}`,'info');
  }

  function tryIt(ep) {
    const resEl = document.getElementById('tryResult');
    if (!window.apiToken) {
      jsLog('No API token','error');
      return void(resEl.textContent='No API token available.');
    }
    const url = `api-proxy.php?method=${encodeURIComponent(ep.method)}&path=${encodeURIComponent(ep.path)}`;
    jsLog(`[Request] ${ep.method} ${ep.path}`,'request');
    jsLog(`[Proxy URL] ${url}`,'request');

    fetch(url, {
      method: ep.method,
      headers: { 'Accept':'application/json','Content-Type':'application/json' },
      body: ep.method==='POST'?JSON.stringify({},null,2):undefined
    })
    .then(r => {
      jsLog(`[Response Status] ${r.status} ${r.statusText}`,'response');
      const hdrs={}; r.headers.forEach((v,k)=>hdrs[k]=v);
      jsLog(`[Response Headers] ${JSON.stringify(hdrs,null,2)}`,'response');
      return r.text().then(b=>({b}));
    })
    .then(o => {
      jsLog('[Response Body]','response'); jsLog(o.b,'response');
      try { resEl.textContent=JSON.stringify(JSON.parse(o.b),null,2); }
      catch { resEl.textContent=o.b; }
    })
    .catch(err => {
      jsLog(`Proxy error: ${err.message}`,'error');
      resEl.textContent=`Error: ${err.message}`;
    });
  }

  function checkConn(url,dot,name) {
    jsLog(`Checking ${name}`,'info');
    fetch(url,{method:'HEAD'})
    .then(r=>{
      if(r.ok){ dot.classList.add('ok'); jsLog(`${name} OK`,'success'); }
      else throw new Error(`HTTP ${r.status}`);
    })
    .catch(err=>{
      dot.classList.add('error'); jsLog(`${name} ERROR: ${err.message}`,'error');
    });
  }

  window.jsLog = jsLog;
})();
