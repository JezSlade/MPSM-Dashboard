/*!
 * public/js/app.js
 * ------------------------------------------------------
 * Handles:
 * 1) Robust OAuth2 token fetch + auto-refresh
 * 2) Customer dropdown population post-token
 * 3) Developer-only debug panel toggle
 * 4) Sidebar with visible icons (fill+stroke=currentColor)
 * 5) Card rendering & Try-It proxy
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // DOM refs
  const sidebar       = document.getElementById('sidebar');
  const toggleBtn     = document.getElementById('toggleDebug');
  const debugPanel    = document.getElementById('debug-panel');
  const debugContent  = document.getElementById('debug-content');
  const clearBtn      = document.getElementById('debugClear');
  const dbDot         = document.getElementById('dbStatus');
  const apiDot        = document.getElementById('apiStatus');
  const cardsView     = document.getElementById('cardsViewport');
  const modal         = document.getElementById('modal');
  const modalBody     = document.getElementById('modalBody');
  const modalClose    = document.getElementById('modalClose');
  const customerInput = document.getElementById('customerSelect');
  const customerList  = document.getElementById('customerList');

  // Globals
  let apiToken = null;
  let tokenExpiry = 0;
  let currentRole = null;
  let currentCustomer = null;

  // Icon map for sidebar
  const icons = {
    Developer:  '<svg class="icon" viewBox="0 0 20 20"><path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/></svg>',
    Admin:      '<svg class="icon" viewBox="0 0 20 20"><path d="M9 17v2a2 2 0 002 2h2a2 2 0 002-2v-2M4 9h12"/></svg>',
    Dealer:     '<svg class="icon" viewBox="0 0 20 20"><path d="M3 10h4l3-6 4 12h4"/></svg>',
    Service:    '<svg class="icon" viewBox="0 0 20 20"><circle cx="10" cy="10" r="3"/><path d="M2 10a8 8 0 0116 0"/></svg>',
    Sales:      '<svg class="icon" viewBox="0 0 20 20"><path d="M3 15l6-6 4 4 4-8"/></svg>',
    Accounting: '<svg class="icon" viewBox="0 0 20 20"><rect x="3" y="3" width="14" height="4"/><rect x="3" y="9" width="14" height="4"/><rect x="3" y="15" width="14" height="2"/></svg>',
    Guest:      '<svg class="icon" viewBox="0 0 20 20"><circle cx="10" cy="6" r="3"/><path d="M2 18a8 8 0 0116 0"/></svg>'
  };

  // Styled logger
  function jsLog(msg, type='info') {
    if (!debugContent) return;
    const line = document.createElement('div');
    line.className = `debug-log-line ${type}`;
    const ts = `<span class="debug-timestamp">[${new Date().toLocaleTimeString()}]</span>`;
    const iconsMap = { error:'❌', warning:'⚠️', success:'✅', info:'ℹ️', request:'📤', response:'📥' };
    line.innerHTML = `${ts}${iconsMap[type]||'ℹ️'} ${msg}`;
    debugContent.appendChild(line);
    debugContent.scrollTop = debugContent.scrollHeight;
    while (debugContent.children.length > 200) {
      debugContent.removeChild(debugContent.firstChild);
    }
  }
  console.error = (...args) => jsLog('Console.error: '+args.join(' '), 'error');

  // 1) Fetch token + retry logic
  async function fetchToken() {
    jsLog('Fetching token…','request');
    let text;
    try {
      const resp = await fetch('get-token.php');
      text = await resp.text();
    } catch (err) {
      jsLog('Network error fetching token: '+err.message,'error');
      return;
    }
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      jsLog('Token JSON parse error, response: '+text,'error');
      return;
    }
    if (!data.access_token) {
      jsLog('No access_token in response','error');
      return;
    }
    apiToken = data.access_token;
    const expires = Number(data.expires_in) || 3600;
    tokenExpiry = Date.now() + expires*1000;
    jsLog('Token acquired','success');
    await loadCustomers();
    // schedule refresh 60s before expiry
    const delay = Math.max(expires - 60, 10)*1000;
    setTimeout(fetchToken, delay);
  }

  // 2) Load Customer List
  async function loadCustomers() {
    if (!apiToken) return;
    jsLog('Loading customers…','request');
    try {
      const resp = await fetch('api-proxy.php?method=POST&path=Customer/List', {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify({ dealerCode: window.DEALER_CODE })
      });
      const customers = await resp.json();
      jsLog('Customers loaded','success');
      customerList.innerHTML = '';
      customers.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.customerName;
        opt.dataset.id = c.customerId;
        customerList.append(opt);
      });
    } catch (err) {
      jsLog('Customer load failed: '+err.message,'error');
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Start token fetch & customer load
    fetchToken();

    // 3) Build sidebar with icons
    const mappings = window.roleMappings || {};
    Object.keys(mappings).forEach((role, idx) => {
      const btn = document.createElement('button');
      btn.className = 'role-btn';
      btn.dataset.role = role;
      btn.innerHTML = icons[role] || '';
      btn.title = role;
      btn.addEventListener('click', () => {
        document.querySelectorAll('.role-btn').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        currentRole = role;
        renderRole(role);
        // Debug only for Developer
        if (role === 'Developer') {
          toggleBtn.style.display = 'inline-block';
          debugPanel.style.display = 'block';
        } else {
          toggleBtn.style.display = 'none';
          debugPanel.style.display = 'none';
        }
      });
      sidebar.appendChild(btn);
      if (idx === 0) btn.classList.add('active');
    });
    // Initial render
    currentRole = Object.keys(mappings)[0];
    renderRole(currentRole);

    // 4) Customer selection
    customerInput.addEventListener('input', () => {
      const val = customerInput.value;
      const opt = Array.from(customerList.options).find(o => o.value === val);
      currentCustomer = opt ? opt.dataset.id : null;
      jsLog('Selected customer: '+currentCustomer,'info');
      renderRole(currentRole);
    });

    // 5) Debug toggle & clear
    toggleBtn.addEventListener('click', () => {
      const hidden = debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden ? 'Show Debug' : 'Hide Debug';
      jsLog(`Debug panel ${hidden ? 'hidden' : 'shown'}`,'info');
    });
    clearBtn.addEventListener('click', () => {
      debugContent.innerHTML = '';
      jsLog('Cleared debug log','info');
    });

    // 6) Modal close handlers
    modalClose.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        modal.style.display = 'none';
        jsLog('Modal closed via Escape','info');
      }
    });
  });

  // Render cards for the selected role
  function renderRole(role) {
    cardsView.innerHTML = '';
    const paths = window.roleMappings[role] || [];
    jsLog(`Rendering ${paths.length} cards for ${role}`,'success');
    paths.forEach(path => {
      const ep = window.allEndpoints.find(e => e.path === path);
      if (!ep) return;
      const card = document.createElement('div');
      card.className = 'card';
      card.innerHTML = `
        <h3>${ep.method} ${ep.path}</h3>
        <p>${ep.summary}</p>
      `;
      card.addEventListener('click', () => openModal(ep));
      cardsView.appendChild(card);
    });
  }

  // Open modal & Try-It
  function openModal(ep) {
    modalBody.innerHTML = `
      <h2>${ep.method} ${ep.path}</h2>
      <p><strong>Summary:</strong> ${ep.summary}</p>
      <button id="tryBtn" class="btn">Try It</button>
      <pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn').addEventListener('click', () => tryIt(ep));
    modal.style.display = 'flex';
    jsLog(`Modal opened for ${ep.method} ${ep.path}`,'info');
  }

  async function tryIt(ep) {
    const resEl = document.getElementById('tryResult');
    if (!apiToken) {
      jsLog('No API token','error');
      return void(resEl.textContent = 'No API token available.');
    }
    const payload = {};
    if (currentCustomer) payload.customerId = currentCustomer;
    jsLog(`[Request] ${ep.method} ${ep.path}`,'request');
    try {
      const r = await fetch(`api-proxy.php?method=${encodeURIComponent(ep.method)}&path=${encodeURIComponent(ep.path)}`, {
        method: ep.method,
        headers: { 'Content-Type':'application/json' },
        body: ep.method === 'POST' ? JSON.stringify(payload) : undefined
      });
      const text = await r.text();
      jsLog(`[Response status] ${r.status}`,'response');
      resEl.textContent = text;
    } catch (err) {
      jsLog(`TryIt error: ${err.message}`,'error');
    }
  }

  // Health-checks
  function checkConn(url, dot, name) {
    fetch(url, { method: 'HEAD' }).then(r => {
      if (r.ok) {
        dot.classList.add('ok');
        jsLog(`${name} OK`,'success');
      } else {
        throw new Error(r.status);
      }
    }).catch(err => {
      dot.classList.add('error');
      jsLog(`${name} ERROR: ${err.message}`,'error');
    });
  }

  window.jsLog = jsLog;

})();
