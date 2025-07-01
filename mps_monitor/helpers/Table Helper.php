<?php declare(strict_types=1);
// /includes/table_helper.php

/**
 * Renders a searchable, sortable, pageable data table from any array of rows.
 * This function generates the necessary HTML and JavaScript for a dynamic table.
 * It does not act as an API endpoint itself, but rather consumes data (which could come from an API).
 *
 * @param array $data    An array of associative arrays, where each inner array represents a row of data.
 * Nested arrays within a row will be JSON-encoded for display.
 * @param array $options An associative array of configuration options for the table:
 * - 'columns'      => array: An associative array mapping data keys to their desired header labels
 * (e.g., `['id' => 'ID', 'name' => 'Full Name']`). Defaults to keys of the first row if not provided.
 * - 'defaultSort'  => string: The key of the column to sort by default on initial render.
 * - 'rowsPerPage'  => int: The number of rows to display per page. Defaults to 10.
 * - 'searchable'   => bool: If true, a search input box will be rendered, enabling client-side search. Defaults to true.
 */
function renderDataTable(array $data, array $options = []): void {
    // If no data is provided, display a message and exit.
    if (empty($data)) {
        echo '<p class="text-gray-400">No data to display.</p>';
        return;
    }

    // Determine the columns to display and their headers.
    // If 'columns' option is not provided, use keys from the first row as both keys and labels.
    $first = (array)$data[0];
    $columns = $options['columns']
        ?? array_combine(array_keys($first), array_keys($first));
    $colKeys = array_keys($columns); // Get an ordered list of column keys

    // Set default sorting column and rows per page.
    $defaultSort = $options['defaultSort'] ?? ($colKeys[0] ?? ''); // Fallback for empty columns
    $rowsPerPage = (int)($options['rowsPerPage'] ?? 10);
    $searchable  = $options['searchable']  ?? true;

    // Generate unique IDs for the table elements to prevent conflicts if multiple tables are on a page.
    $uid = uniqid('dt_');
    $tableId   = $uid;
    $wrapperId = $uid . '_wrapper';
    $searchId  = $uid . '_search';
    $colsId    = $uid . '_cols';
    $pagerId   = $uid . '_pager';

    // Prepare data for JavaScript.
    // Nested arrays are JSON-encoded to be displayed as strings in table cells.
    $jsData = array_map(function($row) {
        return array_map(function($cell) {
            return is_array($cell) ? json_encode($cell, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $cell;
        }, (array)$row);
    }, $data);

    // Encode the entire dataset into a JSON string for JavaScript consumption.
    // JSON_HEX_* flags prevent HTML injection within JSON strings.
    $json = json_encode(
        $jsData,
        JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT
    );
    ?>
<div id="<?= htmlspecialchars($wrapperId) ?>" class="data-table-container mb-4">
  <?php if ($searchable): ?>
    <!-- Search input box -->
    <input
      type="text"
      id="<?= htmlspecialchars($searchId) ?>"
      placeholder="Search…"
      class="mb-2 w-full text-sm bg-gray-800 text-white border border-gray-700 rounded-md py-1 px-2 focus:outline-none focus:ring-1 focus:ring-cyan-500"
    />
  <?php endif; ?>

  <!-- Column visibility toggles -->
  <div id="<?= htmlspecialchars($colsId) ?>" class="mb-2 text-sm flex flex-wrap gap-x-4">
    <?php foreach ($columns as $key => $label): ?>
      <label class="inline-flex items-center">
        <input
          type="checkbox"
          data-dt-col="<?= htmlspecialchars($key) ?>"
          checked
          class="mr-1 form-checkbox h-4 w-4 text-cyan-500 rounded"
        />
        <?= htmlspecialchars($label) ?>
      </label>
    <?php endforeach; ?>
  </div>

  <!-- The main data table -->
  <table id="<?= htmlspecialchars($tableId) ?>" class="data-table w-full border-collapse">
    <thead>
      <tr>
        <?php foreach ($columns as $key => $label): ?>
          <th
            data-dt-key="<?= htmlspecialchars($key) ?>"
            class="cursor-pointer select-none px-2 py-1 text-left text-gray-200 bg-gray-800 border-b border-gray-700 rounded-tl-md rounded-tr-md"
          >
            <?= htmlspecialchars($label) ?>
            <span class="dt-sort-indicator">&nbsp;</span> <!-- Sort indicator (▲/▼) -->
          </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <!-- Table rows will be dynamically inserted here by JavaScript -->
    </tbody>
  </table>

  <!-- Pagination controls -->
  <div id="<?= htmlspecialchars($pagerId) ?>" class="table-pagination mt-2 flex flex-wrap gap-1 text-sm"></div>
</div>

<script>
(function(){
  // --- Data & Configuration ---
  const data        = <?= $json ?>; // The full dataset from PHP
  const columns     = <?= json_encode($colKeys) ?>; // Array of column keys in display order
  let filteredData  = [...data]; // Data after applying search filter
  let currentPage   = 1;         // Current page number for pagination
  let sortKey       = <?= json_encode($defaultSort) ?>; // Current column key for sorting
  let sortDirection = 1;         // 1 for ascending, -1 for descending
  const rowsPerPage = <?= $rowsPerPage ?>; // Number of rows per page

  // --- DOM Elements ---
  const wrapper     = document.getElementById('<?= htmlspecialchars($wrapperId) ?>');
  const tableBody   = wrapper.querySelector('tbody');
  const tableHeaders= wrapper.querySelectorAll('th[data-dt-key]');
  const pager       = document.getElementById('<?= htmlspecialchars($pagerId) ?>');
  const searchBox   = document.getElementById('<?= htmlspecialchars($searchId) ?>');
  const columnToggles= wrapper.querySelectorAll('input[data-dt-col]');

  // --- Renderer Functions ---

  /**
   * Renders the current page of the table based on filtered and sorted data.
   * Updates the table body and then re-renders the pager.
   */
  function renderTable() {
    // 1. Sort the filtered data.
    filteredData.sort((a, b) => {
      // Get values for sorting, convert to string and lowercase for case-insensitive comparison.
      const valA = (a[sortKey] || '').toString().toLowerCase();
      const valB = (b[sortKey] || '').toString().toLowerCase();

      // Perform string comparison.
      if (valA > valB) return sortDirection;
      if (valA < valB) return -sortDirection;
      return 0; // Values are equal
    });

    // 2. Paginate the sorted data.
    const startIndex = (currentPage - 1) * rowsPerPage;
    const pageRows = filteredData.slice(startIndex, startIndex + rowsPerPage);

    // 3. Build HTML for table rows and cells.
    tableBody.innerHTML = pageRows.map(row => {
      const cells = columns.map(key =>
        // Ensure cell content is safe for HTML display
        `<td class="px-2 py-1 border-b border-gray-700">${htmlspecialchars(row[key] ?? '')}</td>`
      );
      return `<tr>${cells.join('')}</tr>`;
    }).join('');

    // 4. Update the pagination controls.
    renderPager();
  }

  /**
   * Renders the pagination buttons based on the total number of pages.
   * Attaches event listeners to pagination buttons.
   */
  function renderPager() {
    const totalPages = Math.ceil(filteredData.length / rowsPerPage) || 1; // At least 1 page
    let pagerHtml = '';

    for (let i = 1; i <= totalPages; i++) {
      const buttonClasses = i === currentPage
        ? 'bg-cyan-600 text-white font-bold' // Active page styling
        : 'bg-gray-700 text-gray-200 hover:bg-gray-600'; // Inactive page styling
      pagerHtml += `<button data-page="${i}" class="px-3 py-1 rounded-md ${buttonClasses} transition-colors duration-200 ease-in-out shadow-sm">${i}</button>`;
    }
    pager.innerHTML = pagerHtml;

    // Attach click event listeners to all pagination buttons.
    pager.querySelectorAll('button').forEach(button =>
      button.addEventListener('click', () => {
        currentPage = Number(button.dataset.page); // Update current page
        renderTable(); // Re-render table for the new page
      })
    );
  }

  /**
   * Updates the visual sort indicators (▲/▼) in the table headers.
   */
  function updateSortIndicators() {
    tableHeaders.forEach(th => {
      const indicator = th.querySelector('.dt-sort-indicator');
      if (indicator) {
        if (th.dataset.dtKey === sortKey) {
          // Set indicator based on current sort direction.
          indicator.textContent = sortDirection === 1 ? ' ▲' : ' ▼';
        } else {
          // Clear indicator for non-sorted columns.
          indicator.textContent = '';
        }
      }
    });
  }

  /**
   * Simple HTML escaping function to prevent XSS.
   * Used for dynamically inserted content.
   * @param {string} str The string to escape.
   * @returns {string} The escaped string.
   */
  function htmlspecialchars(str) {
    if (typeof str !== 'string') {
      return str; // Return non-string values as is
    }
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return str.replace(/[&<>"']/g, function(m) { return map[m]; });
  }

  // --- Event Listeners ---

  // Event listener for sorting by column headers.
  tableHeaders.forEach(th => {
    th.addEventListener('click', () => {
      const key = th.dataset.dtKey;
      if (key) { // Ensure a data-dt-key is present
        if (sortKey === key) {
          // If clicking the same column, reverse sort direction.
          sortDirection = -sortDirection;
        } else {
          // If clicking a new column, set it as sort key and default to ascending.
          sortKey = key;
          sortDirection = 1;
        }
        updateSortIndicators(); // Update visual indicators
        currentPage = 1; // Reset to first page on new sort
        renderTable();    // Re-render table
      }
    });
  });

  // Event listener for the search input box.
  if (searchBox) {
    searchBox.addEventListener('input', () => {
      const query = searchBox.value.toLowerCase();
      // Filter data: check if any stringified row content includes the query.
      filteredData = data.filter(row =>
        JSON.stringify(row).toLowerCase().includes(query)
      );
      currentPage = 1; // Reset to first page after search
      renderTable();    // Re-render table
    });
  }

  // Event listeners for column visibility toggles.
  columnToggles.forEach(checkbox => {
    checkbox.addEventListener('change', () => {
      const columnKey = checkbox.dataset.dtCol;
      // Find the index of the column to toggle (1-based for CSS nth-child).
      const columnIndex = columns.indexOf(columnKey) + 1;
      // Select all header and data cells for the specific column.
      const selector = `table th:nth-child(${columnIndex}), table td:nth-child(${columnIndex})`;
      wrapper.querySelectorAll(selector).forEach(cell => {
        // Set display style based on checkbox checked state.
        cell.style.display = checkbox.checked ? '' : 'none';
      });
    });
  });

  // --- Initial Render ---
  updateSortIndicators(); // Set initial sort indicator
  renderTable();          // Render the table on page load
})();
</script>
<?php
} // end function renderDataTable
