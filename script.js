// Shared application state for all modules
const MPSM = {
  token: null,
  customers: [],
  selectedCustomerId: null,
  printers: [],
  version: 'v1.0.0'
};

// Initialization entry point
async function initDashboard() {
  logDebug('[Init] Booting MPSM v' + MPSM.version);
  await getToken();
  await getCustomers();
  if (MPSM.customers.length > 0) {
    MPSM.selectedCustomerId = MPSM.customers[0].Id;
    await getPrinters(MPSM.selectedCustomerId);
  }
}

// Retrieves token from working_token.php as plain text (not JSON)
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

// Retrieves customer list from get_customers.php
async function getCustomers() {
  try {
    const res = await fetch('get_customers.php');
    const data = await res.json();
    if (Array.isArray(data.Result)) {
      MPSM.customers = data.Result;
      logDebug(`[Customers] Loaded ${data.Result.length} customers`);
      renderCustomerDropdown();
    } else {
      throw new Error("Invalid customer format");
    }
  } catch (err) {
    logDebug('[Customers] ERROR: ' + err.message);
  }
}

// Retrieves printers for selected customer from get_printers.php
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

// Renders dropdown with customer names and binds onchange event
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

// Displays printer results in the #json-output block
function renderPrinterTable() {
  const output = document.getElementById("json-output");
  output.textContent = JSON.stringify(MPSM.printers, null, 2);
}

// Appends log messages to the debug console panel
function logDebug(message) {
  const panel = document.getElementById('debug-log');
  const line = `${new Date().toLocaleTimeString()} ${message}`;
  panel.textContent += `\n${line}`;
}

// Runs init and binds refresh/debug toggle buttons
window.onload = () => {
  initDashboard();
  document.getElementById("refresh-btn").addEventListener("click", () => location.reload(true));
  document.getElementById("toggle-debug").addEventListener("click", () => {
    const dbg = document.getElementById("debug-panel");
    dbg.style.display = dbg.style.display === "none" ? "block" : "none";
  });
};
