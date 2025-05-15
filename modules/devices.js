// v1.0.1 [Fix: Render Valid Device Keys]
import { eventBus } from '../core/event-bus.js';
import { store } from '../core/store.js';

// When a customer is selected, fetch their devices
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
  const root = document.getElementById("app");
  if (!root) return;

  let container = document.getElementById("devices");
  if (!container) {
    container = document.createElement("div");
    container.id = "devices";
    root.appendChild(container);
  } else {
    container.innerHTML = "";
  }

  if (!devices.length) {
    container.innerHTML = "<p>No devices found for this customer.</p>";
    return;
  }

  // Auto-detect first 6 useful fields from first device
  const sample = devices[0];
  const keys = Object.keys(sample).slice(0, 6); // Adjust as needed

  const headers = keys.map(k => `<th>${k}</th>`).join("");
  const rows = devices.map(d => `
    <tr>${keys.map(k => `<td>${d[k] ?? ''}</td>`).join("")}</tr>
  `).join("");

  container.innerHTML = `
    <h3>Devices</h3>
    <table class="device-table">
      <thead><tr>${headers}</tr></thead>
      <tbody>${rows}</tbody>
    </table>
  `;
}
