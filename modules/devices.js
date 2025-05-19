// v1.1.0 [Fix: Ensure Target Container Exists for renderTable()]
import { eventBus } from '../core/event-bus.js';
import { store } from '../core/store.js';
import { renderTable } from '../core/render-table.js';

eventBus.on("customer:selected", async (customerId) => {
  try {
    const res = await fetch('./get_devices.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ customerId })
    });
    const data = await res.json();

    if (!Array.isArray(data?.Result)) throw new Error("Invalid device list");

    store.set("devices", data.Result);
    eventBus.emit("devices:loaded", data.Result);

    let deviceContainer = document.getElementById("device-table");
    if (!deviceContainer) {
      deviceContainer = document.createElement("div");
      deviceContainer.id = "device-table";
      document.getElementById("app")?.appendChild(deviceContainer) ||
        document.body.appendChild(deviceContainer);
    }

    renderTable("device-table", data.Result);
  } catch (err) {
    console.error("Device fetch failed", err);
    window.DebugPanel?.logError("Device fetch failed", err);
  }
});
