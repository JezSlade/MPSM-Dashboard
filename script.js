// === DOMContentLoaded ===
document.addEventListener("DOMContentLoaded", () => {
  const tableContainer = document.getElementById("printer-table-container");
  const debug = document.getElementById("debug-log");
  const customerSelect = document.getElementById("customer-select");
  const paginationControls = document.getElementById("pagination-controls");

  let fullDeviceList = [];
  let currentPage = 1;
  const rowsPerPage = 25;

  function log(message) {
    const timestamp = new Date().toLocaleTimeString();
    debug.textContent += `\n[${timestamp}] ${message}`;
  }

  function scoreDevice(device) {
    let score = 0;
    if (device.IsOffline) score += 10;
    if (device.IsAlertGenerator) score += 5;
    if (device.AlertOnDisplay) score += 7;
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

  function prioritize(devices) {
    return devices.sort((a, b) => scoreDevice(b) - scoreDevice(a));
  }

  function renderPagination(totalRows) {
    paginationControls.innerHTML = "";
    const totalPages = Math.ceil(totalRows / rowsPerPage);
    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement("button");
      btn.className = "pagination-btn" + (i === currentPage ? " active" : "");
      btn.textContent = i;
      btn.addEventListener("click", () => {
        currentPage = i;
        renderTable(fullDeviceList);
      });
      paginationControls.appendChild(btn);
    }
  }

  function generateTableHeader() {
    return `<tr>
      <th>Asset #</th><th>Serial #</th><th>Model</th><th>IP Address</th>
      <th>Customer</th><th>Location</th><th>Status</th>
      <th>Toner (B/C/M/Y)</th><th>Mono Pages</th><th>Color Pages</th>
    </tr>`;
  }

  function renderTable(devices) {
    const sorted = prioritize(devices);
    const start = (currentPage - 1) * rowsPerPage;
    const paged = sorted.slice(start, start + rowsPerPage);

    const table = document.createElement("table");
    const thead = document.createElement("thead");
    thead.innerHTML = generateTableHeader();
    table.appendChild(thead);

    const tbody = document.createElement("tbody");
    paged.forEach(device => {
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

    renderPagination(sorted.length);
  }

  function fetchCustomers() {
    fetch("get_customers.php")
      .then(res => res.json())
      .then(json => {
        const customers = json.Result || [];
        customers.sort((a, b) => a.Description.localeCompare(b.Description));
        customers.forEach(cust => {
          const opt = document.createElement("option");
          opt.value = cust.Id;
          opt.textContent = cust.Description;
          customerSelect.appendChild(opt);
        });
      });
  }

  function fetchPrinters() {
    log("📡 Fetching printers...");
    const customerId = customerSelect.value;
    fetch("get_devices.php", {
      method: "POST",
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ CustomerId: customerId })
    })
      .then(res => res.json())
      .then(json => {
        const all = json.Result || [];
        fullDeviceList = all;
        currentPage = 1;
        renderTable(fullDeviceList);
        log("✅ Devices rendered.");
      })
      .catch(err => {
        tableContainer.innerHTML = "<p>❌ Error loading data</p>";
        log("❌ " + err);
      });
  }

  customerSelect.addEventListener("change", fetchPrinters);

  fetchCustomers();
  fetchPrinters();
});
