// v1.0.0 [Init: Customer Dropdown UI + Event Emit]
import { eventBus } from '../core/event-bus.js';
import { store } from '../core/store.js';

export async function loadCustomers() {
  try {
    const res = await fetch('./get_customers.php');
    const data = await res.json();

    if (!Array.isArray(data?.Result)) throw new Error("Invalid customer list");

    const customers = data.Result;
    store.set("customers", customers);
    eventBus.emit("customers:loaded", customers);
    renderDropdown(customers);
  } catch (err) {
    console.error("Customer fetch failed", err);
    window.DebugPanel?.logError("Customer fetch failed", err);
  }
}

function renderDropdown(customers) {
  let app = document.getElementById("app");
  if (!app) {
    app = document.createElement("div");
    app.id = "app";
    document.body.appendChild(app);
  }

  const select = document.createElement("select");
  select.id = "customer-select";
  select.innerHTML = '<option value="">-- Select Customer --</option>';

  customers.forEach(c => {
    const opt = document.createElement("option");
    opt.value = c.Id;
    opt.textContent = c.Name;
    select.appendChild(opt);
  });

  select.addEventListener("change", () => {
    const selectedId = select.value;
    store.set("customerId", selectedId);
    eventBus.emit("customer:selected", selectedId);
  });

  app.innerHTML = "<h3>Select Customer:</h3>";
  app.appendChild(select);
}
