<?php declare(strict_types=1);
// includes/table_helper.php
// -------------------------------------------------------------------
// Renders a searchable, sortable, pageable data table with Tailwind.
// Per-table settings icon to choose visible columns & page size.
// Hides both <th> and <td> via inline styles for unselected columns.
// -------------------------------------------------------------------

function renderDataTable(array $data, array $options = []): void {
    if (empty($data)) {
        echo '<p class="text-gray-400">No data to display.</p>';
        return;
    }

    // 1) Discover all columns from first row
    $allKeys = array_keys((array)$data[0]);

    // 2) Determine default visible columns (PHP side)
    $defaultVisibles = $options['defaultVisibleColumns'] ?? ['Description'];
    // Keep only valid keys
    $defaultVisibles = array_values(array_intersect($defaultVisibles, $allKeys));

    // 3) IDs and defaults
    $tableId       = uniqid('dt_');
    $settingsKey   = "{$tableId}_settings";
    $defaultSort   = $options['defaultSort']   ?? ($defaultVisibles[0] ?? $allKeys[0]);
    $defaultRows   = (int)($options['rowsPerPage'] ?? 10);
    $searchable    = $options['searchable']    ?? true;

    // 4) JSON data for JS
    $jsData = array_map(fn($row) => array_map(
        fn($c) => is_array($c) ? json_encode($c) : $c,
        (array)$row
    ), $data);
    $jsonData = json_encode($jsData, JSON_HEX_TAG|JSON_HEX_APOS);

    ?>
<div id="<?= $tableId ?>_wrapper"
     class="data-table-container mb-8 bg-gray-800/50 p-4 rounded-lg border border-gray-600 backdrop-blur-md">
  <div class="flex justify-between items-center mb-2">
    <?php if ($searchable): ?>
      <input type="text" id="<?= $tableId ?>_search" placeholder="Search…"
             class="w-1/2 text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500" />
    <?php endif; ?>

    <div class="relative">
      <button id="<?= $tableId ?>_settings_btn"
              class="p-1 rounded-md bg-gray-700 hover:bg-gray-600 transition"
              aria-label="Table settings">
        <i data-feather="settings" class="text-yellow-400 h-4 w-4"></i>
      </button>
      <div id="<?= $tableId ?>_settings_panel"
           class="hidden absolute right-0 mt-2 w-64 bg-gray-800 border border-gray-600 rounded-md shadow-lg p-3 z-10">
        <h3 class="text-white font-semibold mb-2">Table Settings</h3>
        <div class="mb-2">
          <label class="block text-gray-300 mb-1">Rows per page:</label>
          <input type="number" id="<?= $tableId ?>_rows_input" min="1"
                 class="w-full text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-2"
                 value="<?= $defaultRows ?>" />
        </div>
        <div class="max-h-48 overflow-y-auto">
          <?php foreach ($allKeys as $key): ?>
            <label class="flex items-center text-gray-200 mb-1">
              <input type="checkbox" data-col-key="<?= htmlspecialchars($key) ?>"
                     class="mr-2 form-checkbox h-4 w-4 text-cyan-500"
                     <?= in_array($key, $defaultVisibles, true) ? 'checked' : '' ?> />
              <?= htmlspecialchars($key) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <table id="<?= $tableId ?>" class="min-w-full divide-y divide-gray-600">
    <thead class="bg-gray-700">
      <tr>
        <?php foreach ($allKeys as $key):
            // Hide headers for non-default-visible columns
            $thStyle = in_array($key, $defaultVisibles, true) ? '' : 'display:none;';
        ?>
          <th data-key="<?= htmlspecialchars($key) ?>"
              style="<?= $thStyle ?>"
              class="cursor-pointer select-none px-4 py-2 text-left text-sm font-medium text-white uppercase tracking-wider">
            <?= htmlspecialchars($key) ?>
            <span class="sort-indicator">&nbsp;</span>
          </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody class="bg-gray-800 divide-y divide-gray-700"></tbody>
  </table>

  <div id="<?= $tableId ?>_pager" class="mt-3 flex flex-wrap gap-1"></div>
</div>

<script>
(function(){
  const wrapper   = document.getElementById('<?= $tableId ?>_wrapper');
  const data      = <?= $jsonData ?>;
  const columns   = <?= json_encode($allKeys) ?>;
  let filtered    = [...data];
  let currentPage = 1;
  let sortKey     = '<?= $defaultSort ?>';
  let sortDir     = 1;
  let rpp         = <?= $defaultRows ?>;

  // Settings elements
  const settingsBtn   = document.getElementById('<?= $tableId ?>_settings_btn');
  const settingsPanel = document.getElementById('<?= $tableId ?>_settings_panel');
  const rowsInput     = document.getElementById('<?= $tableId ?>_rows_input');
  const colCheckboxes = settingsPanel.querySelectorAll('input[type=checkbox][data-col-key]');

  // Load saved settings or fall back to PHP defaults
  let saved = {};
  try {
    saved = JSON.parse(localStorage.getItem('<?= $settingsKey ?>')) || {};
  } catch {}
  rpp = saved.rpp || rpp;
  rowsInput.value = rpp;

  // Track visible columns in JS
  let visibleCols = saved.visibleCols
      ? saved.visibleCols
      : <?= json_encode($defaultVisibles) ?>;

  // Initialize checkboxes and hide initial non-visible columns
  colCheckboxes.forEach(cb => {
    const key = cb.dataset.colKey;
    const isVisible = visibleCols.includes(key);
    cb.checked = isVisible;
    // also hide any corresponding <th>
    const idx = columns.indexOf(key);
    wrapper.querySelector(`th:nth-child(${idx+1})`).style.display = isVisible ? '' : 'none';
  });

  settingsBtn.addEventListener('click', () => settingsPanel.classList.toggle('hidden'));

  function applySettings() {
    rpp = Math.max(1, parseInt(rowsInput.value) || <?= $defaultRows ?>);

    visibleCols = [];
    colCheckboxes.forEach(cb => {
      const key = cb.dataset.colKey;
      const idx = columns.indexOf(key);
      const show = cb.checked;
      wrapper.querySelectorAll(
        `th:nth-child(${idx+1}), td:nth-child(${idx+1})`
      ).forEach(el => el.style.display = show ? '' : 'none');
      if (show) visibleCols.push(key);
    });

    localStorage.setItem('<?= $settingsKey ?>', JSON.stringify({rpp, visibleCols}));
    currentPage = 1;
    renderTable();
  }

  rowsInput.addEventListener('change', applySettings);
  colCheckboxes.forEach(cb => cb.addEventListener('change', applySettings));

  // Search
  const searchBox = document.getElementById('<?= $tableId ?>_search');
  if (searchBox) {
    searchBox.addEventListener('input', () => {
      const q = searchBox.value.toLowerCase();
      filtered = data.filter(r => JSON.stringify(r).toLowerCase().includes(q));
      currentPage = 1; renderTable();
    });
  }

  const tblBody = wrapper.querySelector('tbody');
  const ths     = wrapper.querySelectorAll('th[data-key]');
  const pager   = document.getElementById('<?= $tableId ?>_pager');

  function renderTable() {
    // Sort & paginate
    filtered.sort((a,b) => {
      const v1 = (a[sortKey]||'').toLowerCase();
      const v2 = (b[sortKey]||'').toLowerCase();
      return v1>v2? sortDir: v1<v2? -sortDir: 0;
    });
    const start = (currentPage-1)*rpp;
    const pageRows = filtered.slice(start, start+rpp);

    // Build rows
    tblBody.innerHTML = pageRows.map((row,i) => {
      const cls = i%2===0? 'bg-gray-800 hover:bg-gray-700':'bg-gray-700 hover:bg-gray-600';
      const cells = columns.map(key => {
        const show = visibleCols.includes(key);
        const style = show ? '' : 'style="display:none;"';
        return `<td ${style} class="px-4 py-2 text-gray-100">${row[key]||''}</td>`;
      });
      return `<tr class="${cls}">${cells.join('')}</tr>`;
    }).join('') || `<tr><td colspan="${columns.length}" class="px-4 py-2 text-center text-gray-300">No data</td></tr>`;

    renderPager();
    updateSortIndicators();
  }

  function renderPager() {
    const total = Math.max(1, Math.ceil(filtered.length/rpp));
    pager.innerHTML = Array.from({length: total}, (_, i) =>
      `<button data-page="${i+1}" class="px-2 py-1 rounded ${
        i+1===currentPage?'bg-cyan-500 text-black':'bg-gray-700 hover:bg-gray-600 text-white'
      }">${i+1}</button>`
    ).join('');
    pager.querySelectorAll('button').forEach(btn =>
      btn.addEventListener('click', () => {
        currentPage = +btn.dataset.page;
        renderTable();
      })
    );
  }

  function updateSortIndicators() {
    ths.forEach(th => {
      const ind = th.querySelector('.sort-indicator');
      ind.textContent = th.dataset.key === sortKey
        ? (sortDir===1 ? ' ▲' : ' ▼')
        : '';
    });
  }

  ths.forEach(th => th.addEventListener('click', () => {
    const k = th.dataset.key;
    if (sortKey === k) sortDir = -sortDir;
    else { sortKey = k; sortDir = 1; }
    renderTable();
  }));

  // Initial render
  renderTable();
})();
</script>
<?php } // end renderDataTable() ?>
