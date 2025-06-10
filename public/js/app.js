/*!
 * public/js/app.js
 * ------------------------------------------------------
 * Implements:
 * 1) Token fetch & auto-refresh
 * 2) Customer dropdown (searchable)
 * 3) Developer-only debug panel
 * 4) Role sidebar & card rendering
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // DOM refs
  const sidebar      = document.getElementById('sidebar');
  const headerRight  = document.querySelector('.header-right');
  const toggleBtn    = document.getElementById('toggleDebug');
  const debugPanel   = document.getElementById('debug-panel');
  const debugContent = document.getElementById('debug-content');
  const clearBtn     = document.getElementById('debugClear');
  const dbDot        = document.getElementById('dbStatus');
  const apiDot       = document.getElementById('apiStatus');
  const cardsView    = document.getElementById('cardsViewport');
  const modal        = document.getElementById('modal');
  const modalBody    = document.getElementById('modalBody');
  const modalClose   = document.getElementById('modalClose');
  const customerSelect = document.getElementById('customerSelect');
  const customerList   = document.getElementById('customerList');

  // Globals
  let apiToken = null;
  let tokenExpiry = 0;
  let currentRole = null;
  let currentCustomer = null;

  // Logging
  function jsLog(msg,type='info'){
    if (!debugContent) return;
    const line=document.createElement('div');
    line.className=`debug-log-line ${type}`;
    const ts=`<span class="debug-timestamp">[${new Date().toLocaleTimeString()}]</span>`;
    const iconMap={error:'❌',warning:'⚠️',success:'✅',info:'ℹ️',request:'📤',response:'📥'};
    line.innerHTML=`${ts}${iconMap[type]||'ℹ️'} ${msg}`;
    debugContent.append(line);
    debugContent.scrollTop=debugContent.scrollHeight;
    while(debugContent.children.length>200) debugContent.removeChild(debugContent.firstChild);
  }
  console.error=(...a)=>jsLog('Console.error: '+a.join(' '),'error');

  // Token management
  async function fetchToken(){
    jsLog('Fetching token…','request');
    const r = await fetch('get-token.php');
    const j = await r.json();
    apiToken = j.access_token;
    tokenExpiry = Date.now() + (j.expires_in||3600)*1000;
    jsLog('Token acquired','success');
    // Refresh 1 minute before expiry
    setTimeout(fetchToken, (j.expires_in - 60)*1000);
  }

  // Fetch customers for constant dealer code
  async function loadCustomers(){
    if(!apiToken) return;
    jsLog('Loading customers…','request');
    const r = await fetch(`api-proxy.php?method=POST&path=Customer/List`,{
      method:'POST',headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ dealerCode: window.DEALER_CODE })
    });
    const data = await r.json();
    jsLog('Customers loaded','success');
    customerList.innerHTML='';
    data.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.customerName; 
      opt.dataset.id = c.customerId;
      customerList.append(opt);
    });
  }

  document.addEventListener('DOMContentLoaded',()=>{
    // 1) Token + refresh
    fetchToken();

    // 2) Sidebar & Roles
    const mappings = window.roleMappings||{};
    const endpoints = window.allEndpoints||{};
    Object.keys(mappings).forEach(role=>{
      const btn = document.createElement('button');
      btn.className='role-btn'; btn.dataset.role=role;
      btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none"
         viewBox="0 0 20 20" stroke="currentColor">
         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
           d="M10 2a8 8 0 100 16 8 8 0 000-16z"/>
       </svg>`;
      btn.title=role;
      btn.addEventListener('click', ()=>{
        // activate
        document.querySelectorAll('.role-btn').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        currentRole=role;
        renderRole(role);
        // Show Debug only for Developer
        if(role==='Developer'){ toggleBtn.style.display='inline-block'; debugPanel.style.display='block'; }
        else { toggleBtn.style.display='none'; debugPanel.style.display='none'; }
      });
      sidebar.append(btn);
    });
    // Click first role
    sidebar.querySelector('.role-btn').click();

    // 3) Customer selector
    customerSelect.addEventListener('input', ()=>{
      const val = customerSelect.value;
      const option = Array.from(customerList.options).find(o=>o.value===val);
      currentCustomer = option ? option.dataset.id : null;
      jsLog(`Selected customer: ${currentCustomer}`,'info');
      renderRole(currentRole);
    });
    // Load customers once token is ready
    setTimeout(loadCustomers, 2000);

    // 4) Debug toggle
    toggleBtn.addEventListener('click', ()=>{
      const hidden=debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden?'Show Debug':'Hide Debug';
      jsLog(`Debug ${hidden?'hidden':'visible'}`,'info');
    });
    clearBtn.addEventListener('click', ()=>{ debugContent.innerHTML=''; jsLog('Cleared debug log','info'); });

    // 5) Modal close
    modalClose.addEventListener('click',()=>modal.style.display='none');
    modal.addEventListener('click',e=>{ if(e.target===modal) modal.style.display='none';});
    document.addEventListener('keydown',e=>{ if(e.key==='Escape'){ modal.style.display='none'; jsLog('Modal closed','info'); }});
  });

  // Render cards filtered by currentRole & currentCustomer
  function renderRole(role){
    cardsView.innerHTML='';
    const paths = window.roleMappings[role]||[];
    jsLog(`Rendering ${paths.length} cards for ${role}`,'info');
    paths.forEach(path=>{
      // find endpoint metadata
      const ep = window.allEndpoints.find(e=>e.path===path);
      if(!ep) return;
      const card = document.createElement('div');
      card.className='card';
      card.innerHTML=`
        <h3>${ep.method} ${ep.path}</h3>
        <p>${ep.summary}</p>
      `;
      card.addEventListener('click', ()=> openModal(ep));
      cardsView.append(card);
    });
  }

  // Modal & Try-It
  async function tryIt(ep){
    const res = document.getElementById('tryResult');
    if(!apiToken){ jsLog('No token','error'); return res.textContent='No token'; }
    const payload = {};
    if(currentCustomer) payload.customerId = currentCustomer;
    jsLog(`[Request] ${ep.method} ${ep.path}`,'request');
    const r=await fetch(`api-proxy.php?method=${encodeURIComponent(ep.method)}&path=${encodeURIComponent(ep.path)}`,{
      method:ep.method,headers:{'Content-Type':'application/json'},body:ep.method==='POST'?JSON.stringify(payload):undefined
    });
    const text = await r.text();
    res.textContent = text;
    jsLog(`[Response] ${r.status}`,'response');
  }
  function openModal(ep){
    modalBody.innerHTML=`
      <h2>${ep.method} ${ep.path}</h2>
      <p><strong>Summary:</strong> ${ep.summary}</p>
      <button id="tryBtn" class="btn">Try It</button>
      <pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn').addEventListener('click',()=>tryIt(ep));
    modal.style.display='flex';
    jsLog(`Modal opened for ${ep.method}`,'info');
  }

  // Health-check
  function checkConn(url,dot,name){
    fetch(url,{method:'HEAD'}).then(r=>{
      if(r.ok){ dot.classList.add('ok'); jsLog(`${name} OK`,'success'); }
      else throw new Error(r.status);
    }).catch(err=>{ dot.classList.add('error'); jsLog(`${name} ERROR: ${err.message}`,'error'); });
  }
  window.jsLog=jsLog;
})();
