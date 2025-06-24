<?php declare(strict_types=1);
// includes/table_helper.php
// -------------------------------------------------------------------
// Renders a searchable, sortable, pageable data table with Tailwind.
// Adds a per-table settings dropdown to choose visible columns & page size.
// -------------------------------------------------------------------

/**
 * @param array $data
 *   Array of associative arrays (rows).
 * @param array $options [
 *   'columns'     => [ 'key' => 'Header Label', ... ],  // default visible columns
 *   'defaultSort' => 'key',                             // default sort key
 *   'rowsPerPage' => int,                               // default page size
 *   'searchable'  => bool,                              // show search box
 * ]
 */
function renderDataTable(array $data, array $options = []): void {
    if (empty($data)) {
        echo '<p class="text-gray-400">No data to display.</p>';
        return;
    }

    // Determine all columns from first row
    $allKeys = array_keys((array)$data[0]);
    $allCols = $options['columns'] 
             ?? array_combine($allKeys, $allKeys);

    // Load table-specific settings from localStorage (JS) by a unique ID
    $tableId = uniqid('dt_');
    $settingsKey = "{$tableId}_settings";

    $defaultSort = $options['defaultSort'] ?? array_key_first($allCols);
    $defaultRows = $options['rowsPerPage'] ?? 10;
    $searchable  = $options['searchable'] ?? true;

    // Prepare JSON data for JS
    $jsData = array_map(function($row) use ($allKeys) {
        return array_map(function($cell) {
            return is_array($cell) ? json_encode($cell) : $cell;
        }, array_merge([], $row));
    }, $data);
    $jsonData = json_encode($jsData, JSON_HEX_TAG|JSON_HEX_APOS);

    ?>
<div id="<?= $tableId ?>_wrapper" class="data-table-container mb-8 bg-gray-800/50 p-4 rounded-lg
                                      border border-gray-600 backdrop-blur-md">
  <div class="flex justify-between items-center mb-3">
    <?php if ($searchable): ?>
      <input type="text" id="<?= $tableId ?>_search"
             placeholder="Search…"
             class="w-1/2 text-sm bg-gray-700 text-white border border-gray-600 
                    rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500" />
    <?php endif; ?>

    <!-- Settings icon -->
    <div class="relative">
      <button id="<?= $tableId ?>_settings_btn"
              class="p-2 rounded-md bg-gray-700 hover:bg-gray-600 transition"
              aria-label="Table settings">
        <i data-feather="settings" class="text-yellow-400"></i>
      </button>
      <div id="<?= $tableId ?>_settings_panel"
           class="hidden absolute right-0 mt-2 w-64 bg-gray-800 border border-gray-600 rounded-md
                  shadow-lg p-4 z-10">
        <h3 class="text-white font-semibold mb-2">Table Settings</h3>
        <div class="mb-3">
          <label class="block text-gray-300 mb-1">Rows per page:</label>
          <input type="number" id="<?= $tableId ?>_rows_input"
                 min="1" class="w-full text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-2"
                 value="<?= $defaultRows ?>" />
        </div>
        <div>
          <span class="block text-gray-300 mb-1">Columns:</span>
          <?php foreach ($allCols as $key => $label): ?>
            <label class="flex items-center text-gray-200 mb-1">
              <input type="checkbox" data-col-key="<?= htmlspecialchars($key) ?>"
                     class="mr-2 form-checkbox h-4 w-4 text-cyan-500"
                     checked />
              <?= htmlspecialchars($label) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <table id="<?= $tableId ?>" class="min-w-full divide-y divide-gray-600">
    <thead class="bg-gray-700">
      <tr>
        <?php foreach ($allCols as $key => $label): ?>
          <th data-key="<?= htmlspecialchars($key) ?>"
              class="cursor-pointer select-none px-4 py-2 text-left text-sm font-medium text-white uppercase tracking-wider">
            <?= htmlspecialchars($label) ?>
            <span class="sort-indicator">&nbsp;</span>
          </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody class="bg-gray-800 divide-y divide-gray-700"></tbody>
  </table>

  <div id="<?= $tableId ?>_pager" class="mt-4 flex flex-wrap gap-1"></div>
</div>

<script>
(function(){
  const wrapper   = document.getElementById('<?= $tableId ?>_wrapper');
  const data      = <?= $jsonData ?>;
  const columns   = <?= json_encode(array_keys($allCols)) ?>;
  let filtered    = [...data];
  let currentPage = 1;
  let sortKey     = '<?= $defaultSort ?>';
  let sortDir     = 1; // 1=asc, -1=desc
  let rpp         = <?= $defaultRows ?>;

  // Settings panel
  const settingsBtn   = document.getElementById('<?= $tableId ?>_settings_btn');
  const settingsPanel = document.getElementById('<?= $tableId ?>_settings_panel');
  const rowsInput     = document.getElementById('<?= $tableId ?>_rows_input');
  const colCheckboxes = settingsPanel.querySelectorAll('input[type=checkbox][data-col-key]');

  // Load saved settings
  const saved = localStorage.getItem('<?= $settingsKey ?>');
  if (saved) {
    try {
      const s = JSON.parse(saved);
      if (s.rpp) rpp = s.rpp;
      if (Array.isArray(s.visibleCols)) {
        colCheckboxes.forEach(cb => {
          cb.checked = s.visibleCols.includes(cb.dataset.colKey);
        });
      }
    } catch {}
    rowsInput.value = rpp;
  }

  // Toggle settings panel
  settingsBtn.addEventListener('click', () => {
    settingsPanel.classList.toggle('hidden');
  });

  // Apply settings
  function applySettings() {
    // Rows per page
    rpp = Math.max(1, parseInt(rowsInput.value, 10) || <?= $defaultRows ?>);

    // Columns
    const visible = [];
    colCheckboxes.forEach(cb => {
      const idx = columns.indexOf(cb.dataset.colKey);
      const cells = wrapper.querySelectorAll(
        `th:nth-child(${idx+1}), td:nth-child(${idx+1})`
      );
      cells.forEach(el => el.style.display = cb.checked ? '' : 'none');
      if (cb.checked) visible.push(cb.dataset.colKey);
    });

    // Persist
    localStorage.setItem('<?= $settingsKey ?>', JSON.stringify({
      rpp, visibleCols: visible
    }));

    currentPage = 1;
    renderTable();
  }

  rowsInput.addEventListener('change', applySettings);
  colCheckboxes.forEach(cb => cb.addEventListener('change', applySettings));

  // Search box
  const searchBox = document.getElementById('<?= $tableId ?>_search');
  if (searchBox) {
    searchBox.addEventListener('input', () => {
      const q = searchBox.value.toLowerCase();
      filtered = data.filter(row =>
        JSON.stringify(row).toLowerCase().includes(q)
      );
      currentPage = 1;
      renderTable();
    });
  }

  // Table elements
  const tblBody = wrapper.querySelector('tbody');
  const ths     = wrapper.querySelectorAll('th[data-key]');
  const pager   = document.getElementById('<?= $tableId ?>_pager');

  // Render functions
  function renderTable() {
    // Sort
    filtered.sort((a,b) => {
      const v1 = (a[sortKey]||'').toString().toLowerCase();
      const v2 = (b[sortKey]||'').toString().toLowerCase();
      return v1 > v2 ? sortDir : v1 < v2 ? -sortDir : 0;
    });
    // Paginate
    const start = (currentPage-1)*rpp;
    const pageRows = filtered.slice(start, start+rpp);
    // Build rows
    tblBody.innerHTML = pageRows.map((row,i) => {
      const cls = i % 2 === 0 ? 'bg-gray-800 hover:bg-gray-700' : 'bg-gray-700 hover:bg-gray-600';
      const cells = columns.map(key => `<td class="px-4 py-3 text-gray-100">${row[key]||''}</td>`);
      return `<tr class="${cls}">${cells.join('')}</tr>`;
    }).join('') || `
      <tr><td colspan="${columns.length}" class="px-4 py-4 text-center text-gray-300">
        No data
      </td></tr>`;

    renderPager();
    updateSortIndicators();
  }

  function renderPager() {
    const totalPages = Math.max(1, Math.ceil(filtered.length/rpp));
    pager.innerHTML = Array.from({length: totalPages}, (_,i) =>
      `<button data-page="${i+1}"
               class="px-3 py-1 rounded ${
                 i+1===currentPage ? 'bg-cyan-500 text-black' : 'bg-gray-700 hover:bg-gray-600 text-white'
               }">
         ${i+1}
       </button>`
    ).join('');

    pager.querySelectorAll('button').forEach(btn =>
      btn.addEventListener('click', () => {
        currentPage = Number(btn.dataset.page);
        renderTable();
      })
    );
  }

  function updateSortIndicators() {
    ths.forEach(th => {
      const ind = th.querySelector('.sort-indicator');
      ind.textContent = th.dataset.key === sortKey
        ? (sortDir===1? ' ▲':' ▼')
        : '';
    });
  }

  // Sorting
  ths.forEach(th => {
    th.addEventListener('click', () => {
      const key = th.dataset.key;
      if (sortKey===key) sortDir = -sortDir;
      else { sortKey=key; sortDir=1; }
      renderTable();
    });
  });

  // Initial render
  applySettings();
  renderTable();
})();
</script>
<?php
} // end renderDataTable
