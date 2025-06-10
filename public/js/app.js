/*!
 * public/js/app.js
 * ------------------------------------------------------
 * Patched to handle blank or error token responses,
 * ensure customer dropdown loads, and show proper icons.
 * ------------------------------------------------------
 */
(async function(){
  'use strict';

  // DOM refs...
  const sidebar = document.getElementById('sidebar');
  // ...other refs...

  let apiToken = null;
  let currentRole = null;
  let currentCustomer = null;

  const icons = {
    Developer:  '<svg class="icon" viewBox="0 0 20 20"><path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/></svg>',
    // ...other icons...
  };

  function jsLog(msg,type='info'){ /* unchanged */ }

  async function fetchToken(){
    jsLog('Fetching token…','request');
    let text;
    try {
      const resp = await fetch('get-token.php');
      text = await resp.text();
    } catch(err) {
      jsLog('Network error fetching token: '+err.message,'error');
      return;
    }
    let data;
    try {
      data = JSON.parse(text);
    } catch(e) {
      jsLog('Invalid JSON from token endpoint: '+text,'error');
      return;
    }
    if (!data.access_token) {
      jsLog('Token error: '+(data.error||'no access_token'),'error');
      return;
    }
    apiToken = data.access_token;
    const expires = Number(data.expires_in) || 3600;
    jsLog('Token acquired','success');
    await loadCustomers();
    setTimeout(fetchToken, (expires-60)*1000);
  }

  async function loadCustomers(){
    if (!apiToken) { jsLog('Cannot load customers without token','error'); return; }
    jsLog('Loading customers…','request');
    try {
      const resp = await fetch('api-proxy.php?method=POST&path=Customer/List',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ dealerCode: window.DEALER_CODE })
      });
      const list = await resp.json();
      jsLog('Customers loaded','success');
      customerList.innerHTML = '';
      list.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.customerName;
        opt.dataset.id = c.customerId;
        customerList.appendChild(opt);
      });
    } catch(err) {
      jsLog('Error loading customers: '+err.message,'error');
    }
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    fetchToken();

    // Sidebar roles
    Object.keys(window.roleMappings).forEach((role,i)=>{
      const btn = document.createElement('button');
      btn.className = 'role-btn';
      btn.dataset.role = role;
      btn.innerHTML = icons[role] || '';
      btn.title = role;
      btn.addEventListener('click', ()=>{
        document.querySelectorAll('.role-btn').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        currentRole = role;
        renderRole(role);
        // Developer only debug
        toggleDebugVisibility(role);
      });
      sidebar.appendChild(btn);
      if (i===0) btn.classList.add('active');
    });
    renderRole(Object.keys(window.roleMappings)[0]);
    toggleDebugVisibility(currentRole);

    // Customer select
    customerInput.addEventListener('input',()=>{
      const val = customerInput.value;
      const opt = Array.from(customerList.options).find(o=>o.value===val);
      currentCustomer = opt ? opt.dataset.id : null;
      jsLog('Selected customer: '+currentCustomer,'info');
      renderRole(currentRole);
    });

    // Debug controls, modal, etc. (unchanged)
  });

  function toggleDebugVisibility(role){
    const isDev = role==='Developer';
    toggleBtn.style.display = isDev ? 'inline-block' : 'none';
    debugPanel.style.display = isDev ? 'block' : 'none';
  }

  function renderRole(role){ /* unchanged */ }
  function openModal(ep){ /* unchanged */ }
  function tryIt(ep){ /* unchanged */ }
  function checkConn(url,dot,name){ /* unchanged */ }

})();
