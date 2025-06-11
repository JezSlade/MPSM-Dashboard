/*!
 * public/js/app.js
 * ------------------------------------------------------
 * Builds a left-sidebar icon menu for roles,
 * renders cards, powers Try-It proxy, and logs
 * into the Debug Panel. Modal closes via X,
 * backdrop, and Escape.
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // DOM refs
  const sidebar    = document.getElementById('sidebar');
  const debugPanel = document.getElementById('debug-panel');
  const debugContent = document.getElementById('debug-content');
  const toggleBtn  = document.getElementById('toggleDebug');
  const clearBtn   = document.getElementById('debugClear');
  const dbDot      = document.getElementById('dbStatus');
  const apiDot     = document.getElementById('apiStatus');
  const cardsView  = document.getElementById('cardsViewport');
  const modal      = document.getElementById('modal');
  const modalBody  = document.getElementById('modalBody');
  const modalClose = document.getElementById('modalClose');

  // Icon SVGs for each role
  const icons = {
    Developer:  '<svg viewBox="0 0 20 20"><path d="M16 18l6-6-6-6M8 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
    Admin:      '<svg viewBox="0 0 20 20"><path d="M9 17v2a2 2 0 002 2h2a2 2 0 002-2v-2m-6-4h6m-6-4h6" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
    Dealer:     '<svg viewBox="0 0 20 20"><path d="M3 10h4l3-6 4 12h4" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
    Service:    '<svg viewBox="0 0 20 20"><circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="2" fill="none"/><path d="M2 10a8 8 0 0116 0" stroke="currentColor" stroke-width="2" fill="none"/></svg>',
    Sales:      '<svg viewBox="0 0 20 20"><path d="M3 15l6-6 4 4 4-8" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
    Accounting: '<svg viewBox="0 0 20 20"><rect x="3" y="3" width="14" height="4" fill="none" stroke="currentColor" stroke-width="2"/><rect x="3" y="9" width="14" height="4" fill="none" stroke="currentColor" stroke-width="2"/><rect x="3" y="15" width="14" height="2" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
    Guest:      '<svg viewBox="0 0 20 20"><circle cx="10" cy="6" r="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M2 18a8 8 0 0116 0" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
  };

  // Logger
  function jsLog(msg, type='info') {
    const line = document.createElement('div');
    line.className = `debug-log-line ${type}`;
    const ts = `<span class="debug-timestamp">[${new Date().toLocaleTimeString()}]</span>`;
    const iconsMap = { error:'❌', warning:'⚠️', success:'✅', info:'ℹ️', request:'📤', response:'📥' };
    line.innerHTML = `${ts}${iconsMap[type]||'ℹ️'} ${msg}`;
    debugContent.append(line);
    debugContent.scrollTop = debugContent.scrollHeight;
    while(debugContent.children.length>200) debugContent.removeChild(debugContent.firstChild);
  }
  console.error = (...args)=> jsLog('Console.error: '+args.join(' '),'error');

  // Build roleGroups
  const mappings   = window.roleMappings || {};
  const endpoints  = window.allEndpoints  || [];
  const roleGroups = {};
  Object.entries(mappings).forEach(([role, paths])=>{
    roleGroups[role] = endpoints.filter(ep=> paths.includes(ep.path));
  });

  document.addEventListener('DOMContentLoaded',()=>{
    jsLog('App initialized','success');

    // Populate sidebar
    Object.keys(roleGroups).forEach((role,i)=>{
      const btn = document.createElement('button');
      btn.className = 'role-btn';
      btn.dataset.role = role;
      btn.title = role;
      btn.innerHTML = icons[role];
      btn.addEventListener('click', ()=>{
        document.querySelectorAll('.role-btn').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        renderRole(role);
        jsLog(`Role switched to ${role}`,'info');
      });
      sidebar.append(btn);
      if (i===0) btn.classList.add('active');
    });

    // Render initial role
    renderRole(Object.keys(roleGroups)[0]);

    // Fetch token
    jsLog('Fetching API token…','request');
    fetch('get-token.php').then(r=>r.json()).then(json=>{
      if(json.access_token){window.apiToken=json.access_token; jsLog('Token acquired','success')}
      else jsLog('Token error','error');
    }).catch(err=>jsLog('Token fetch failed: '+err.message,'error'));

    // Health checks
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // Debug controls
    toggleBtn.addEventListener('click',()=>{
      const hidden = debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden?'Show Debug':'Hide Debug';
      jsLog(`Debug panel ${hidden?'hidden':'shown'}`,'info');
    });
    clearBtn.addEventListener('click',()=>{
      debugContent.innerHTML=''; jsLog('Cleared debug log','info');
    });

    // Modal close
    modalClose.addEventListener('click',()=> modal.style.display='none');
    modal.addEventListener('click',e=>{ if(e.target===modal) modal.style.display='none';});
    document.addEventListener('keydown',e=>{ if(e.key==='Escape') modal.style.display='none'; });
  });

  function renderRole(role) {
    cardsView.innerHTML='';
    const group = roleGroups[role]||[];
    jsLog(`Rendering ${group.length} cards for ${role}`,'success');
    group.forEach(ep=>{
      const c = document.createElement('div'); c.className='card';
      c.innerHTML = `<h3>${ep.method} ${ep.path}</h3><p>${ep.summary}</p>`;
      c.addEventListener('click',()=>openModal(ep));
      cardsView.append(c);
    });
  }

  function openModal(ep) {
    modalBody.innerHTML=`
      <h2>${ep.method} ${ep.path}</h2>
      <p><strong>Summary:</strong> ${ep.summary}</p>
      <p><strong>Description:</strong> ${ep.description}</p>
      <button id="tryBtn" class="btn">Try It</button>
      <pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn').addEventListener('click',()=>tryIt(ep));
    modal.style.display='flex'; jsLog(`Modal opened for ${ep.method} ${ep.path}`,'info');
  }

  function tryIt(ep) {
    const res = document.getElementById('tryResult');
    if(!window.apiToken){jsLog('No token','error'); return res.textContent='No API token';}
    const url = `api-proxy.php?method=${encodeURIComponent(ep.method)}&path=${encodeURIComponent(ep.path)}`;
    jsLog(`[Request] ${ep.method} ${ep.path}`,'request');
    fetch(url,{method:ep.method,headers:{'Accept':'application/json'}})
      .then(r=>r.json().then(data=>({status:r.status,data})))
      .then(o=>{res.textContent=JSON.stringify(o,null,2); jsLog('TryIt success','success');})
      .catch(err=>{jsLog(`Error: ${err.message}`,'error');res.textContent=err.message;});
  }

  function checkConn(url,dot,name) {
    fetch(url,{method:'HEAD'}).then(r=>{
      if(r.ok){dot.classList.add('ok');jsLog(`${name} OK`,'success')} 
      else throw new Error(r.status);
    }).catch(err=>{dot.classList.add('error'); jsLog(`${name} ERROR: ${err.message}`,'error');});
  }

  window.jsLog = jsLog;
})();
