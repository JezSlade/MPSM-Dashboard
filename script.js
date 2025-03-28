let DEBUG_MODE = false;

// Toggle the debug panel and update the button label
function toggleDebug() {
    DEBUG_MODE = !DEBUG_MODE;
    const toggleBtn = document.getElementById("debug-toggle");
    const debugPanel = document.getElementById("debug-panel");
    toggleBtn.textContent = "Debug: " + (DEBUG_MODE ? "ON" : "OFF");
    debugPanel.style.display = DEBUG_MODE ? "block" : "none";

    if (!DEBUG_MODE) {
        document.getElementById("debug-output").textContent = "";
    }
}

// Append logs to the debug panel
function logDebug(message, data = null) {
    if (!DEBUG_MODE) return;
    const output = document.getElementById("debug-output");
    output.textContent += message + "\n";
    if (data) {
        output.textContent += JSON.stringify(data, null, 2) + "\n\n";
    }
}

function loadAuthData() {
    logDebug("🔍 Fetching: /mpsm/api/auth");
    fetch('/mpsm/api/auth')
        .then(response => {
            logDebug("✅ /auth response status: " + response.status);
            return response.json();
        })
        .then(data => {
            logDebug("📦 Auth Data Response:", data);
            const pre = document.getElementById("auth-data");
            if (data.status === "success") {
                pre.textContent = data.auth.join("\n");
            } else {
                pre.textContent = "⚠️ Error loading authentication data:\n" + data.message;
            }
        })
        .catch(err => {
            logDebug("❌ Network error fetching /auth:", err);
            document.getElementById("auth-data").textContent = "❌ Network error loading auth data.";
        });
}

function loadMpsmData() {
    logDebug("🔍 Fetching: /mpsm/api/data");
    fetch('/mpsm/api/data')
        .then(response => {
            logDebug("✅ /data response status: " + response.status);
            return response.json();
        })
        .then(data => {
            logDebug("📦 MPSM Data Response:", data);
            const pre = document.getElementById("mpsm-data");
            if (data.status === "success") {
                pre.textContent = data.data.join("\n");
            } else {
                pre.textContent = "⚠️ Error loading MPSM data:\n" + data.message;
            }
        })
        .catch(err => {
            logDebug("❌ Network error fetching /data:", err);
            document.getElementById("mpsm-data").textContent = "❌ Network error loading MPSM data.";
        });
}

// Run after the page fully loads
window.onload = function () {
    loadAuthData();
    loadMpsmData();
};
