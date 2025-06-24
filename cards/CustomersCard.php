<?php
// cards/CustomersCard.php
// -------------------------------------------------------------------
// Full, self-contained Customers card with extra diagnostics.
// -------------------------------------------------------------------

require_once __DIR__ . '/../includes/card_base.php';
?>

<div class="card customers-card">
  <header class="card-header">
    <h2>Customers</h2>
  </header>

  <div class="card-body" id="customers-container">
    <!-- injected by JavaScript -->
  </div>

  <footer class="card-footer">
    <button data-action="refresh">Refresh</button>
  </footer>
</div>

<script type="module">
// -------------------------------------------------------------------
// Full inline JavaScript for CustomersCard
// Includes extra debugging to explain why nav (PHP-rendered) succeeds
// but this client-side card might fail or return empty.
// -------------------------------------------------------------------

import { fetchJson } from '/js/api.js';          // shared fetch wrapper
import { renderTable } from '/js/ui_helpers.js'; // shared UI helper

const container = document.getElementById('customers-container');
const PAGE_SIZE = 15;

/**
 * Load page of customers and render results,
 * or show diagnostic info on failure or unexpected empty array.
 */
async function loadCustomers(page = 1) {
  try {
    const url = `/api/get_customers.php?PageNumber=${page}&PageRows=${PAGE_SIZE}`;
    console.debug('Fetching customers from:', url);
    const response = await fetch(url, {
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin'
    });
    console.debug('Raw response status:', response.status, response.statusText);

    if (!response.ok) {
      const text = await response.text();
      console.error('Non-OK response body:', text);
      throw new Error(`HTTP ${response.status}`);
    }

    const data = await response.json();
    console.debug('Parsed JSON payload:', data);

    // Normalize row array (support Result or result)
    let rows = Array.isArray(data.Result)
      ? data.Result
      : Array.isArray(data.result)
        ? data.result
        : [];

    // If nav dropdown works, then data.Result must have items server-side.
    // If rows is empty here, check console.debug above to see:
    //  • whether data.Result exists but under another key
    //  • whether the client request is reaching the API
    //  • whether CORS or path issues are blocking you

    if (rows.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          No customers found.
        </div>
        <pre class="debug-info">
          DEBUG: rows length = ${rows.length}
          keys: ${Object.keys(data).join(', ')}
        </pre>
      `;
      return;
    }

    // Render table when we have at least one row
    const totalRows  = typeof data.TotalRows === 'number'
      ? data.TotalRows
      : rows.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / PAGE_SIZE));

    container.innerHTML = renderTable({
      columns:      ['CustomerCode', 'Description'],
      rows,
      page,
      totalPages,
      onPageChange: loadCustomers
    });

  } catch (err) {
    container.innerHTML = `
      <div class="error">
        Failed to load customers.
      </div>
      <pre class="debug-error">
        ${err.toString()}
      </pre>
    `;
    console.error('loadCustomers() error:', err);
  }
}

// Wire up “Refresh” button
document
  .querySelector('.customers-card [data-action="refresh"]')
  .addEventListener('click', () => loadCustomers());

// Kick off first load
loadCustomers();

</script>
