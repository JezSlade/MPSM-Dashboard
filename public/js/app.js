/*!
 * public/js/app.js
 * ------------------------------------------------------
 * 1) jsLog at top
 * 2) Token fetch + customer load
 * 3) Sidebar roles with visible icons
 * 4) Customer dropdown on 'change' only
 * 5) Card rendering & Try-It proxy
 * 6) Developer-only debug panel
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // === 1) jsLog ===
  function jsLog(msg, type='info') {
    const debugContent = document.getElementById('debug-content');
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

  // === DOM refs & globals ===
  const sidebar       = document.getElementById('sidebar');
  const toggleBtn     = document.getElementById('toggleDebug');
  const debugPanel    = document.getElementById('debug-panel');
  const clearBtn      = document.getElementById('debugClear');
  const cardsView     = document.getElementById('cardsViewport');
  const modal         = document.getElementById('modal');
  const modalBody     = document.getElementById('modalBody');
  const modalClose    = document.getElementById('modalClose');
  const customerInput = document.getElementById('customerSelect');
  const customerList  = document.getElementById('customerList');

  let apiToken = null;
  let currentRole = null;
  let currentCustomer = null;

  // SVG icons
  const icons = {
    Developer:  '<svg class="icon" viewBox="0 0 20 20"><path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/></svg>',
    Admin:      '<svg class="icon" viewBox="0 0 20 20"><path d="M9 17v2a2 2 0 002 2h2a2 2 0 002-2v-2M4 9h12"/></svg>',
    Dealer:     '<svg class="icon" viewBox="0 0 20 20"><path d="M3 10h4l3-6 4 12h4"/></svg>',
    Service:    '<svg class="icon" viewBox="0 0 20 20"><circle cx="10" cy="10" r="3"/><path d="M2 10a8 8 0 0116 0"/></svg>',
    Sales:      '<svg class="icon" viewBox="0 0 20 20"><path d="M3 15l6-6 4 4 4-8"/></svg>',
    Accounting: '<svg class="icon" viewBox="0 0 20 20"><rect x="3" y="3" width="14" height="4"/><rect x="3" y="9" width="14" height="4"/><rect x="3" y="15" width="14" height="2"/></svg>',
    Guest:      '<svg class="icon" viewBox="0 0 20 20"><circle cx="10" cy="6" r="3"/><path d="M2 18a8 8 0 0116 0"/></svg>'
  };

  // === 2) Token & Customer Load ===
  async function fetchToken() {
    jsLog('Fetching token…','request');
    let raw;
    try {
      const resp = await fetch('get-token.php');
      raw = await resp.text();
    } catch (err) {
      jsLog('Network token error: '+err.message,'error');
      return;
    }
    let data;
    try {
      data = JSON.parse(raw);
    } catch(e) {
      jsLog('Invalid token JSON: '+raw,'error');
      return;
    }
    if (!data.access_token) {
      jsLog('Token error: '+(data.error||'no access_token'),'error');
      return;
    }
    apiToken = data.access_token;
    jsLog('Token acquired','success');
    await loadCustomers();
    const expires = Number(data.expires_in) || 3600;
    setTimeout(fetchToken, Math.max(expires - 60, 10)*1000);
  }

  async function loadCustomers() {
    if (!apiToken) { jsLog('No token—cannot load customers','error'); return; }
    jsLog('Loading customers…','request');
    try {
      const resp = await fetch('api-proxy.php?method=POST&path=Customer/GetCustomers', {
        method:'POST',
        headers:{ 'Accept':'application/json','Content-Type':'application/json' },
        body: JSON.stringify({
          DealerCode: window.DEALER_CODE,
          Code:       null,
          HasHpSds:   null,
          FilterText: null,
          PageNumber: 1,
          PageRows:   2147483647,
          SortColumn: 'Id',
          SortOrder:  0
        })
      });
      const raw = await resp.text();
      jsLog('[Customer Raw Response]','response');
      jsLog(raw,'response');
      let json;
      try {
        json = JSON.parse(raw);
      } catch(e) {
        jsLog('Customer JSON parse error: '+e.message,'error');
        return;
      }
      const list = Array.isArray(json.Result) ? json.Result : [];
      jsLog(`Unwrapped ${list.length} customers`,'success');
      customerList.innerHTML = '';
      list.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.Description;        // use Description for readability
        opt.dataset.id = c.Id;
        customerList.appendChild(opt);
      });
      jsLog('Customers loaded','success');
    } catch(err) {
      jsLog('Customer load failed: '+err.message,'error');
    }
  }

  // === 3) Init ===
  document.addEventListener('DOMContentLoaded', () => {
    fetchToken();

    // Sidebar
    const mappings = window.roleMappings||{};
    Object.keys(mappings).forEach((role,i)=>{
      const btn = document.createElement('button');
      btn.className='role-btn'; btn.dataset.role=role;
      btn.innerHTML=icons[role]||''; btn.title=role;
      btn.addEventListener('click', ()=>{
        document.querySelectorAll('.role-btn').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        currentRole=role; renderRole(role);
        // Debug only for Developer
        const isDev = role==='Developer';
        toggleBtn.style.display = isDev?'inline-block':'none';
        debugPanel.style.display= isDev?'block':'none';
      });
      if(i===0) btn.classList.add('active');
      sidebar.appendChild(btn);
    });
    currentRole = Object.keys(mappings)[0];
    renderRole(currentRole);

    // 4) Customer select on 'change' only
    customerInput.addEventListener('change', ()=>{
      const val = customerInput.value;
      const opt = Array.from(customerList.options).find(o=>o.value===val);
      if(opt){
        currentCustomer=opt.dataset.id;
        jsLog('Selected customer: '+currentCustomer,'info');
        renderRole(currentRole);
      }
    });

    // Debug toggle & clear
    toggleBtn.addEventListener('click', ()=>{
      const hidden=debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden?'Show Debug':'Hide Debug';
      jsLog(`Debug panel ${hidden?'hidden':'shown'}`,'info');
    });
    clearBtn.addEventListener('click', ()=>{ debugContent.innerHTML=''; jsLog('Cleared debug log','info'); });

    // Modal close
    modalClose.addEventListener('click', ()=>modal.style.display='none');
    modal.addEventListener('click', e=>{ if(e.target===modal) modal.style.display='none'; });
    document.addEventListener('keydown', e=>{ if(e.key==='Escape'){ modal.style.display='none'; jsLog('Modal closed','info'); }});
  });

  // === 4) Render Cards ===
  function renderRole(role){
    cardsView.innerHTML='';
    const paths=window.roleMappings[role]||[];
    jsLog(`Rendering ${paths.length} cards for ${role}`,'success');
    paths.forEach(path=>{
      const ep = window.allEndpoints.find(e=>e.path===path);
      if(!ep) return;
      const card = document.createElement('div');
      card.className='card';
      card.innerHTML=`<h3>${ep.method} ${ep.path}</h3><p>${ep.summary}</p>`;
      card.addEventListener('click', ()=>openModal(ep));
      cardsView.appendChild(card);
    });
  }

  // === 5) Modal & Try-It ===
  function openModal(ep){
    modalBody.innerHTML=`
      <h2>${ep.method} ${ep.path}</h2>
      <p><strong>Summary:</strong> ${ep.summary}</p>
      <button id="tryBtn" class="btn">Try It</button>
      <pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn').addEventListener('click', ()=>tryIt(ep));
    modal.style.display='flex'; jsLog(`Modal opened for ${ep.method}`,'info');
  }
  async function tryIt(ep){
    const resEl=document.getElementById('tryResult');
    if(!apiToken){ jsLog('No API token','error'); return void(resEl.textContent='No token'); }
    const payload={};
    if(currentCustomer) payload.customerId=currentCustomer;
    jsLog(`[Request] ${ep.method} ${ep.path}`,'request');
    try{
      const r=await fetch(`api-proxy.php?method=${encodeURIComponent(ep.method)}&path=${encodeURIComponent(ep.path)}`,{
        method:ep.method, headers:{'Content-Type':'application/json'},
        body:ep.method==='POST'?JSON.stringify(payload):undefined
      });
      const txt=await r.text();
      jsLog(`[Response status] ${r.status}`,'response');
      resEl.textContent=txt;
    }catch(err){
      jsLog(`TryIt error: ${err.message}`,'error');
    }
  }

})();
