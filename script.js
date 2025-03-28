let DEBUG_MODE = true;

// Toggle debug panel visibility
function toggleDebug() {
    DEBUG_MODE = !DEBUG_MODE;
    document.getElementById("debug-toggle").textContent = "Debug: " + (DEBUG_MODE ? "ON" : "OFF");
    document.getElementById("debug-panel").style.display = DEBUG_MODE ? "block" : "none";
    if (!DEBUG_MODE) {
        document.getElementById("debug-output").textContent = "";
    }
}

// Append debug messages to the panel
function logDebug(message, data = null) {
    if (!DEBUG_MODE) return;
    const output = document.getElementById("debug-output");
    output.textContent += message + "\n";
    if (data) {
        output.textContent += JSON.stringify(data, null, 2) + "\n\n";
    }
}

// Fetch device data from Flask proxy API
function loadDevices() {
    logDebug("🔍 Fetching: /mpsm/api/devices");

    fetch('/mpsm/api/devices')
        .then(res => {
            logDebug("✅ /devices response status: " + res.status);
            return res.json();
        })
        .then(data => {
            logDebug("📦 /devices response JSON:", data);
            const tbody = document.getElementById("device-tbody");
            tbody.innerHTML = ""; // Clear previous or loading content

            if (data.status !== "success") {
                tbody.innerHTML = `<tr><td colspan="4">⚠️ Error: ${data.message}</td></tr>`;
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
            logDebug("❌ Network error fetching /devices:", err);
            const tbody = document.getElementById("device-tbody");
            tbody.innerHTML = `<tr><td colspan="4">❌ Network error loading device data.</td></tr>`;
        });
}

// Initialize on page load
window.onload = function () {
    loadDevices();
};
