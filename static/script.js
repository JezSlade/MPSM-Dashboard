let DEBUG_MODE = true;

function logDebug(message, data = null) {
  const output = document.getElementById("debug-output");
  const timestamp = new Date().toISOString().split("T")[1].slice(0, 8);
  const fullMessage = `[${timestamp}] ${message}`;
  output.textContent += fullMessage + "\n";
  if (data) output.textContent += JSON.stringify(data, null, 2) + "\n\n";
  console.log(fullMessage);
  if (data) console.log(data);
}

function toggleDebug() {
  DEBUG_MODE = !DEBUG_MODE;
  document.getElementById("debug-toggle").textContent = "Debug: " + (DEBUG_MODE ? "ON" : "OFF");
  document.getElementById("debug-panel").style.display = DEBUG_MODE ? "block" : "none";
  if (!DEBUG_MODE) document.getElementById("debug-output").textContent = "";
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("debug-toggle").addEventListener("click", toggleDebug);
  document.getElementById("debug-panel").style.display = "block";
  logDebug("🔧 Debug console active");

  fetch("trigger_api.php")
    .then(res => res.json())
    .then(data => {
      logDebug("✅ API response received", data);
      const tbody = document.getElementById("device-tbody");
      tbody.innerHTML = "";

      if (data.status !== "success") {
        tbody.innerHTML = `<tr><td colspan="4">❌ ${data.message}</td></tr>`;
        return;
      }

      const devices = data.data;
      if (!devices.length) {
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
      logDebug("❌ Fetch error", err);
      document.getElementById("device-tbody").innerHTML =
        `<tr><td colspan="4">Error loading devices</td></tr>`;
    });
});
