// v1.0.1 [Fix: Unterminated Template Literal in Device Overlay]
import { eventBus } from '../core/event-bus.js';
import { store } from '../core/store.js';

function createOverlay() {
  let overlay = document.getElementById("overlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.id = "overlay";
    overlay.innerHTML = '<div class="overlay-inner"><button class="close-btn">✕</button><div id="overlay-content">Loading...</div></div>';
    document.body.appendChild(overlay);
    overlay.querySelector(".close-btn").onclick = () => overlay.classList.remove("visible");
  }
  return overlay;
}

export function bindSEIDClicks(containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;

  container.querySelectorAll("td:first-child").forEach(td => {
    td.style.cursor = "pointer";
    td.onclick = async () => {
      const row = store.get("devices")?.find(r => r.SEID === td.textContent);
      if (!row || !row.Id) return;

      const overlay = createOverlay();
      overlay.classList.add("visible");

      try {
        const res = await fetch("./get_device_details.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ DeviceId: row.Id })
        });
        const data = await res.json();

        const out = formatDetailDisplay(row, data);
        document.getElementById("overlay-content").innerHTML = out;
      } catch (e) {
        document.getElementById("overlay-content").innerText = "Error loading details.";
        console.error(e);
      }
    };
  });
}

function formatDetailDisplay(row, detail) {
  const fields = [
    ["Brand", row.Product?.Brand],
    ["Model", row.Product?.Model],
    ["Serial", row.SerialNumber],
    ["IP", row.IpAddress],
    ["Firmware", row.Firmware],
    ["MAC", row.MacAddress]
  ];

  const supplies = (detail?.SuppliesInfo || []).map(s => `<li>${s.Name}: ${s.Value}</li>`).join("");
  const kits = (detail?.MaintenanceKitLevels || []).map(k => `<li>${k.Description}: ${k.Level}</li>`).join("");

  return `
    <h2>Device Detail</h2>
    <ul>${fields.map(([k,v]) => `<li><strong>${k}:</strong> ${v || '—'}</li>`).join("")}</ul>
    <hr/>
    <h3>Supplies</h3><ul>${supplies}</ul>
    <h3>Maintenance Kits</h3><ul>${kits}</ul>
  `;
}
