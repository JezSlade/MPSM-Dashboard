// Enable debug mode by default
let DEBUG_MODE = true;

// Toggle visibility of debug panel
function toggleDebug() {
  DEBUG_MODE = !DEBUG_MODE;
  document.getElementById("debug-toggle").textContent = "Debug: " + (DEBUG_MODE ? "ON" : "OFF");
  document.getElementById("debug-panel").style.display = DEBUG_MODE ? "block" : "none";
  if (!DEBUG_MODE) {
    document.getElementById("debug-output").textContent = "";
  }
}

// Append messages to the debug output area and console
function logDebug(message, data = null) {
  const output = document.getElementById("debug-output");
  const timestamp = new Date().toISOString().split("T")[1].slice(0, 8);
  const fullMessage = `[${timestamp}] ${message}`;
  
  if (DEBUG_MODE) {
    output.textContent += fullMessage + "\n";
    if (data) {
      output.textContent += JSON.stringify(data, null, 2) + "\n\n";
    }
  }

  // Always log to browser console for developer tools
  console.log(fullMessage);
  if (data) console.log(data);
}

// Fetch and display device data
function loadDevices() {
  logDebug("🔍 Fetching /mpsm/api/devices");

  fetch("/mpsm/api/devices")
    .then(res => {
      logDebug("✅ Received response: " + res.status);
      return res.json();
    })
    .then(data => {
      logDebug("📦 JSON response received", data);
      const tbody = document.getElementById("device-tbody");
      tbody.innerHTML = "";

      if (data.status !== "success") {
        tbody.innerHTML = `<tr><td colspan="4">⚠️ ${data.message}</td></tr>`;
        return;
      }

      const devices = data.data;
      if (!devices || devices.length === 0) {
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
      logDebug("❌ Network or fetch error occurred", err);
      const tbody = document.getElementById("device-tbody");
      tbody.innerHTML = `<tr><td colspan="4">❌ Failed to load device data.</td></tr>`;
    });
}

// Run once page has loaded
window.onload = function () {
  // Force debug panel visible by default
  document.getElementById("debug-panel").style.display = "block";
  document.getElementById("debug-toggle").textContent = "Debug: ON";
  loadDevices();
};
