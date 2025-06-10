/*!
 * js/app.js
 * ------------------------------------------------------
 * Dynamically groups ~540 endpoints by role, renders
 * a gorgeous selector to switch roles, displays each
 * role’s endpoints as cards, and embeds a full‐featured
 * modal + TryIt proxy + enhanced Debug Console.
 * ------------------------------------------------------
 */
(function(){
  'use strict';

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

  // Styled logging
  function jsLog(msg, type='info') {
    if (!debugContent) return;
    const line = document.createElement('div');
    line.className = `debug-log-line ${type}`;
    const ts = `<span class="debug-timestamp">[${new Date().toLocaleTimeString()}]</span>`;
    const iconMap = {
      error:'❌', warning:'⚠️', success:'✅',
      info:'ℹ️', request:'📤', response:'📥'
    };
    line.innerHTML = `${ts}${iconMap[type]||'ℹ️'} ${msg}`;
    debugContent.appendChild(line);
    debugContent.scrollTop = debugContent.scrollHeight;
    if (debugContent.children.length > 200) debugContent.removeChild(debugContent.firstChild);
  }

  // Global error capture
  window.addEventListener('error', e => jsLog(`${e.message} at ${e.filename}:${e.lineno}`, 'error'));
  window.addEventListener('unhandledrejection', e => jsLog(`Promise Rejection: ${e.reason}`, 'error'));
  console.error = (...args) => { jsLog('Console.error: '+args.join(' '),'error'); };

  document.addEventListener('DOMContentLoaded', ()=>{
    jsLog('App initialized','success');

    // Define roles
    const roles = ['Developer','Admin','Dealer','Service','Sales','Accounting','Guest'];
    // Map endpoints to roles via prefix logic
    const endpoints = window.allEndpoints || [];
    const roleGroups = {
      Developer: endpoints.filter(e=> e.path.startsWith('/ApiClient')),
      Admin:     endpoints.filter(e=> e.path.startsWith('/Analytics') || e.path.startsWith('/ApiClient') || e.path.startsWith('/Account/GetAccounts') || e.path.startsWith('/Account/UpdateProfile')),
      Dealer:    endpoints.filter(e=> ['/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'].some(p=> e.path.startsWith(p)) || e.path.startsWith('/Analytics')),
      Service:   endpoints.filter(e=> e.path.startsWith('/AlertLimit') || ['/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'].some(p=> e.path.startsWith(p))),
      Sales:     endpoints.filter(e=> e.path.startsWith('/Analytics') || ['/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'].some(p=> e.path.startsWith(p))),
      Accounting:endpoints.filter(e=> e.path.startsWith('/Analytics') || ['/Alert/List','/Contract/List','/Customer/List','/Device/List','/MeterReading/List','/SupplyItem/List'].some(p=> e.path.startsWith(p))),
      Guest:     endpoints.filter(e=> ['/Account/GetProfile','/Account/Logout','/Account/UpdateProfile'].includes(e.path))
    };

    // Populate dropdown
    roles.forEach(r=>{
      const opt = document.createElement('option');
      opt.value = opt.textContent = r;
      roleSelect.appendChild(opt);
    });
    roleSelect.addEventListener('change', ()=> {
      renderRole(roleSelect.value);
      jsLog(`Switched to role ${roleSelect.value}`,'info');
    });
    renderRole(roles[0]); // initial

    // Fetch token
    jsLog('Fetching token…','request');
    fetch('get-token.php')
      .then(r=>r.json())
      .then(json=>{
        if(json.access_token){ window.apiToken=json.access_token; jsLog('Token acquired','success'); }
        else jsLog('Token error: '+(json.error||'unknown'),'error');
      })
      .catch(err=>jsLog('Token fetch failed: '+err.message,'error'));

    // Health checks
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // Toggle & Clear debug
    toggleBtn.addEventListener('click',()=>{
      const hidden = debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden?'Show Debug':'Hide Debug';
      toggleBtn.classList.toggle('panel-hidden', hidden);
      document.body.style.paddingBottom = hidden? '0':'220px';
      jsLog(`Debug ${hidden?'hidden':'shown'}`,'info');
    });
    clearBtn.addEventListener('click',()=>{
      debugContent.innerHTML=''; jsLog('Cleared debug log','info');
    });

    // Modal close
    modalClose.addEventListener('click',()=>modal.style.display='none');
    modal.addEventListener('click',e=>{ if(e.target===modal) modal.style.display='none'; });
  });

  // Render endpoints for a given role
  function renderRole(role) {
    cardsView.innerHTML='';
    (window.roleGroups||{})[role].forEach(ep=>{
      const c = document.createElement('div'); c.className='card';
      c.innerHTML=`<h3>${ep.method} ${ep.path}</h3><p class="summary">${ep.summary}</p>`;
      c.addEventListener('click',()=>openModal(ep));
      cardsView.appendChild(c);
    });
  }

  // Open modal + Try It
  function openModal(ep) {
    modalBody.innerHTML=`
      <h2>${ep.method} ${ep.path}</h2>
      <p><strong>Summary:</strong> ${ep.summary}</p>
      <p><strong>Description:</strong> ${ep.description}</p>
      <button id="tryBtn">Try It</button><pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn').onclick = ()=>tryIt(ep);
    modal.style.display='flex';
    jsLog(`Modal open: ${ep.method} ${ep.path}`,'info');
  }

  // Proxy call
  function tryIt(ep) {
    const res = document.getElementById('tryResult');
    if(!window.apiToken){ jsLog('No token','error'); return res.textContent='No token'; }
    const url=`api-proxy.php?method=${encodeURIComponent(ep.method)}&path=${encodeURIComponent(ep.path)}`;
    jsLog(`[Req] ${ep.method} ${ep.path}`,'request');
    jsLog(`[Proxy] ${url}`,'request');
    fetch(url,{method:ep.method,headers:{'Accept':'application/json','Content-Type':'application/json'},
      body:ep.method==='POST'?JSON.stringify({/*payload*/}):undefined})
    .then(r=>{
      jsLog(`[Stat] ${r.status}`,'response');
      const hdrs={}; r.headers.forEach((v,k)=>hdrs[k]=v);
      jsLog(`[Hdrs] ${JSON.stringify(hdrs)}`,'response');
      return r.text().then(b=>({body:b}));
    })
    .then(o=>{
      jsLog('[Body]', 'response'); jsLog(o.body,'response');
      try{res.textContent=JSON.stringify(JSON.parse(o.body),null,2)}catch{res.textContent=o.body}
    })
    .catch(e=>{ jsLog(`Err: ${e.message}`,'error'); res.textContent=e.message; });
  }

  // Health-check
  function checkConn(url,dot,name) {
    jsLog(`Check ${name}`,'info');
    fetch(url,{method:'HEAD'})
    .then(r=>{
      if(r.ok){dot.classList.add('ok');jsLog(`${name} OK`,'success')}
      else throw new Error(r.status);
    })
    .catch(e=>{dot.classList.add('error');jsLog(`${name} ERR: ${e.message}`,'error')});
  }

  // Expose for debugging
  window.jsLog = jsLog;
})();
