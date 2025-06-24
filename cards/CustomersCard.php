<?php
// cards/CustomersCard.php
// -------------------------------------------------------------------
// Customers card: glassmorphic Tailwind + neon‐CMYK glow + compact layout
// -------------------------------------------------------------------
require_once __DIR__ . '/../includes/card_base.php';
?>
<div class="max-w-2xl mx-auto 
            bg-white bg-opacity-10 backdrop-blur-md 
            border border-white border-opacity-20 
            rounded-lg shadow-[0_0_10px_rgba(0,255,255,0.4),0_0_20px_rgba(255,0,255,0.3),0_0_30px_rgba(255,255,0,0.2)]
            mb-6 overflow-hidden">
  <header class="flex justify-between items-center px-4 py-2 
                 bg-white bg-opacity-20 border-b border-white border-opacity-10">
    <h2 class="text-lg font-semibold text-white">Customers</h2>
    <button data-action="refresh"
      class="p-1 rounded-md bg-white bg-opacity-20 hover:bg-opacity-30 transition">
      <i data-feather="refresh-ccw" class="text-magenta-400"></i>
    </button>
  </header>
  <div class="p-4" id="customers-container">
    <!-- injected by JS -->
  </div>
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
      container.innerHTML = `<div class="text-center text-white">No customers found.</div>`;
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
    container.innerHTML = `<div class="text-center text-red-400">Failed to load customers.</div>`;
    console.error(err);
  }
}

document.querySelector('[data-action="refresh"]').addEventListener('click', () => loadCustomers());
loadCustomers();
</script>
