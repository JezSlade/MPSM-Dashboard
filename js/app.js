let currentModule = null;
let currentCustomerId = localStorage.getItem("selectedCustomer") || null;

function populateCustomerDropdown() {
  fetch("api/token.php")
    .then(res => res.json())
    .then(token => {
      return fetch("https://api.abassetmanagement.com/api3/Customer/GetCustomers", {
        headers: {
          Authorization: `Bearer ${token.access_token}`
        }
      });
    })
    .then(res => res.json())
    .then(customers => {
      const dropdown = document.getElementById("customerSelect");
      dropdown.innerHTML = "";
      customers.forEach(c => {
        const opt = document.createElement("option");
        opt.value = c.Id;
        opt.textContent = c.Name;
        if (c.Id == currentCustomerId) opt.selected = true;
        dropdown.appendChild(opt);
      });
      dropdown.addEventListener("change", e => {
        currentCustomerId = e.target.value;
        localStorage.setItem("selectedCustomer", currentCustomerId);
        reloadCurrentModule();
      });
    });
}

function reloadCurrentModule() {
  if (currentModule) loadModule(currentModule);
}

function loadModule(name) {
  currentModule = name;
  import(`./modules/${name}.js`)
    .then(module => module.init(document.getElementById("moduleContent"), currentCustomerId))
    .catch(err => {
      document.getElementById("moduleContent").innerText = `Module failed: ${err}`;
    });
}

document.querySelectorAll(".sidebar li").forEach(item => {
  item.addEventListener("click", () => {
    const module = item.getAttribute("data-module");
    loadModule(module);
  });
});

populateCustomerDropdown();
