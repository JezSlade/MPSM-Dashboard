// v1.2.0 [Add: Searchable Custom Dropdown for Customers]
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
    renderCustomDropdown(customers);
  } catch (err) {
    console.error("Customer fetch failed", err);
    window.DebugPanel?.logError("Customer fetch failed", err);
  }
}

function renderCustomDropdown(customers) {
  let app = document.getElementById("app");
  if (!app) {
    app = document.createElement("div");
    app.id = "app";
    document.body.appendChild(app);
  }

  app.innerHTML = `
    <div class="dropdown-wrapper">
      <div class="dropdown-selected">-- Select Customer --</div>
      <div class="dropdown-options">
        <input type="text" placeholder="Search..." class="dropdown-search" />
        <div class="dropdown-list"></div>
      </div>
    </div>
  `;

  const selected = app.querySelector(".dropdown-selected");
  const options = app.querySelector(".dropdown-options");
  const list = options.querySelector(".dropdown-list");
  const search = options.querySelector(".dropdown-search");

  function populate(filtered) {
    list.innerHTML = "";
    filtered.forEach(c => {
      const opt = document.createElement("div");
      opt.className = "dropdown-option";
      opt.dataset.id = c.Id;
      opt.textContent = c.Description;
      opt.addEventListener("click", () => {
        selected.textContent = c.Description;
        options.classList.remove("show");
        store.set("customerId", c.Id);
        eventBus.emit("customer:selected", c.Id);
      });
      list.appendChild(opt);
    });
  }

  populate(customers);

  search.addEventListener("input", () => {
    const val = search.value.toLowerCase();
    const filtered = customers.filter(c =>
      c.Description.toLowerCase().includes(val) ||
      c.Code.toLowerCase().includes(val)
    );
    populate(filtered);
  });

  selected.addEventListener("click", () => {
    options.classList.toggle("show");
    if (options.classList.contains("show")) {
      search.focus();
    }
  });

  document.addEventListener("click", (e) => {
    if (!app.contains(e.target)) {
      options.classList.remove("show");
    }
  });
}
