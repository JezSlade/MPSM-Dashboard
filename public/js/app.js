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
  const roleGroups = {};
  Object.entries(window.roleMappings || {}).forEach(([role, paths]) => {
    roleGroups[role] = (window.allEndpoints || []).filter(ep => paths.includes(ep.path));
  });

  document.addEventListener('DOMContentLoaded', () => {
    jsLog('App initialized','success');

    // Sidebar icons
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
    renderRole(Object.keys(roleGroups)[0]);

    // Fetch customers via SDK shape
    jsLog('Loading customers…','request');
    fetch('api-proxy.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        Url:     'Customer/GetCustomers',
        Request: {
          DealerCode: window.dealerCode,
          Code:       null,
          HasHpSds:   null,
          FilterText: null,
          PageNumber: 1,
          PageRows:   2147483647,
          SortColumn: 'Id',
          SortOrder:  0
        },
        Method: 'POST'
      })
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

    // Customer input binding
    const customerInput = document.getElementById('customerInput');
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

    // Health checks
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // Debug controls
    toggleBtn.addEventListener('click', () => {
      const hidden = debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden ? 'Show Debug' : 'Hide Debug';
      jsLog(`Debug panel ${hidden?'hidden':'shown'}`,'info');
    });
    clearBtn.addEventListener('click', () => {
      debugContent.innerHTML = '';
      jsLog('Cleared debug log','info');
    });

    // Modal close
    modalClose.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') modal.style.display = 'none'; });
  });

  // Render cards for a role
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

  // Try-It modal and execution (unchanged)…
  function openModal(ep){ /* … */ }
  function tryIt(ep){ /* … */ }
  function checkConn(u,d,n){ /* … */ }

  // Icon definitions (unchanged)…
  const icons = {
    Developer:  '<svg>…</svg>',
    Admin:      '<svg>…</svg>',
    Dealer:     '<svg>…</svg>',
    Service:    '<svg>…</svg>',
    Sales:      '<svg>…</svg>',
    Accounting: '<svg>…</svg>',
    Guest:      '<svg>…</svg>',
  };

  window.jsLog = jsLog;
})();
