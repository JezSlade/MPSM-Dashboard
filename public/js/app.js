/*!
 * public/js/app.js
 * ------------------------------------------------------
 * OAuth2 token + refresh, Customer dropdown (mirroring
 * Working.php), role sidebar, cards & Try-It proxy.
 * ------------------------------------------------------
 */
(async function(){
  'use strict';

  // … [other DOM refs and globals remain unchanged] …

  // 1) Fetch token + schedule refresh → then load customers
  async function fetchToken() {
    jsLog('Fetching token…','request');
    let text;
    try {
      const resp = await fetch('get-token.php');
      text = await resp.text();
    } catch (err) {
      jsLog('Network token error: '+err.message,'error');
      return;
    }
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      jsLog('Invalid token JSON: '+text,'error');
      return;
    }
    if (!data.access_token) {
      jsLog('Token endpoint error: '+(data.error||'no access_token'),'error');
      return;
    }
    apiToken = data.access_token;
    jsLog('Token acquired','success');
    // now load customers with correct payload & headers
    await loadCustomers();
    const expires = Number(data.expires_in) || 3600;
    setTimeout(fetchToken, Math.max(expires - 60, 10)*1000);
  }

  // 2) loadCustomers() exactly as in Working.php (callGetCustomers) :contentReference[oaicite:0]{index=0}
  async function loadCustomers() {
    if (!apiToken) {
      jsLog('Cannot load customers without token','error');
      return;
    }
    jsLog('Loading customers…','request');
    try {
      const resp = await fetch('api-proxy.php?method=POST&path=Customer/GetCustomers', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
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
      jsLog('[Customer Raw Response]', 'response');
      jsLog(raw, 'response');

      let json;
      try {
        json = JSON.parse(raw);
      } catch (err) {
        jsLog('Customer JSON parse error: '+err.message,'error');
        return;
      }

      // unwrap the Result array
      const list = Array.isArray(json.Result) ? json.Result : [];
      jsLog(`Unwrapped ${list.length} customers`, 'success');

      customerList.innerHTML = '';
      list.forEach(c => {
        const opt = document.createElement('option');
        opt.value      = c.customerName;
        opt.dataset.id = c.customerId;
        customerList.appendChild(opt);
      });
      jsLog('Customers loaded successfully','success');
    } catch (err) {
      jsLog('Customer load failed: '+err.message,'error');
    }
  }

  // … [the rest of app.js remains unchanged] …

  // kick it off
  document.addEventListener('DOMContentLoaded', () => {
    fetchToken();
    // … sidebar init, role switching, etc. …
  });

})();
