/*!
 * js/app.js
 * ------------------------------------------------------
 * Renders cards from window.allEndpoints,
 * fetches OAuth token, proxies API calls via api-proxy.php,
 * and logs **every** error, request, response, health-check
 * into the enhanced Debug Panel which can be toggled/cleared.
 * ------------------------------------------------------
 */
(function(){
  'use strict';

  // DOM refs
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

  // Utility: styled log
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
    // cap lines
    while(debugContent.children.length>200) {
      debugContent.removeChild(debugContent.firstChild);
    }
  }

  // Global JS errors
  window.addEventListener('error', e => jsLog(`${e.message} at ${e.filename}:${e.lineno}`, 'error'));
  window.addEventListener('unhandledrejection', e => jsLog(`Promise Rejection: ${e.reason}`, 'error'));
  const origErr = console.error;
  console.error = (...args) => { jsLog('Console.error: '+args.join(' '), 'error'); origErr.apply(console,args); };
  const origWarn = console.warn;
  console.warn = (...args) => { jsLog('Console.warn: '+args.join(' '), 'warning'); origWarn.apply(console,args); };

  // On DOM ready
  document.addEventListener('DOMContentLoaded',()=>{
    jsLog('Application initialized', 'success');
    
    // Toggle Debug Panel
    toggleBtn.addEventListener('click',()=>{
      const hidden = debugPanel.classList.toggle('hidden');
      toggleBtn.textContent = hidden ? 'Show Debug' : 'Hide Debug';
      toggleBtn.classList.toggle('panel-hidden', hidden);
      document.body.style.paddingBottom = hidden ? '0' : '220px';
      jsLog(`Debug panel ${hidden?'hidden':'visible'}`, 'info');
    });

    // Clear log
    clearBtn.addEventListener('click',()=>{
      debugContent.innerHTML='';
      jsLog('Debug log cleared', 'info');
    });

    // Fetch token
    jsLog('Fetching API token…','request');
    fetch('get-token.php')
      .then(r=>r.json())
      .then(json=>{
        if(json.access_token){
          window.apiToken=json.access_token;
          jsLog('API token acquired','success');
        } else {
          jsLog('Token error: '+(json.error||'unknown'),'error');
        }
      })
      .catch(err=> jsLog('Token fetch failed: '+err.message,'error'));

    // Health checks
    checkConn('db-status.php', dbDot, 'DB');
    checkConn('api-status.php', apiDot, 'API');

    // Render cards
    renderAllCards();

    // Modal handlers
    modalClose.addEventListener('click',()=>modal.style.display='none');
    modal.addEventListener('click',e=>{ if(e.target===modal) modal.style.display='none'; });
  });

  // Render cards
  function renderAllCards(){
    cardsView.innerHTML='';
    const eps = window.allEndpoints||[];
    jsLog(`Rendering ${eps.length} endpoint cards`,'info');
    eps.forEach(ep=>{
      const card=document.createElement('div');
      card.className='card';
      card.innerHTML=`<h3>${ep.method} ${ep.path}</h3><p class="summary">${ep.summary||''}</p>`;
      card.addEventListener('click',()=>openModal(ep));
      cardsView.appendChild(card);
    });
  }

  // Open modal + Try It
  function openModal(ep){
    modalBody.innerHTML=`
      <h2>${ep.method} ${ep.path}</h2>
      <p><strong>Summary:</strong> ${ep.summary||'(none)'}</p>
      <p><strong>Description:</strong> ${ep.description||'(none)'}</p>
      <button id="tryBtn">Try It</button>
      <pre id="tryResult"></pre>
    `;
    document.getElementById('tryBtn').addEventListener('click',()=>tryIt(ep));
    modal.style.display='flex';
    jsLog(`Opened modal for ${ep.method} ${ep.path}`,'info');
  }

  // Proxy call
  function tryIt(ep){
    const resEl=document.getElementById('tryResult');
    if(!window.apiToken){
      jsLog('Cannot call API: no token','error');
      resEl.textContent='No API token available.';
      return;
    }
    const method=ep.method, path=ep.path;
    const url=`api-proxy.php?method=${encodeURIComponent(method)}&path=${encodeURIComponent(path)}`;
    jsLog(`[Request] ${method} ${path}`,'request');
    jsLog(`[Proxy URL] ${url}`,'request');
    jsLog(`[Headers] ${JSON.stringify({
      Authorization:`Bearer ${window.apiToken}`,
      Accept:'application/json',
      'Content-Type':'application/json'
    },null,2)}`,'request');

    fetch(url,{
      method:method, headers:{'Accept':'application/json','Content-Type':'application/json'},
      body: method==='POST'?JSON.stringify({/* TODO: payload */},null,2):undefined
    })
    .then(r=>{
      jsLog(`[Response Status] ${r.status} ${r.statusText}`,'response');
      const hdrs={};
      r.headers.forEach((v,k)=>hdrs[k]=v);
      jsLog(`[Response Headers] ${JSON.stringify(hdrs,null,2)}`,'response');
      return r.text().then(body=>({status:r.status,body}));
    })
    .then(obj=>{
      jsLog('[Response Body]','response');
      jsLog(obj.body,'response');
      try{resEl.textContent=JSON.stringify(JSON.parse(obj.body),null,2)}
      catch{resEl.textContent=obj.body}
    })
    .catch(err=>{
      jsLog(`Proxy error: ${err.message}`,'error');
      resEl.textContent=`Error: ${err.message}`;
    });
  }

  // Health-check
  function checkConn(url,dot,name){
    jsLog(`Checking ${name} → ${url}`,'info');
    fetch(url,{method:'HEAD'})
      .then(r=>{
        if(r.ok){dot.classList.add('ok');jsLog(`${name} HEAD OK`,'success')}
        else throw new Error(`HTTP ${r.status}`)
      })
      .catch(err=>{
        dot.classList.add('error');jsLog(`${name} HEAD ERROR: ${err.message}`,'error')
      });
  }

  // Expose
  window.jsLog = jsLog;
})();
