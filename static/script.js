document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("toggle-settings");
  const settingsPanel = document.getElementById("settings-content");
  const endpointContainer = document.getElementById("endpoint-list");
  const loadBtn = document.getElementById("load-selected");
  const resultsPanel = document.getElementById("results");

  // === Verify all required DOM elements exist ===
  if (!toggleBtn || !settingsPanel || !endpointContainer || !loadBtn || !resultsPanel) {
    console.error("❌ Missing one or more critical DOM elements. Check HTML structure.");
    return;
  }

  // === Retrieve user-selected endpoints from localStorage (if any) ===
  let selectedEndpoints = JSON.parse(localStorage.getItem("mpsm_selected_endpoints") || "[]");

  function saveSelections() {
    localStorage.setItem("mpsm_selected_endpoints", JSON.stringify(selectedEndpoints));
  }

  // === Renders the list of checkboxes from endpoint definitions ===
  function renderEndpointsList(endpoints) {
    endpointContainer.innerHTML = "";
    endpoints.forEach(({ path, description }) => {
      const label = document.createElement("label");
      label.className = "endpoint-option";

      const input = document.createElement("input");
      input.type = "checkbox";
      input.value = path;
      input.checked = selectedEndpoints.includes(path);
      input.addEventListener("change", () => {
        if (input.checked) {
          if (!selectedEndpoints.includes(path)) selectedEndpoints.push(path);
        } else {
          selectedEndpoints = selectedEndpoints.filter(p => p !== path);
        }
        saveSelections();
      });

      label.appendChild(input);
      label.appendChild(document.createTextNode(` ${path} – ${description}`));
      endpointContainer.appendChild(label);
    });
  }

  // === Fetch results for each selected endpoint ===
  function loadResults() {
    resultsPanel.innerHTML = "";
    if (!selectedEndpoints.length) {
      resultsPanel.innerHTML = "<p>No endpoints selected.</p>";
      return;
    }

    selectedEndpoints.forEach(endpoint => {
      const container = document.createElement("div");
      container.className = "result-block";
      container.innerHTML = `<h4>${endpoint}</h4><pre>Loading...</pre>`;
      resultsPanel.appendChild(container);

      fetch(`trigger_api.php?endpoint=${encodeURIComponent(endpoint)}`)
        .then(res => res.json())
        .then(json => {
          container.querySelector("pre").textContent = JSON.stringify(json, null, 2);
        })
        .catch(err => {
          container.querySelector("pre").textContent = `Error: ${err}`;
        });
    });
  }

  // === Toggle visibility of the settings panel ===
  toggleBtn.addEventListener("click", () => {
    settingsPanel.classList.toggle("hidden");
  });

  // === Load data on button click ===
  loadBtn.addEventListener("click", () => {
    loadResults();
  });

  // === Fetch and render available endpoints from static JSON ===
  fetch("static/endpoints.json")
    .then(res => res.json())
    .then(renderEndpointsList)
    .catch(err => {
      endpointContainer.innerHTML = `<p class="error">Failed to load endpoints: ${err}</p>`;
    });
});
