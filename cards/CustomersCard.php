<?php
// cards/CustomersCard.php
// -------------------------------------------------------------------
// This is the full, corrected Customers card—no snippets, all inline.
// -------------------------------------------------------------------

require_once __DIR__ . '/../includes/card_base.php';
?>

<div class="card customers-card">
  <header class="card-header">
    <h2>Customers</h2>
  </header>

  <div class="card-body" id="customers-container">
    <!-- content injected by JavaScript -->
  </div>

  <footer class="card-footer">
    <button data-action="refresh">Refresh</button>
  </footer>
</div>

<script type="module">
// js logic for CustomersCard, full inline

import { fetchJson } from '/js/api.js';         // shared wrapper
import { renderTable } from '/js/ui_helpers.js'; // shared UI helper

const container = document.getElementById('customers-container');
const PAGE_SIZE = 15;

/**
 * Load a page of customers and render the table or an empty state.
 * @param {number} page – 1-based page index
 */
async function loadCustomers(page = 1) {
  try {
    const url = `/api/get_customers.php?PageNumber=${page}&PageRows=${PAGE_SIZE}`;
    const data = await fetchJson(url);

    // Debug: inspect raw payload if results are missing
    console.debug('get_customers response:', data);

    // If no array or zero-length, show friendly empty state
    if (!Array.isArray(data.Result) || data.Result.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          No customers found.
        </div>
      `;
      return;
    }

    // Otherwise render the paginated table
    const totalPages = Math.ceil((data.TotalRows || 0) / PAGE_SIZE);
    container.innerHTML = renderTable({
      columns:    ['CustomerCode', 'Description'],
      rows:       data.Result,
      page,
      totalPages,
      onPageChange: loadCustomers
    });

  } catch (err) {
    // On error, show a message and log details
    container.innerHTML = `
      <div class="error">
        Failed to load customers.
      </div>
    `;
    console.error('Error in loadCustomers():', err);
  }
}

// Wire up the Refresh button
document
  .querySelector('.customers-card [data-action="refresh"]')
  .addEventListener('click', () => loadCustomers());

// Initial load
loadCustomers();

</script>
