// public/js/app.js
/*!
 * public/js/app.js
 * ------------------------------------------------------
 * Builds sidebar icons, handles search, fetches data,
 * powers Try-It proxy, and logs into the Debug Panel.
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // DOM refs
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

  // Icon SVGs for each role
  const icons = {
    Developer:  '<svg viewBox="0 0 20 20"><path d="M16 18l..."/></svg>',
    Admin:      '<svg viewBox="0 0 20 20"><path d="M9 17v2..."/></svg>',
    Dealer:     '<svg viewBox="0 0 20 20"><path d="M3 10h4..."/></svg>',
    Service:    '<svg viewBox="0 0 20 20"><circle cx="10"..."/></svg>',
    Sales:      '<svg viewBox="0 0 20 20"><path d="M3 15l6..."/></svg>',
    Accounting: '<svg viewBox="0 0 20 20"><rect x="3" y="3"..."/></svg>',
    Guest:      '<svg viewBox="0 0 20 20"><circle cx="10"..."/></svg>',
  };

  // Simple logger into Debug Panel
  function jsLog(msg, type='info') {
    const line = document.createElement('div');
    line.className = `debug-log-line ${type}`;
    const ts = `<span class="debug-timestamp">[${new Date().toLocaleTimeString()}]</span>`;
    line.innerHTML = ts + msg;
    debugContent.appendChild(line);
    debugContent.scrollTop = debugContent.scrollHeight;
  }

  // Build roleGroups from injected data
  const mappings  = window.roleMappings || {};
  const endpoints = window.allEndpoints  || [];
  const roleGroups = {};
  Object.entries(mappings).forEach(([role, paths]) => {
    roleGroups[role] = endpoints.filter(ep => paths.includes(ep.path));
  });

  document.addEventListener('DOMContentLoaded', () => {
    jsLog('App initialized','success');

    // Populate sidebar icons
    Object.keys(roleGroups).forEach((role, i) => {
      const btn = document.createElement('button');
      btn.className = 'role-btn';
      btn.dataset.role = role;
      btn.title = role;
      btn.innerHTML = icons[role];
      btn.addEventListener('click', () => {
        document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderRole(role);
      });
      sidebar.appendChild(btn);
      if (i === 0) btn.classList.add('active');
    });

    // Initial view
    renderRole(Object.keys(roleGroups)[0]);

    // Customer search wiring
    const customerInput = document.getElementById('customerInput');
    if (customerInput) {
      customerInput.addEventListener('input', () => {
        const val = customerInput.value;
        jsLog(`Customer input: ${val}`,'info');
        const sel = (window.customers||[]).find(c => c.DealerDescription === val);
        if (sel) {
          window.selectedCustomer = sel.DealerCode;
          jsLog(`Customer selected: ${sel.DealerDescription} (${sel.DealerCode})`,'success');
          // Re-render default role for the selected customer
          renderRole(Object.keys(roleGroups)[0]);
        }
      });
    } else {
      jsLog('customerInput element not found','warning');
    }

    // Fetch OAuth token, then load customers
    jsLog('Fetching API token…','request');
    fetch('get-token.php')
      .then(r => r.json())
      .then(json => {
        if (json.access_token) {
          window.apiToken = json.access_token;
          jsLog('Token acquired','success');

          // Now load customers
          jsLog('Loading customers…','request');
          fetch(
            `api-proxy.php?method=${encodeURIComponent('POST')}` +
            `&path=${encodeURIComponent('Customer/GetCustomers')}`, {
              method: 'POST',
              headers: {'Content-Type':'application/json'},
              body: JSON.stringify({
                DealerCode:   window.dealerCode,
                Code:         null,
                HasHpSds:     null,
                FilterText:   null,
                PageNumber:   1,
                PageRows:     2147483647,
                SortColumn:   'Id',
                SortOrder:    0
              })
            }
          ).then(r => r.json())
           .then(data => {
             window.customers = data.Result || [];
             const list = document.getElementById('customerList');
             if (list) {
               window.customers.forEach(c => {
                 const opt = document.createElement('option');
                 opt.value      = c.DealerDescription;
                 opt.dataset.code = c.DealerCode;
                 list.appendChild(opt);
               });
               jsLog(`Loaded ${window.customers.length} customers`,'success');
             } else {
               jsLog('customerList datalist not found','warning');
             }
           })
           .catch(err => jsLog('Customer fetch error: '+err.message,'error'));

        } else {
          jsLog('Token error','error');
        }
      })
      .catch(err => jsLog('Token fetch failed: '+err.message,'error'));

    // Health checks
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // Debug toggles
    toggleBtn.addEventListener('click', () => {
      const hidden = debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden ? 'Show Debug' : 'Hide Debug';
      jsLog(`Debug panel ${hidden?'hidden':'shown'}`,'info');
    });
    clearBtn.addEventListener('click', () => {
      debugContent.innerHTML = '';
      jsLog('Cleared debug log','info');
    });

    // Modal close handlers
    modalClose.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') modal.style.display = 'none'; });
  });

  // Renders the cards for a given role, filtering by selectedCustomer if set
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

  // Try-It modal logic (unchanged)…
  function openModal(ep) { /* … */ }
  function tryIt(ep)     { /* … */ }
  function checkConn(u,d,n){ /* … */ }

  window.jsLog = jsLog;
})();
