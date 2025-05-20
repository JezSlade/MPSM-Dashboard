// modules/devices.js
// v2.2.6 [Fix: wire into existing get_devices.php + core renderTable]

import { debug }       from '../core/debug.js';
import { eventBus }    from '../core/event-bus.js';
import { get }         from '../core/dom.js';
import { renderTable } from '../core/render-table.js';

export function initDevices() {
  debug.log('Devices: initialization started');

  eventBus.on('customer:selected', async (customerCode) => {
    debug.log(`Devices: loading for customer ${customerCode}`);

    // Ensure our container exists
    let container = get('devices-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'devices-container';
      get('app').appendChild(container);
    }

    // Show a quick loading indicator
    container.innerHTML = '<p>Loading devices…</p>';

    try {
      // Call your PHP wrapper endpoint
      const res = await fetch('get_devices.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ customerId: customerCode })
      });

      if (!res.ok) {
        throw new Error(`HTTP ${res.status}`);
      }

      // Wrapper returns the same "Result" array shape :contentReference[oaicite:0]{index=0}:contentReference[oaicite:1]{index=1}
      const data = await res.json();
      if (!Array.isArray(data.Result)) {
        throw new Error('Unexpected payload');
      }

      debug.log(`Devices loaded: ${data.Result.length}`);

      // Delegate to your existing core render-table logic :contentReference[oaicite:2]{index=2}:contentReference[oaicite:3]{index=3}
      renderTable('devices-container', data.Result);
    } catch (err) {
      debug.error(`Devices load failed: ${err.message}`);
      container.innerHTML = `<p style="color:red;">Error loading devices: ${err.message}</p>`;
    }
  });
}
