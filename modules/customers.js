// v1.1.0 [Fix: Dropdown Style, Correct Value, Scroll]
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

  const label = document.createElement("label");
  label.textContent = "Select Customer:";
  label.style.display = "block";
  label.style.margin = "1rem";

  const select = document.createElement("select");
  select.id = "customer-select";
  select.style.maxWidth = "600px";
  select.style.maxHeight = "300px";
  select.style.overflowY = "auto";
  select.style.display = "block";
  select.style.margin = "1rem";
  select.innerHTML = '<option value="">-- Select Customer --</option>';

  customers.forEach(c => {
    const opt = document.createElement("option");
    opt.value = c.Code; // ✅ Use Code not Id
    opt.textContent = c.Description;
    select.appendChild(opt);
  });

  select.addEventListener("change", () => {
    const selectedCode = select.value;
    store.set("customerId", selectedCode);
    eventBus.emit("customer:selected", selectedCode);
  });

  app.innerHTML = ""; // Clear previous
  app.appendChild(label);
  app.appendChild(select);
}
