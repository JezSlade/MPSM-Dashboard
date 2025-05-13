document.addEventListener("DOMContentLoaded", () => {
  const tableContainer = document.getElementById("printer-table-container");
  const debug = document.getElementById("debug-log");

  // Helper function for timestamped debug logging
  function log(message) {
    const timestamp = new Date().toLocaleTimeString();
    debug.textContent += `\n[${timestamp}] ${message}`;
  }

  // Sort devices by critical conditions first (offline, alert, low toner)
  function prioritize(devices) {
    return devices.sort((a, b) => {
      const aScore = scoreDevice(a);
      const bScore = scoreDevice(b);
      return bScore - aScore;
    });
  }

  // Assign priority score to a device
  function scoreDevice(device) {
    let score = 0;
    if (device.IsOffline) score += 10;
    if (device.IsAlertGenerator) score += 5;

    const toners = [
      device.BlackToner, device.CyanToner,
      device.MagentaToner, device.YellowToner
    ];

    for (const t of toners) {
      if (typeof t === "number" && t <= 10) score += 1;
      if (typeof t === "number" && t === 0) score += 2;
    }

    return score;
  }

  // Build table layout from devices
  function renderTable(devices) {
    if (!Array.isArray(devices) || devices.length === 0) {
      tableContainer.innerHTML = "<p>No devices found.</p>";
      return;
    }

    const table = document.createElement("table");
    const thead = document.createElement("thead");
    const headers = [
      "Asset #", "Serial #", "Model", "IP Address",
      "Customer", "Location", "Status",
      "Toner (B/C/M/Y)", "Mono Pages", "Color Pages"
    ];

    thead.innerHTML = `<tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr>`;
    table.appendChild(thead);

    const tbody = document.createElement("tbody");

    // Prioritize devices before rendering
    const sortedDevices = prioritize(devices);

    sortedDevices.forEach(device => {
      const sds = device.SdsDevice || {};
      const status = device.IsOffline ? "Offline" : "Online";

      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${device.AssetNumber || "—"}</td>
        <td>${device.SerialNumber || sds.SerialNumber || "—"}</td>
        <td>${device.Product?.Model || sds.ModelName || "—"}</td>
        <td>${device.IpAddress || sds.IpAddress || "—"}</td>
        <td>${device.CustomerDescription || "—"}</td>
        <td>${device.Department || device.SystemName || "—"}</td>
        <td>${status}</td>
        <td>
          ${device.BlackToner ?? "—"} / ${device.CyanToner ?? "—"} /
          ${device.MagentaToner ?? "—"} / ${device.YellowToner ?? "—"}
        </td>
        <td>${device.CounterMono ?? "—"}</td>
        <td>${device.CounterColor ?? "—"}</td>
      `;
      tbody.appendChild(row);
    });

    table.appendChild(tbody);
    tableContainer.innerHTML = "";
    tableContainer.appendChild(table);
  }

  // Fetch data from backend and pass to renderer
  function fetchPrinters() {
    const url = "working_token.php";
    log("📡 Fetching data from working_token.php...");

    fetch(url)
      .then(res => {
        log(`Status: ${res.status}`);
        if (!res.ok) throw new Error(`Fetch failed with status ${res.status}`);
        return res.json();
      })
      .then(json => {
        if (json.Result && Array.isArray(json.Result)) {
          renderTable(json.Result);
          log("✅ Printer table rendered.");
        } else {
          tableContainer.innerHTML = "<p>❌ Unexpected API structure.</p>";
          log("⚠️ Unexpected JSON structure.");
        }
      })
      .catch(err => {
        tableContainer.innerHTML = "<p>❌ Error loading data</p>";
        log(`❌ ${err}`);
      });
  }

  // Initial data load
  fetchPrinters();
});
