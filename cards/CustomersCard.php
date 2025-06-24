<?php
// cards/CustomersCard.php
// -------------------------------------------------------------------
// Full, self-contained Customers card with enhanced result handling.
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
// -------------------------------------------------------------------

import { fetchJson } from '/js/api.js';          // shared fetch wrapper
import { renderTable } from '/js/ui_helpers.js'; // shared UI helper

const container = document.getElementById('customers-container');
const PAGE_SIZE = 15;

/**
 * Loads customers and renders table or empty/error states.
 * Handles both uppercase and lowercase Result keys.
 */
async function loadCustomers(page = 1) {
  try {
    const url = `/api/get_customers.php?PageNumber=${page}&PageRows=${PAGE_SIZE}`;
    const data = await fetchJson(url);

    // Inspect full payload
    console.debug('get_customers payload:', data);

    // Normalize rows array from possible 'Result' or 'result' key
    let rows = [];
    if (Array.isArray(data.Result)) {
      rows = data.Result;
    } else if (Array.isArray(data.result)) {
      rows = data.result;
    }

    // If still no rows, show empty-state
    if (rows.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          No customers found.
        </div>
      `;
      return;
    }

    // Compute total rows & pages (fallback if missing)
    const totalRows = typeof data.TotalRows === 'number'
      ? data.TotalRows
      : rows.length;
    const totalPages = Math.ceil(totalRows / PAGE_SIZE) || 1;

    // Render table with paging
    container.innerHTML = renderTable({
      columns:      ['CustomerCode', 'Description'],
      rows,
      page,
      totalPages,
      onPageChange: loadCustomers
    });

  } catch (err) {
    // On error, show friendly message
    container.innerHTML = `
      <div class="error">
        Failed to load customers.
      </div>
    `;
    console.error('loadCustomers() error:', err);
  }
}

// Wire up “Refresh” button
document
  .querySelector('.customers-card [data-action="refresh"]')
  .addEventListener('click', () => loadCustomers());

// Initial invocation
loadCustomers();
</script>
