<?php
// cards/CustomersCard.php
// -------------------------------------------------------------------
// Full, etched glassmorphic Customers card with neon CMYK styling
// and first column removed (only Description shown).
// -------------------------------------------------------------------

require_once __DIR__ . '/../includes/card_base.php';
?>

<div class="card customers-card">
  <header class="card-header">
    <h2>Customers</h2>
  </header>

  <div class="card-body" id="customers-container">
    <!-- injected by JS -->
  </div>

  <footer class="card-footer">
    <button data-action="refresh">Refresh</button>
  </footer>
</div>

<script type="module">
// -------------------------------------------------------------------
// Full inline JavaScript for CustomersCard
// No CustomerCode column, only Description.
// -------------------------------------------------------------------

import { fetchJson } from '/js/api.js';          // shared fetch wrapper
import { renderTable } from '/js/ui_helpers.js'; // shared UI helper

const container = document.getElementById('customers-container');
const PAGE_SIZE = 15;

/**
 * Loads customers and renders a glassmorphic table with only Description.
 */
async function loadCustomers(page = 1) {
  try {
    const url = `/api/get_customers.php?PageNumber=${page}&PageRows=${PAGE_SIZE}&SortColumn=Description&SortOrder=Asc`;
    console.debug('Fetching customers:', url);

    const data = await fetchJson(url);
    console.debug('Payload:', data);

    const rows = Array.isArray(data.Result) ? data.Result : [];

    if (rows.length === 0) {
      container.innerHTML = `
        <div class="empty-state">No customers found.</div>
      `;
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
    container.innerHTML = `
      <div class="error">Failed to load customers.</div>
      <pre class="debug-error">${err.toString()}</pre>
    `;
    console.error('loadCustomers error:', err);
  }
}

// Wire up “Refresh” button
document
  .querySelector('.customers-card [data-action="refresh"]')
  .addEventListener('click', () => loadCustomers());

// Initial load
loadCustomers();

</script>
