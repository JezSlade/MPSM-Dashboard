<?php declare(strict_types=1);
// includes/table_helper.php
// -------------------------------------------------------------------
// Renders a searchable, sortable, pageable data table with Tailwind.
// Per-table settings (columns & rows-per-page) + optional row selection.
// Clicking a row with `rowSelectKey` reloads page with ?<param>=<value>.
// -------------------------------------------------------------------

function renderDataTable(array $data, array $options = []): void {
    if (empty($data)) {
        echo '<p class="text-gray-400">No data to display.</p>';
        return;
    }

    // 1. Columns & labels
    $first            = (array)$data[0];
    $columns          = $options['columns'] 
                      ?? array_combine(array_keys($first), array_keys($first));
    $colKeys          = array_keys($columns);

    // 2. Row-selection config
    $selectKey        = $options['rowSelectKey']   ?? null;
    $selectParam      = $options['rowSelectParam'] 
                      ?? ($selectKey ? strtolower($selectKey) : null);

    // 3. Defaults & IDs
    $defaultVisibles  = $options['defaultVisibleColumns'] ?? $colKeys;
    $defaultSort      = $options['defaultSort']           ?? $colKeys[0];
    $rowsPerPage      = (int)($options['rowsPerPage']     ?? 10);
    $searchable       = $options['searchable']            ?? true;
    $tableId          = uniqid('dt_');
    $settingsKey      = "{$tableId}_settings";

    // 4. JSON-encode data
    $jsData = array_map(fn($r) => array_map(
      fn($c) => is_array($c) ? json_encode($c) : $c,
      (array)$r
    ), $data);
    $jsonData = json_encode($jsData, JSON_HEX_TAG|JSON_HEX_APOS);
    ?>
<div id="<?= $tableId ?>_wrapper" class="mb-6 bg-gray-800/50 p-4 rounded-lg border border-gray-600 backdrop-blur-md">
  <div class="flex justify-between items-center mb-2">
    <?php if ($searchable): ?>
      <input id="<?= $tableId ?>_search" type="text" placeholder="Search…"
             class="w-1/2 text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500"/>
    <?php endif; ?>

    <!-- Table Settings Button & Panel -->
    <div class="relative inline-block">
      <button id="<?= $tableId ?>_settings_btn"
              class="p-1 ml-2 rounded-md bg-gray-700 hover:bg-gray-600 transition"
              aria-label="Table settings">
        <i data-feather="settings" class="text-yellow-400 h-4 w-4"></i>
      </button>
      <div id="<?= $tableId ?>_settings_panel"
           class="hidden absolute right-0 mt-1 w-56 bg-gray-800 border border-gray-600 rounded-md shadow-lg p-3 z-20 pointer-events-auto">
        <h3 class="text-white font-semibold mb-2">Columns &amp; Rows</h3>
        <div class="mb-2">
          <label class="block text-gray-300 mb-1">Rows per page:</label>
          <input type="number" id="<?= $tableId ?>_rows_input" min="1"
                 class="w-full text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-2"
                 value="<?= $rowsPerPage ?>" />
        </div>
        <div class="max-h-40 overflow-y-auto">
          <?php foreach ($colKeys as $key): ?>
            <label class="flex items-center text-gray-200 mb-1">
              <input type="checkbox" data-col-key="<?= htmlspecialchars($key) ?>"
                     class="mr-2 form-checkbox h-4 w-4 text-cyan-500"
                     <?= in_array($key, $defaultVisibles, true) ? 'checked' : '' ?> />
              <?= htmlspecialchars($columns[$key]) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table id="<?= $tableId ?>" class="min-w-full divide-y divide-gray-600">
      <thead class="bg-gray-700">
        <tr>
          <?php foreach ($colKeys as $key):
              $hide = in_array($key, $defaultVisibles, true) ? '' : 'hidden';
          ?>
            <th data-key="<?= htmlspecialchars($key) ?>"
                class="<?= $hide ?> px-4 py-2 text-left text-sm font-semibold text-white cursor-pointer">
              <?= htmlspecialchars($columns[$key]) ?>
              <span class="ml-1 text-xs dt-sort-indicator"></span>
            </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody class="bg-gray-800"></tbody>
    </table>
  </div>

  <div id="<?= $tableId ?>_pager" class="mt-3 flex justify-center gap-1 text-sm"></div>
</div>

<script>
(function(){
  const wrapper     = document.getElementById('<?= $tableId ?>_wrapper');
  const data        = <?= $jsonData ?>;
  const columns     = <?= json_encode($colKeys) ?>;
  const selectKey   = <?= json_encode($selectKey) ?>;
  const selectParam = <?= json_encode($selectParam) ?>;
  let filtered      = [...data];
  let pageIdx       = 1;
  let sortKey       = <?= json_encode($defaultSort) ?>;
  let sortDir       = 1;
  let rpp           = <?= $rowsPerPage ?>;

  const searchBox   = document.getElementById('<?= $tableId ?>_search');
  const tblBody     = wrapper.querySelector('tbody');
  const ths         = wrapper.querySelectorAll('th[data-key]');
  const pager       = document.getElementById('<?= $tableId ?>_pager');
  const settingsBtn   = document.getElementById('<?= $tableId ?>_settings_btn');
  const settingsPanel = document.getElementById('<?= $tableId ?>_settings_panel');
  const rowsInput     = document.getElementById('<?= $tableId ?>_rows_input');
  const colCheckboxes = settingsPanel.querySelectorAll('input[type=checkbox][data-col-key]');

  // Show/hide settings panel
  settingsBtn.addEventListener('click', e => {
    e.stopPropagation();
    settingsPanel.classList.toggle('hidden');
  });
  document.addEventListener('click', e => {
    if (!settingsPanel.classList.contains('hidden') &&
        !settingsPanel.contains(e.target) &&
        !settingsBtn.contains(e.target)) {
      settingsPanel.classList.add('hidden');
    }
  });

  // Apply settings (rows & columns)
  function applySettings(){
    rpp = Math.max(1, parseInt(rowsInput.value)||<?= $rowsPerPage ?>);
    pageIdx = 1;
    const visibleCols = [];
    colCheckboxes.forEach(cb=>{
      const key = cb.dataset.colKey;
      const idx = columns.indexOf(key);
      const show = cb.checked;
      document.querySelectorAll(
        `#<?= $tableId ?> th:nth-child(${idx+1}), #<?= $tableId ?> td:nth-child(${idx+1})`
      ).forEach(el=>el.classList.toggle('hidden', !show));
      if(show) visibleCols.push(key);
    });
    render();
  }
  rowsInput.addEventListener('change', applySettings);
  colCheckboxes.forEach(cb=>cb.addEventListener('change', applySettings));

  // Search filter
  if(searchBox){
    searchBox.addEventListener('input', ()=>{
      const q = searchBox.value.toLowerCase();
      filtered = data.filter(r=> JSON.stringify(r).toLowerCase().includes(q) );
      pageIdx = 1;
      render();
    });
  }

  // Build table rows
  function render(){
    // Sort
    filtered.sort((a,b)=>{
      const v1=(a[sortKey]||'').toString().toLowerCase();
      const v2=(b[sortKey]||'').toString().toLowerCase();
      return v1>v2?sortDir:v1<v2?-sortDir:0;
    });
    // Paginate
    const start = (pageIdx-1)*rpp;
    const slice = filtered.slice(start, start+rpp);

    tblBody.innerHTML = slice.map((row,i)=>{
      const bg = i%2? 'bg-gray-700':'bg-gray-800';
      const hov= selectKey? ' hover:bg-gray-600 cursor-pointer':'';
      const attr = selectKey && row[selectKey]!=null
        ? ` data-select-value="${encodeURIComponent(row[selectKey])}"`
        : '';
      const tds = columns.map(key=>
        `<td class="px-4 py-2 text-gray-100"${attr}>${row[key]||''}</td>`
      ).join('');
      return `<tr class="${bg+hov}"${attr}>${tds}</tr>`;
    }).join('') || `<tr><td colspan="${columns.length}" class="px-4 py-2 text-center text-gray-400">No data</td></tr>`;

    attachClicks();
    renderPager();
    updateSortIndicators();
  }

  // Attach row click handlers
  function attachClicks(){
    if(!selectKey||!selectParam) return;
    wrapper.querySelectorAll('tr[data-select-value]').forEach(tr=>{
      tr.onclick = ()=>{
        const v = decodeURIComponent(tr.getAttribute('data-select-value'));
        const url = new URL(window.location.href);
        url.searchParams.set(selectParam, v);
        window.location.href = url;
      };
    });
  }

  // Pagination
  function renderPager(){
    const total = Math.max(1, Math.ceil(filtered.length/rpp));
    pager.innerHTML = Array.from({length:total}, (_,i)=>
      `<button data-page="${i+1}"
               class="px-2 py-1 rounded ${
                 i+1===pageIdx?'bg-cyan-500 text-black':'bg-gray-700 hover:bg-gray-600 text-white'
               }">${i+1}</button>`
    ).join('');
    pager.querySelectorAll('button').forEach(btn=>
      btn.onclick = ()=>{ pageIdx = +btn.dataset.page; render(); }
    );
  }

  // Sort indicators
  function updateSortIndicators(){
    ths.forEach(th=>{
      const ind = th.querySelector('.dt-sort-indicator');
      ind.textContent = th.dataset.key===sortKey
        ? (sortDir===1?' ▲':' ▼')
        : '';
    });
  }

  // Column header click → sort
  ths.forEach(th=> th.onclick = ()=>{
    const k = th.dataset.key;
    if(sortKey===k) sortDir=-sortDir;
    else { sortKey=k; sortDir=1; }
    render();
  });

  // Initial render
  render();
})();
</script>
<?php } // end renderDataTable() ?>
