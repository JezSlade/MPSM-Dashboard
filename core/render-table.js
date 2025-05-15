// v1.0.0 [Init: Shared Table Renderer]
export function renderTable(containerId, data) {
  const container = document.getElementById("app");
  const table = document.createElement("table");
  table.className = "mpsm-table";

  if (!data.length) {
    table.innerHTML = "<tr><td>No data available</td></tr>";
    container.appendChild(table);
    return;
  }

  const keys = Object.keys(data[0]);
  const thead = document.createElement("thead");
  const headRow = document.createElement("tr");
  keys.forEach(key => {
    const th = document.createElement("th");
    th.textContent = key;
    headRow.appendChild(th);
  });
  thead.appendChild(headRow);
  table.appendChild(thead);

  const tbody = document.createElement("tbody");
  data.forEach(row => {
    const tr = document.createElement("tr");
    keys.forEach(key => {
      const td = document.createElement("td");
      td.textContent = row[key];
      tr.appendChild(td);
    });
    tbody.appendChild(tr);
  });

  table.appendChild(tbody);

  let section = document.getElementById(containerId);
  if (!section) {
    section = document.createElement("div");
    section.id = containerId;
    container.appendChild(section);
  } else {
    section.innerHTML = ""; // clear previous
  }

  section.appendChild(table);
}
