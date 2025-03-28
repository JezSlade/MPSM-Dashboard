let DEBUG_MODE = true;

function logDebug(message, data = null) {
  const output = document.getElementById("debug-output");
  const timestamp = new Date().toISOString().split("T")[1].slice(0, 8);
  const fullMessage = `[${timestamp}] ${message}`;
  output.textContent += fullMessage + "\n";
  if (data) {
    output.textContent += JSON.stringify(data, null, 2) + "\n\n";
  }
  console.log(fullMessage);
  if (data) console.log(data);
}

function toggleDebug() {
  DEBUG_MODE = !DEBUG_MODE;
  document.getElementById("debug-toggle").textContent = "Debug: " + (DEBUG_MODE ? "ON" : "OFF");
  document.getElementById("debug-panel").style.display = DEBUG_MODE ? "block" : "none";
  if (!DEBUG_MODE) {
    document.getElementById("debug-output").textContent = "";
  }
}

document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("debug-toggle").addEventListener("click", toggleDebug);
  document.getElementById("debug-panel").style.display = "block";
  document.getElementById("debug-toggle").textContent = "Debug: ON";
  logDebug("🔧 Debug console active");

  fetch("static/devices.json")
    .then(res => {
      logDebug("📡 Fetching devices.json... Status: " + res.status);
      return res.json();
    })
    .then(json => {
      logDebug("✅ devices.json loaded", json);
      const tbody = document.getElementById("device-tbody");
      const timestamp = document.getElementById("timestamp");

      // Set the timestamp
      timestamp.textContent = json.timestamp || "unknown";

      // Render devices
      const devices = json.devices;
      tbody.innerHTML = "";

      if (!Array.isArray(devices) || devices.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4">No devices found.</td></tr>`;
        return;
      }

      devices.forEach(device => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${device.model || "N/A"}</td>
          <td>${device.ipAddress || "N/A"}</td>
          <td>${device.serialNumber || "N/A"}</td>
          <td>${device.assetNumber || "N/A"}</td>
        `;
        tbody.appendChild(row);
      });
    })
    .catch(err => {
      logDebug("❌ Failed to load devices.json", err);
      const tbody = document.getElementById("device-tbody");
      tbody.innerHTML = `<tr><td colspan="4">Error loading device data.</td></tr>`;
    });
});
