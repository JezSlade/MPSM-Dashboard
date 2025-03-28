let DEBUG_MODE = false;

function toggleDebug() {
    DEBUG_MODE = !DEBUG_MODE;
    document.getElementById("debug-toggle").textContent = "Debug: " + (DEBUG_MODE ? "ON" : "OFF");
    document.getElementById("debug-panel").style.display = DEBUG_MODE ? "block" : "none";
    if (!DEBUG_MODE) {
        document.getElementById("debug-output").textContent = "";
    }
}

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
            logDebug("✅ Received auth response with status: " + response.status);
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
            logDebug("❌ Network error on /mpsm/api/auth:", err);
            document.getElementById("auth-data").textContent = "❌ Network error loading auth data.";
        });
}

function loadMpsmData() {
    logDebug("🔍 Fetching: /mpsm/api/data");
    fetch('/mpsm/api/data')
        .then(response => {
            logDebug("✅ Received data response with status: " + response.status);
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
            logDebug("❌ Network error on /mpsm/api/data:", err);
            document.getElementById("mpsm-data").textContent = "❌ Network error loading MPSM data.";
        });
}

window.onload = function () {
    loadAuthData();
    loadMpsmData();
};
