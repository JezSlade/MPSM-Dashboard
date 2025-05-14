// Central shared state object for all modules
const MPSM = {
  token: null,
  customers: [],
  selectedCustomerId: null,
  printers: [],
  version: 'v1.0.2 [Customer Check Fixed]'
};

// Dashboard initializer
async function initDashboard() {
  logDebug(`[Init] Booting MPSM ${MPSM.version}`);
  await getToken();
  await getCustomers();
  if (MPSM.customers.length > 0) {
    MPSM.selectedCustomerId = MPSM.customers[0].Id;
    await getPrinters(MPSM.selectedCustomerId);
  }
}

// Fetches raw token string from working_token.php
async function getToken() {
  try {
    const res = await fetch('working_token.php');
    const text = await res.text();

    if (text && text.trim().length > 0) {
      MPSM.token = text.trim();
      logDebug('[Token] Token acquired');
    } else {
      throw new Error("No token returned from PHP");
    }
  } catch (err) {
    logDebug('[Token] ERROR: ' + err.message);
  }
}

// Fetches customer list with Authorization header and verifies format
async function getCustomers() {
  try {
    const res = await fetch('get_customers.php', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${MPSM.token}`,
        'Content-Type': 'application/json'
      }
    });

    if (!res.ok) {
      const text = await res.text();
      throw new Error(`HTTP ${res.status}: ${text}`);
    }

    const data = await res.json();

    if (!data || !Array.isArray(data.Result)) {
      logDebug(`[Customers] Unexpected data format:\n${JSON.stringify(data, null, 2)}`);
      return;
    }

    MPSM.customers = data.Result;
    logDebug(`[Customers] Loaded ${data.Result.length} customers`);
    renderCustomerDropdown();

  } catch (err) {
    logDebug('[Customers] ERROR: ' + err.message);
  }
}

// Fetches printers for selected customer
async function getPrinters(customerId) {
  try {
    const res = await fetch(`get_printers.php?customerId=${encodeURIComponent(customerId)}`);
    const data = await res.json();
    if (Array.isArray(data.Result)) {
      MPSM.printers = data.Result;
      logDebug(`[Printers] Loaded ${data.Result.length} printers for customer ${customerId}`);
      renderPrinterTable();
    } else {
      throw new Error("Invalid printer format");
    }
  } catch (err) {
    logDebug('[Printers] ERROR: ' + err.message);
  }
}

// Renders a dropdown of customer descriptions
function renderCustomerDropdown() {
  const container = document.getElementById("customer-select-panel");
  if (!container) return;

  const select = document.createElement("select");
  select.id = "customer-selector";

  MPSM.customers.forEach(customer => {
    const option = document.createElement("option");
    option.value = customer.Id;
    option.textContent = customer.Description || "Unnamed Customer";
    select.appendChild(option);
  });

  select.addEventListener("change", async (e) => {
    const customerId = e.target.value;
    MPSM.selectedCustomerId = customerId;
    logDebug(`[Dropdown] Switched to customer ID ${customerId}`);
    await getPrinters(customerId);
  });

  container.innerHTML = "";
  container.appendChild(select);
}

// Displays printers in raw JSON format
function renderPrinterTable() {
  const output = document.getElementById("json-output");
  output.textContent = JSON.stringify(MPSM.printers, null, 2);
}

// Appends a message to the debug panel
function logDebug(message) {
  const panel = document.getElementById('debug-log');
  const line = `${new Date().toLocaleTimeString()} ${message}`;
  panel.textContent += `\n${line}`;
}

// Bind refresh/debug toggle and kick off dashboard
window.onload = () => {
  initDashboard();
  document.getElementById("refresh-btn").addEventListener("click", () => location.reload(true));
  document.getElementById("toggle-debug").addEventListener("click", () => {
    const dbg = document.getElementById("debug-panel");
    dbg.style.display = dbg.style.display === "none" ? "block" : "none";
  });
};
