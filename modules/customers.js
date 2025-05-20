// modules/customers.js
// v2.2.2 [Debug Logging Added: Load + Select]
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
    window.DebugPanel?.logEvent("customers:loaded", customers.length);
    renderDropdown(customers);
  } catch (err) {
    console.error("Customer fetch failed", err);
    window.DebugPanel?.logError("customer fetch failed", err);
  }
}

function renderDropdown(customers) {
  const app = document.getElementById("app");
  app.innerHTML = "";

  const wrapper = document.createElement("div");
  wrapper.className = "customer-select-wrapper";

  const label = document.createElement("label");
  label.textContent = "Select Customer";

  const input = document.createElement("input");
  input.type = "text";
  input.placeholder = "Type or click to search...";
  input.className = "customer-input";

  const clear = document.createElement("span");
  clear.textContent = "✕";
  clear.className = "clear-input";
  clear.onclick = () => {
    input.value = "";
    updateList();
    input.focus();
  };

  const list = document.createElement("div");
  list.className = "customer-list";
  list.style.display = "none";

  let filtered = [...customers];
  let selectedIndex = -1;

  const updateList = () => {
    filtered = customers.filter(c =>
      c.Description.toLowerCase().includes(input.value.toLowerCase())
    );
    list.innerHTML = "";
    filtered.forEach((c, i) => {
      const item = document.createElement("div");
      item.textContent = c.Description;
      item.className = "customer-item";
      if (i === selectedIndex) item.classList.add("active");
      item.onclick = () => {
        input.value = c.Description;
        list.style.display = "none";
        store.set("customerId", c.Code);
        eventBus.emit("customer:selected", c.Code);
        window.DebugPanel?.logEvent("customer:selected", c.Code);
      };
      list.appendChild(item);
    });
    list.style.display = filtered.length ? "block" : "none";
  };

  input.addEventListener("focus", () => {
    selectedIndex = -1;
    updateList();
  });
  input.addEventListener("input", () => {
    selectedIndex = -1;
    updateList();
  });
  input.addEventListener("click", () => {
    selectedIndex = -1;
    updateList();
  });
  input.addEventListener("keydown", (e) => {
    if (filtered.length === 0) return;
    if (e.key === "ArrowDown") {
      selectedIndex = (selectedIndex + 1) % filtered.length;
      updateList();
    } else if (e.key === "ArrowUp") {
      selectedIndex = (selectedIndex - 1 + filtered.length) % filtered.length;
      updateList();
    } else if (e.key === "Enter" && selectedIndex >= 0) {
      const c = filtered[selectedIndex];
      input.value = c.Description;
      list.style.display = "none";
      store.set("customerId", c.Code);
      eventBus.emit("customer:selected", c.Code);
      window.DebugPanel?.logEvent("customer:selected", c.Code);
    }
  });

  wrapper.appendChild(label);
  wrapper.appendChild(input);
  wrapper.appendChild(clear);
  wrapper.appendChild(list);
  app.appendChild(wrapper);
}
