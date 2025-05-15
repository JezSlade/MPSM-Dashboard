// v1.0.0 [Init: Device Fetch + Table Display]
import { eventBus } from '../core/event-bus.js';
import { store } from '../core/store.js';

// Triggered when a customer is selected
eventBus.on("customer:selected", async (customerId) => {
  try {
    const res = await fetch('./get_devices.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ CustomerId: customerId })
    });
    const data = await res.json();

    if (!Array.isArray(data?.Result)) throw new Error("Invalid device data");

    eventBus.emit("devices:loaded", data.Result);
    renderTable(data.Result);
  } catch (err) {
    console.error("Device fetch failed", err);
    window.DebugPanel?.logError("Device fetch failed", err);
  }
});

function renderTable(devices) {
  let app = document.getElementById("devices");
  if (!app) {
    app = document.createElement("div");
    app.id = "devices";
    document.getElementById("app").appendChild(app);
  }

  const rows = devices.map(d => `
    <tr>
      <td>${d.AssetNumber || ''}</td>
      <td>${d.IPAddress || ''}</td>
      <td>${d.Model || ''}</td>
      <td>${d.AlertOnDisplay || ''}</td>
    </tr>
  `).join("");

  app.innerHTML = `
    <h3>Devices</h3>
    <table class="device-table">
      <thead>
        <tr><th>Asset</th><th>IP</th><th>Model</th><th>Alert</th></tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>
  `;
}
