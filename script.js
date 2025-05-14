const MPSM = {
  token: null,
  customers: [],
  selectedCustomerId: null,
  printers: [],
  version: 'v1.0.0'
};

async function initDashboard() {
  logDebug('[Init] Booting MPSM v' + MPSM.version);
  await getToken();
  await getCustomers();
  if (MPSM.customers.length > 0) {
    MPSM.selectedCustomerId = MPSM.customers[0].Id;
    await getPrinters(MPSM.selectedCustomerId);
  }
}

async function getToken() {
  try {
    const res = await fetch('working_token.php');
    const data = await res.json();
    if (data.access_token) {
      MPSM.token = data.access_token;
      logDebug('[Token] Token acquired');
    } else {
      throw new Error("No token in response");
    }
  } catch (err) {
    logDebug('[Token] ERROR: ' + err.message);
  }
}

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

function renderPrinterTable() {
  const output = document.getElementById("json-output");
  output.textContent = JSON.stringify(MPSM.printers, null, 2);
}

function logDebug(message) {
  const panel = document.getElementById('debug-log');
  const line = `${new Date().toLocaleTimeString()} ${message}`;
  panel.textContent += `\n${line}`;
}

window.onload = () => {
  initDashboard();
  document.getElementById("refresh-btn").addEventListener("click", () => location.reload(true));
  document.getElementById("toggle-debug").addEventListener("click", () => {
    const dbg = document.getElementById("debug-panel");
    dbg.style.display = dbg.style.display === "none" ? "block" : "none";
  });
};
