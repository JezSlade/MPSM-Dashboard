<?php
// cards/CustomersCard.php

// All cards should start by including the card-base wrapper,
// which handles container markup, glassmorphic styling, theme toggles, etc.
require_once __DIR__ . '/../includes/card_base.php';
?>

<div class="card customers-card">
  <header class="card-header">
    <h2>Customers</h2>
    <!-- future-proof slot for filters, search, actions -->
  </header>
  <div class="card-body" id="customers-container">
    <!-- JS will inject a paginated table here -->
  </div>
  <footer class="card-footer">
    <button data-action="refresh">Refresh</button>
  </footer>
</div>

<script type="module">
import { fetchJson } from '/js/api.js';      // shared fetch wrapper
import { renderTable } from '/js/ui_helpers.js';

const container = document.getElementById('customers-container');
const PAGE_ROWS = 15;

async function loadCustomers(page = 1) {
  try {
    const params = new URLSearchParams({ PageNumber: page, PageRows: PAGE_ROWS });
    const data = await fetchJson(`/api/get_customers.php?${params}`);
    container.innerHTML = renderTable({
      columns: ['CustomerCode', 'Description'],
      rows: data.Result,
      page: data.PageNumber,
      totalPages: Math.ceil(data.TotalRows / PAGE_ROWS),
      onPageChange: loadCustomers
    });
  } catch (err) {
    container.innerHTML = `<div class="error">Failed to load customers</div>`;
    console.error(err);
  }
}

// wire up the refresh button
document.querySelector('.customers-card [data-action="refresh"]')
  .addEventListener('click', () => loadCustomers());

// initial load
loadCustomers();
</script>
