// v1.3.0 [LCARS Integrated Search Dropdown]
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
  const app = document.getElementById("app");
  app.innerHTML = ""; // Reset

  const wrapper = document.createElement("div");
  wrapper.style.position = "relative";
  wrapper.style.margin = "1rem";
  wrapper.style.maxWidth = "600px";
  wrapper.style.background = "rgba(0,30,60,0.8)";
  wrapper.style.border = "1px solid #446688";
  wrapper.style.borderRadius = "10px";
  wrapper.style.padding = "1rem";

  const label = document.createElement("label");
  label.textContent = "Select Customer";
  label.style.color = "#7fcfff";
  label.style.display = "block";
  label.style.marginBottom = "0.5rem";

  const input = document.createElement("input");
  input.type = "text";
  input.placeholder = "Type to search...";
  input.style.width = "100%";
  input.style.padding = "0.5rem";
  input.style.border = "1px solid #3388cc";
  input.style.borderRadius = "8px";
  input.style.marginBottom = "0.5rem";
  input.style.background = "rgba(0,40,80,0.9)";
  input.style.color = "#cceeff";

  const list = document.createElement("div");
  list.style.maxHeight = "250px";
  list.style.overflowY = "auto";
  list.style.border = "1px solid #3388cc";
  list.style.borderRadius = "8px";
  list.style.background = "#0a0f1a";

  const renderOptions = (filter = "") => {
    list.innerHTML = "";
    customers
      .filter(c => c.Description.toLowerCase().includes(filter.toLowerCase()))
      .forEach(c => {
        const item = document.createElement("div");
        item.textContent = c.Description;
        item.style.padding = "0.5rem";
        item.style.cursor = "pointer";
        item.style.borderBottom = "1px solid #223344";
        item.onmouseover = () => item.style.background = "#112244";
        item.onmouseout = () => item.style.background = "transparent";
        item.onclick = () => {
          store.set("customerId", c.Code);
          eventBus.emit("customer:selected", c.Code);
          input.value = c.Description;
          list.innerHTML = "";
        };
        list.appendChild(item);
      });
  };

  input.addEventListener("input", () => {
    renderOptions(input.value);
  });

  wrapper.appendChild(label);
  wrapper.appendChild(input);
  wrapper.appendChild(list);
  app.appendChild(wrapper);

  // Initial render

  renderOptions();
}