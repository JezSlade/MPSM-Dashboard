<?php declare(strict_types=1);
// includes/table_helper.php
// -------------------------------------------------------------------
// Renders a data table with Tailwind styling, searchable dropdown,
// column visibility settings (only Description visible by default),
// and row-click selection that sets the global “customer” cookie.
// -------------------------------------------------------------------

require_once __DIR__ . '/searchable_dropdown.php';

function renderDataTable(array $data, array $options = []): void
{
    if (empty($data)) {
        echo '<p class="text-gray-400">No data to display.</p>';
        return;
    }

    // 1) Determine columns and labels from the first row
    $first   = (array)$data[0];
    $columns = array_keys($first);
    $labels  = $options['labels'] ?? array_combine($columns, $columns);

    // 2) Settings
    $sortable       = $options['sortable']     ?? true;
    $searchable     = $options['searchable']   ?? true;
    $pageRows       = $options['rowsPerPage']  ?? 15;
    $rowSelectKey   = $options['rowSelectKey'] ?? 'CustomerCode';
    $rowSelectParam = $options['rowSelectParam'] ?? $rowSelectKey;

    // 3) Unique IDs
    $tableId       = 'tbl_' . preg_replace('/[^a-z0-9_]/i','', uniqid());
    $wrapperId     = $tableId . '_wrapper';
    $settingsBtnId = $tableId . '_settings_btn';
    $settingsPanel = $tableId . '_settings_panel';
    $searchInputId = $tableId . '_search';
    $datalistId    = $tableId . '_datalist';

    // 4) Prepare JSON
    $jsRows = array_map(
        fn($row) => (object) array_intersect_key((array)$row, array_flip($columns)),
        $data
    );
    $jsMeta = (object)[
        'columns'        => $columns,
        'labels'         => $labels,
        'sortable'       => (bool)$sortable,
        'searchable'     => (bool)$searchable,
        'pageRows'       => (int)$pageRows,
        'rowSelectKey'   => $rowSelectKey,
        'rowSelectParam' => $rowSelectParam,
    ];
    $jsonData = json_encode((object)[
        'rows' => $jsRows,
        'meta' => $jsMeta
    ], JSON_HEX_TAG|JSON_HEX_APOS);
    ?>

<div id="<?= $wrapperId ?>" class="mb-6 bg-gray-800/50 p-4 rounded-lg border border-gray-600 backdrop-blur-md">
  <div class="flex justify-between items-center mb-2">
    <?php if ($searchable): ?>
      <?php
        renderSearchableDropdown(
          $searchInputId,
          $datalistId,
          '/api/get_customers.php',
          'customer',
          'Filter…',
          'w-1/2 text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500'
        );
      ?>
    <?php endif; ?>

    <div class="relative inline-block">
      <button id="<?= $settingsBtnId ?>"
              class="p-1 ml-2 rounded-md bg-gray-700 hover:bg-gray-600 transition"
              aria-label="Column settings">
        <i data-feather="settings" class="text-yellow-400 h-4 w-4"></i>
      </button>
      <div id="<?= $settingsPanel ?>"
           class="hidden absolute right-0 mt-1 w-56 bg-gray-800 border border-gray-600 rounded-md shadow-lg p-3 z-20">
        <h3 class="text-white font-semibold mb-2">Columns</h3>
        <div class="max-h-40 overflow-y-auto">
          <?php foreach ($columns as $col): ?>
            <label class="flex items-center text-gray-200 mb-1">
              <input type="checkbox"
                     data-col="<?= $col ?>"
                     <?= $col === 'Description' ? 'checked' : '' ?>
                     class="mr-2 form-checkbox h-4 w-4 text-cyan-500">
              <?= htmlspecialchars($labels[$col], ENT_QUOTES) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div id="<?= $tableId ?>" class="overflow-auto">
    <table class="min-w-full divide-y divide-gray-700 text-sm">
      <thead><tr><!-- JS will populate headers --></tr></thead>
      <tbody class="bg-gray-800 divide-y divide-gray-600"><!-- JS will populate rows --></tbody>
    </table>
  </div>
</div>

<script>
(function(){
  const wrapper = document.getElementById('<?= $wrapperId ?>');
  const data    = <?= $jsonData ?>;
  const cookieName = data.meta.rowSelectParam;

  let sortKey     = data.meta.sortable ? data.meta.columns[0] : null;
  let sortDir     = 1;
  let currentPage = 1;

  const Cookie = {
    set(name, value) {
      document.cookie = `${name}=${encodeURIComponent(value)};path=/`;
    }
  };

  function renderHeader(visibleCols) {
    const theadRow = wrapper.querySelector('thead tr');
    theadRow.innerHTML = visibleCols.map(col =>
      `<th data-key="${col}" class="px-4 py-2 text-left ${data.meta.sortable?'cursor-pointer hover:text-cyan-400':''}">
         ${data.meta.labels[col]}
       </th>`
    ).join('');
    // attach sort handlers
    wrapper.querySelectorAll('th[data-key]').forEach(th =>
      th.addEventListener('click', () => {
        const col = th.dataset.key;
        if (sortKey === col) sortDir = -sortDir;
        else { sortKey = col; sortDir = 1; }
        render();
      })
    );
  }

  function render() {
    // determine visible columns
    const visibleCols = Array.from(
      document.querySelectorAll('#<?= $settingsPanel ?> input[type="checkbox"]:checked')
    ).map(cb => cb.dataset.col);

    renderHeader(visibleCols);

    let rows = [...data.rows];

    // sort
    if (data.meta.sortable && sortKey) {
      rows.sort((a,b) => {
        const va = a[sortKey], vb = b[sortKey];
        return ((va>vb)?1:(va<vb)?-1:0)*sortDir;
      });
    }

    // paginate
    const total = Math.ceil(rows.length/data.meta.pageRows);
    if (currentPage>total) currentPage= total||1;
    const start = (currentPage-1)*data.meta.pageRows;
    const page  = rows.slice(start, start+data.meta.pageRows);

    // render body
    const tbody = wrapper.querySelector('tbody');
    tbody.innerHTML = page.map(r => {
      const cells = visibleCols.map(col =>
        `<td class="px-4 py-2">${String(r[col]||'')}</td>`
      ).join('');
      const code = r[data.meta.rowSelectKey] || '';
      return `<tr class="cursor-pointer" data-customer="${code}">${cells}</tr>`;
    }).join('');
  }

  // toggle settings panel
  document.getElementById('<?= $settingsBtnId ?>')
    .addEventListener('click', ()=>document.getElementById('<?= $settingsPanel ?>').classList.toggle('hidden'));

  // re-render on column toggle
  document.querySelectorAll('#<?= $settingsPanel ?> input[type="checkbox"]')
    .forEach(cb=>cb.addEventListener('change', render));

  // row click
  wrapper.querySelector('tbody')
    .addEventListener('click', e=>{
      const row=e.target.closest('tr[data-customer]');
      if(!row) return;
      const code = row.dataset.customer;
      if(!code) return;
      Cookie.set(cookieName, code);
      window.location.reload();
    });

  // initial render
  render();
})();
</script>
<?php
} // end renderDataTable()
