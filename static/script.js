const ENDPOINTS_JSON = "static/endpoints.json";
const API_TRIGGER = "trigger_api.php";

let selectedEndpoints = JSON.parse(localStorage.getItem("mpsm_selected_endpoints") || "[]");

function saveSelections() {
  localStorage.setItem("mpsm_selected_endpoints", JSON.stringify(selectedEndpoints));
}

function renderEndpointsList(endpoints) {
  const list = document.getElementById("endpoint-list");
  list.innerHTML = "";
  endpoints.forEach(({ path, description }) => {
    const label = document.createElement("label");
    label.className = "endpoint-option";
    const input = document.createElement("input");
    input.type = "checkbox";
    input.value = path;
    input.checked = selectedEndpoints.includes(path);
    input.addEventListener("change", () => {
      if (input.checked) selectedEndpoints.push(path);
      else selectedEndpoints = selectedEndpoints.filter(p => p !== path);
      saveSelections();
    });
    label.appendChild(input);
    label.appendChild(document.createTextNode(` ${path} – ${description}`));
    list.appendChild(label);
  });
}

function loadResults() {
  const results = document.getElementById("results");
  results.innerHTML = "";
  if (!selectedEndpoints.length) {
    results.innerHTML = "<p>No endpoints selected.</p>";
    return;
  }
  selectedEndpoints.forEach(endpoint => {
    const container = document.createElement("div");
    container.className = "result-block";
    container.innerHTML = `<h4>${endpoint}</h4><pre>Loading...</pre>`;
    results.appendChild(container);

    fetch(`${API_TRIGGER}?endpoint=${encodeURIComponent(endpoint)}`)
      .then(res => res.json())
      .then(json => {
        container.querySelector("pre").textContent = JSON.stringify(json, null, 2);
      })
      .catch(err => {
        container.querySelector("pre").textContent = `Error: ${err}`;
      });
  });
}

function setupUI() {
  document.getElementById("toggle-settings").addEventListener("click", () => {
    document.getElementById("settings-content").classList.toggle("hidden");
  });

  document.getElementById("load-selected").addEventListener("click", () => {
    loadResults();
  });

  fetch(ENDPOINTS_JSON)
    .then(res => res.json())
    .then(renderEndpointsList);
}

document.addEventListener("DOMContentLoaded", setupUI);
