// v1.2.0 [Fix: LCARS Style + Live Search Dropdown]
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
    renderSearchableDropdown(customers);
  } catch (err) {
    console.error("Customer fetch failed", err);
    window.DebugPanel?.logError("Customer fetch failed", err);
  }
}

function renderSearchableDropdown(customers) {
  let app = document.getElementById("app");
  if (!app) {
    app = document.createElement("div");
    app.id = "app";
    document.body.appendChild(app);
  }

  const container = document.createElement("div");
  container.id = "customer-dropdown";
  container.style.margin = "1rem";
  container.style.maxWidth = "600px";

  const label = document.createElement("label");
  label.textContent = "🔎 Search + Select Customer";
  label.style.display = "block";
  label.style.color = "#7fcfff";
  label.style.fontSize = "14px";
  label.style.marginBottom = "0.5rem";

  const input = document.createElement("input");
  input.type = "text";
  input.placeholder = "Start typing to filter...";
  input.style.width = "100%";
  input.style.padding = "0.5rem";
  input.style.marginBottom = "0.5rem";
  input.style.background = "rgba(0, 30, 60, 0.8)";
  input.style.color = "#cceeff";
  input.style.border = "1px solid #446688";
  input.style.borderRadius = "4px";

  const select = document.createElement("select");
  select.id = "customer-select";
  select.style.width = "100%";
  select.style.maxHeight = "250px";
  select.style.overflowY = "auto";
  select.style.background = "rgba(0, 30, 60, 0.8)";
  select.style.color = "#cceeff";
  select.style.border = "1px solid #446688";
  select.style.borderRadius = "4px";
  select.style.padding = "0.5rem";

  const defaultOption = document.createElement("option");
  defaultOption.value = "";
  defaultOption.textContent = "-- Select Customer --";
  select.appendChild(defaultOption);

  function updateOptions(filter = "") {
    select.innerHTML = "";
    select.appendChild(defaultOption);
    customers
      .filter(c => c.Description.toLowerCase().includes(filter.toLowerCase()))
      .forEach(c => {
        const opt = document.createElement("option");
        opt.value = c.Code; // ✅ This is what Device/List requires
        opt.textContent = c.Description;
        select.appendChild(opt);
      });
  }

  updateOptions();

  input.addEventListener("input", () => {
    updateOptions(input.value);
  });

  select.addEventListener("change", () => {
    const selectedCode = select.value;
    store.set("customerId", selectedCode);
    eventBus.emit("customer:selected", selectedCode);
  });

  container.appendChild(label);
  container.appendChild(input);
  container.appendChild(select);

  app.innerHTML = ""; // clear and inject
  app.appendChild(container);
}
