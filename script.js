document.getElementById("refresh-btn").addEventListener("click", () => {
  location.reload(true); // Full reload bypassing cache
});

document.getElementById("toggle-debug").addEventListener("click", () => {
  const panel = document.getElementById("debug-panel");
  panel.style.display = (panel.style.display === "none") ? "block" : "none";
});

document.addEventListener("DOMContentLoaded", () => {
    const output = document.getElementById("json-output");
    const debug = document.getElementById("debug-log");
  
    function log(message) {
      const timestamp = new Date().toLocaleTimeString();
      debug.textContent += `\n[${timestamp}] ${message}`;
    }
  
    function fetchPrinters() {
      const url = "working_token.php";
      log("📡 Fetching data from working_token.php...");
  
      fetch(url)
        .then(res => {
          log(`Status: ${res.status}`);
          if (!res.ok) throw new Error(`Fetch failed with status ${res.status}`);
          return res.json();
        })
        .then(data => {
          output.textContent = JSON.stringify(data, null, 2);
          log("✅ API response loaded and displayed.");
        })
        .catch(err => {
          output.textContent = "❌ Error loading data";
          log(`❌ ${err}`);
        });
    }
  
    fetchPrinters();
  });
