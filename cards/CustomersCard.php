<?php
// cards/CustomersCard.php
// -------------------------------------------------------------------
// Customers card—no navigation/select here.
// -------------------------------------------------------------------
require_once __DIR__ . '/../includes/card_base.php';
?>

<div class="card customers-card">
  <header class="card-header">
    <h2>Customers</h2>
  </header>
  <div class="card-body" id="customers-container"></div>
  <footer class="card-footer">
    <button data-action="refresh">Refresh</button>
  </footer>
</div>

<script type="module">
import { fetchJson }   from '/js/api.js';
import { renderTable } from '/js/ui_helpers.js';

const container = document.getElementById('customers-container');
const PAGE_SIZE = 15;

async function loadCustomers(page = 1) {
  try {
    const url = `/api/get_customers.php?PageNumber=${page}&PageRows=${PAGE_SIZE}&SortColumn=Description&SortOrder=Asc`;
    const data = await fetchJson(url);
    const rows = Array.isArray(data.Result) ? data.Result : [];

    if (rows.length === 0) {
      container.innerHTML = `<div class="empty-state">No customers found.</div>`;
      return;
    }

    const totalRows  = typeof data.TotalRows === 'number' ? data.TotalRows : rows.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / PAGE_SIZE));

    container.innerHTML = renderTable({
      columns:      ['Description'],
      rows,
      page,
      totalPages,
      onPageChange: loadCustomers
    });
  } catch (err) {
    container.innerHTML = `<div class="error">Failed to load customers.</div>`;
    console.error(err);
  }
}

document.querySelector('.customers-card [data-action="refresh"]').addEventListener('click', () => loadCustomers());
loadCustomers();
</script>
