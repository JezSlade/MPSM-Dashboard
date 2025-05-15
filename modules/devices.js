// v1.0.2 [Add: Full Key Dump + Reset on Customer Change]
import { eventBus } from '../core/event-bus.js';
import { store } from '../core/store.js';

// Triggered on new customer selection
eventBus.on("customer:selected", async (customerId) => {
  clearTable(); // wipe previous content
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

// Clear previous device UI
function clearTable() {
  const el = document.getElementById("devices");
  if (el) el.remove();
}

// Render full device table with all fields
function renderTable(devices) {
  const root = document.getElementById("app");
  if (!root) return;

  let container = document.createElement("div");
  container.id = "devices";
  root.appendChild(container);

  if (!devices.length) {
    container.innerHTML = "<p>No devices found for this customer.</p>";
    return;
  }

  const keys = Object.keys(devices[0]);
  const headers = keys.map(k => `<th>${k}</th>`).join("");
  const rows = devices.map(d => `
    <tr>${keys.map(k => `<td>${d[k] ?? ''}</td>`).join("")}</tr>
  `).join("");

  container.innerHTML = `
    <h3>Devices (${devices.length})</h3>
    <div style="overflow-x:auto;">
      <table class="device-table wide">
        <thead><tr>${headers}</tr></thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
  `;
}
