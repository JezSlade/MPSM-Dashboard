/*!
 * public/js/app.js
 * ------------------------------------------------------
 * Builds a left-sidebar icon menu for roles,
 * renders cards, powers Try-It proxy, and logs
 * into the Debug Panel. Modal closes via X,
 * backdrop click, or Escape key.
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // ─── DOM References ───────────────────────────────────────────────────────────
  const sidebar      = document.getElementById('sidebar');
  const debugPanel   = document.getElementById('debug-panel');
  const debugContent = document.getElementById('debug-content');
  const toggleBtn    = document.getElementById('toggleDebug');
  const clearBtn     = document.getElementById('debugClear');
  const dbDot        = document.getElementById('dbStatus');
  const apiDot       = document.getElementById('apiStatus');
  const cardsView    = document.getElementById('cardsViewport');
  const modal        = document.getElementById('modal');
  const modalBody    = document.getElementById('modalBody');
  const modalClose   = document.getElementById('modalClose');

  // ─── Icon SVGs per Role ───────────────────────────────────────────────────────
  const icons = {
    Developer:  '<svg viewBox="0 0 20 20"><path d="M16 18l..."/></svg>',
    Admin:      '<svg viewBox="0 0 20 20"><path d="M9 17v2..."/></svg>',
    Dealer:     '<svg viewBox="0 0 20 20"><path d="M3 10h4..."/></svg>',
    Service:    '<svg viewBox="0 0 20 20"><circle cx="10"..."/></svg>',
    Sales:      '<svg viewBox="0 0 20 20"><path d="M3 15l6..."/></svg>',
    Accounting: '<svg viewBox="0 0 20 20"><rect x="3" y="3"..."/></svg>',
    Guest:      '<svg viewBox="0 0 20 20"><circle cx="10"..."/></svg>'
  };

  // ─── Logger into Debug Panel ─────────────────────────────────────────────────
  function jsLog(msg, type='info') {
    const line = document.createElement('div');
    line.className = `debug-log-line ${type}`;
    const ts = `<span class="debug-timestamp">[${new Date().toLocaleTimeString()}]</span>`;
    line.innerHTML = ts + msg;
    debugContent.appendChild(line);
    debugContent.scrollTop = debugContent.scrollHeight;
  }

  // ─── Build roleGroups: map each role to its allowed endpoints ─────────────────
  const mappings   = window.roleMappings  || {};
  const endpoints  = window.allEndpoints  || [];
  const roleGroups = {};
  Object.entries(mappings).forEach(([role, paths]) => {
    roleGroups[role] = endpoints.filter(ep => paths.includes(ep.path));
  });

  // ─── Initialization ──────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', () => {
    jsLog('App initialized','success');

    // Populate the sidebar with role icons
    Object.keys(roleGroups).forEach((role, i) => {
      const btn = document.createElement('button');
      btn.className    = 'role-btn';
      btn.dataset.role = role;
      btn.title        = role;
      btn.innerHTML    = icons[role] || '';
      btn.addEventListener('click', () => {
        document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderRole(role);
      });
      sidebar.appendChild(btn);
      if (i === 0) btn.classList.add('active');
    });

    // Render the first role by default
    renderRole(Object.keys(roleGroups)[0]);

    // ─── Fetch OAuth token, then load customers ─────────────────────────────────
    jsLog('Fetching API token…','request');
    fetch('get-token.php')
      .then(r => r.json())
      .then(json => {
        if (json.access_token) {
          window.apiToken = json.access_token;
          jsLog('Token acquired','success');
          jsLog('Loading customers…','request');
          return fetch(
            'api-proxy.php?method=POST&path=' + encodeURIComponent('Customer/GetCustomers'),
            {
              method: 'POST',
              headers: {'Content-Type':'application/json'},
              body: JSON.stringify({
                DealerCode: window.dealerCode,
                Code:       null,
                HasHpSds:   null,
                FilterText: null,
                PageNumber: 1,
                PageRows:   2147483647,
                SortColumn: 'Id',
                SortOrder:  0
              })
            }
          );
        } else {
          jsLog('Token error','error');
          throw new Error('Token error');
        }
      })
      .then(r => r.json())
      .then(data => {
        window.customers = data.Result || [];
        const list = document.getElementById('customerList');
        list.innerHTML = '';
        window.customers.forEach(c => {
          const opt = document.createElement('option');
          opt.value = `${c.Code} – ${c.Description}`;
          list.appendChild(opt);
        });
        jsLog(`Loaded ${window.customers.length} customers`,'success');
      })
      .catch(err => jsLog('Customer fetch error: '+err.message,'error'));

    // ─── Wire up the customer search input ─────────────────────────────────────
    const customerInput = document.getElementById('customerInput');
    if (customerInput) {
      customerInput.addEventListener('input', () => {
        const val = customerInput.value;
        jsLog(`Customer input: ${val}`,'info');
        const sel = (window.customers||[]).find(
          c => `${c.Code} – ${c.Description}` === val
        );
        if (sel) {
          window.selectedCustomer = sel.Code;
          jsLog(`Customer selected: ${sel.Description} (${sel.Code})`,'success');
          renderRole(Object.keys(roleGroups)[0]);
        }
      });
    } else {
      jsLog('customerInput element not found','warning');
    }

    // ─── Health checks for DB & API ────────────────────────────────────────────
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // ─── Debug panel controls ───────────────────────────────────────────────────
    toggleBtn.addEventListener('click', () => {
      const hidden = debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden ? 'Show Debug' : 'Hide Debug';
      jsLog(`Debug panel ${hidden?'hidden':'shown'}`,'info');
    });
    clearBtn.addEventListener('click', () => {
      debugContent.innerHTML = '';
      jsLog('Cleared debug log','info');
    });

    // ─── Modal close controls ──────────────────────────────────────────────────
    modalClose.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => {
      if (e.target === modal) modal.style.display = 'none';
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') modal.style.display = 'none';
    });
  });

  // ─── Render cards for a given role ──────────────────────────────────────────
  function renderRole(role) {
    cardsView.innerHTML = '';
    const group = roleGroups[role] || [];
    jsLog(`Rendering ${group.length} cards for ${role}`,'success');
    group.forEach(ep => {
      const c = document.createElement('div');
      c.className = 'card';
      c.innerHTML = `<h3>${ep.method} ${ep.path}</h3><p>${ep.summary}</p>`;
      c.addEventListener('click', () => openModal(ep));
      cardsView.appendChild(c);
    });
  }

  // ─── Open “Try-It” modal for an endpoint ────────────────────────────────────
  function openModal(ep) {
    modalBody.innerHTML = `
      <h2>Try: ${ep.method} ${ep.path}</h2>
      <button id="tryBtn" class="btn">Execute</button>
      <pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn').addEventListener('click', () => tryIt(ep));
    modal.style.display = 'flex';
    jsLog(`Modal opened for ${ep.method} ${ep.path}`,'info');
  }

  // ─── Execute the Try-It request ────────────────────────────────────────────
  function tryIt(ep) {
    const res = document.getElementById('tryResult');
    if (!window.apiToken) {
      jsLog('No token','error');
      return res.textContent = 'No API token';
    }
    const url = `api-proxy.php?method=${encodeURIComponent(ep.method)}&path=${encodeURIComponent(ep.path)}`;
    jsLog(`[Request] ${ep.method} ${ep.path}`,'request');
    fetch(url, {
      method: ep.method,
      headers: {'Accept': 'application/json'}
    })
    .then(r => r.json().then(data => ({status: r.status, data})))
    .then(o => {
      res.textContent = JSON.stringify(o, null, 2);
      jsLog('TryIt success','success');
    })
    .catch(err => {
      jsLog(`Error: ${err.message}`,'error');
      res.textContent = err.message;
    });
  }

  // ─── Simple HEAD request health check ───────────────────────────────────────
  function checkConn(url, dot, name) {
    fetch(url, {method: 'HEAD'})
      .then(r => {
        if (r.ok) {
          dot.classList.add('ok');
          jsLog(`${name} OK`,'success');
        } else {
          throw new Error(r.status);
        }
      })
      .catch(err => {
        dot.classList.add('error');
        jsLog(`${name} ERROR: ${err.message}`,'error');
      });
  }

  window.jsLog = jsLog;
})();
