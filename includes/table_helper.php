<?php declare(strict_types=1);
// includes/table_helper.php
// -------------------------------------------------------------------
// Renders a searchable, sortable, pageable data table with Tailwind.
// Per‐table settings (columns & rows‐per‐page) + optional row selection.
// Clicking a row with `rowSelectKey` reloads page with ?<param>=<value>.
// -------------------------------------------------------------------

function renderDataTable(array $data, array $options = []): void {
    if (empty($data)) {
        echo '<p class="text-gray-400">No data to display.</p>';
        return;
    }

    // 1. Columns & labels
    $first           = (array)$data[0];
    $columns         = $options['columns']      ?? array_keys($first);
    $labels          = $options['labels']       ?? array_combine($columns, $columns);
    $sortable        = $options['sortable']     ?? true;
    $searchable      = $options['searchable']   ?? true;
    $pageRows        = $options['rowsPerPage']  ?? 15;
    $rowSelectKey    = $options['rowSelectKey'] ?? null; // e.g. 'CustomerCode'
    $rowSelectParam  = $options['rowSelectParam'] ?? $rowSelectKey;

    // 2. Unique IDs
    $tableId       = 'tbl_' . preg_replace('/[^a-z0-9_]/i','', uniqid());
    $wrapperId     = $tableId . '_wrapper';
    $settingsBtnId = $tableId . '_settings_btn';
    $settingsPanel = $tableId . '_settings_panel';
    $rowsInputId   = $tableId . '_rows_input';

    // 3. Prepare JSON for client‐side render() function
    $jsData = array_map(fn($r) => (object)array_intersect_key((array)$r, array_flip($columns)), $data);
    $jsMeta = (object)[
        'columns'      => $columns,
        'labels'       => $labels,
        'sortable'     => (bool)$sortable,
        'searchable'   => (bool)$searchable,
        'pageRows'     => (int)$pageRows,
        'rowSelectKey' => $rowSelectKey,
        'rowSelectParam' => $rowSelectParam,
    ];
    $jsonData = json_encode((object)[
        'rows' => $jsData,
        'meta' => $jsMeta
    ], JSON_HEX_TAG|JSON_HEX_APOS);
    ?>

<div id="<?= $wrapperId ?>" class="mb-6 bg-gray-800/50 p-4 rounded-lg border border-gray-600 backdrop-blur-md">
  <div class="flex justify-between items-center mb-2">
    <?php if ($searchable): ?>
      <?php
      // Render searchable dropdown instead of plain text input
      // Parameters: input ID, datalist ID, API endpoint, cookie name, placeholder, CSS classes
      renderSearchableDropdown(
        id:           $tableId . '_search',
        datalistId:   $tableId . '_datalist',
        apiEndpoint:  '/api/get_customers.php',
        cookieName:   'customer',
        placeholder:  'Search…',
        cssClasses:   'w-1/2 text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500'
      );
      ?>
    <?php endif; ?>

    <!-- Table Settings Button & Panel -->
    <div class="relative inline-block">
      <button id="<?= $settingsBtnId ?>"
              class="p-1 ml-2 rounded-md bg-gray-700 hover:bg-gray-600 transition"
              aria-label="Table settings">
        <i data-feather="settings" class="text-yellow-400 h-4 w-4"></i>
      </button>
      <div id="<?= $settingsPanel ?>"
           class="hidden absolute right-0 mt-1 w-56 bg-gray-800 border border-gray-600 rounded-md shadow-lg p-3 z-20 pointer-events-auto">
        <h3 class="text-white font-semibold mb-2">Columns &amp; Rows</h3>
        <div class="mb-2">
          <label for="<?= $rowsInputId ?>" class="block text-gray-300 mb-1">Rows per page:</label>
          <input type="number" id="<?= $rowsInputId ?>" min="1"
                 class="w-full text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-2"
                 value="<?= $pageRows ?>"/>
        </div>
        <div class="max-h-40 overflow-y-auto">
          <?php foreach ($columns as $col): ?>
            <label class="flex items-center text-gray-200 mb-1">
              <input type="checkbox"
                     data-col="<?= $col ?>"
                     checked
                     class="mr-2 form-checkbox h-4 w-4 text-cyan-500">
              <?= htmlspecialchars($labels[$col] ?? $col) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Table container -->
  <div id="<?= $tableId ?>" class="overflow-auto">
    <table class="min-w-full divide-y divide-gray-700 text-sm">
      <thead>
        <tr>
          <?php foreach ($columns as $col): ?>
            <th
              data-key="<?= $col ?>"
              class="px-4 py-2 text-left <?= $sortable ? 'cursor-pointer hover:text-cyan-400' : '' ?>"
            >
              <?= htmlspecialchars($labels[$col] ?? $col) ?>
            </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody class="bg-gray-800 divide-y divide-gray-600">
        <!-- Rows will be rendered by client‐side JS -->
      </tbody>
    </table>
  </div>
</div>

<script>
(function(){
  const wrapper = document.getElementById('<?= $wrapperId ?>');
  const data    = <?= $jsonData ?>;
  let sortKey   = data.meta.sortable ? data.meta.columns[0] : null;
  let sortDir   = 1; // 1=asc, -1=desc
  let currentPage = 1;

  // Utility to read/set cookie for row selection or search
  const Cookie = {
    get(name) {
      return document.cookie.split('; ').reduce((r, c) => {
        const [k, v] = c.split('=');
        return k===name ? decodeURIComponent(v) : r;
      }, null);
    },
    set(name, value) {
      document.cookie = `${name}=${encodeURIComponent(value)};path=/`;
    }
  };

  function render() {
    let rows = [...data.rows];

    // Apply search filter if searchable
    const searchInput = document.getElementById('<?= $tableId ?>_search');
    if (data.meta.searchable && searchInput?.value) {
      const term = searchInput.value.toLowerCase();
      rows = rows.filter(r =>
        data.meta.columns.some(k => String(r[k]).toLowerCase().includes(term))
      );
      Cookie.set('<?= $rowSelectParam ?>', searchInput.value);
    }

    // Sorting
    if (data.meta.sortable && sortKey) {
      rows.sort((a,b) => {
        const va = a[sortKey], vb = b[sortKey];
        return (va > vb ? 1 : va < vb ? -1 : 0) * sortDir;
      });
    }

    // Pagination
    const totalPages = Math.ceil(rows.length / data.meta.pageRows);
    if (currentPage > totalPages) currentPage = totalPages || 1;
    const start = (currentPage-1)*data.meta.pageRows;
    const paged = rows.slice(start, start + data.meta.pageRows);

    // Build HTML
    const tbody = wrapper.querySelector('tbody');
    tbody.innerHTML = paged.map(r => {
      const cells = data.meta.columns.map(k =>
        `<td class="px-4 py-2">${String(r[k])}</td>`
      ).join('');
      return `<tr ${data.meta.rowSelectKey ? `data-${data.meta.rowSelectParam}="${r[data.meta.rowSelectKey]}"` : ''}>${cells}</tr>`;
    }).join('');

    // (Reattach row‐click listener for selection outside this function if needed)
  }

  // Column header click → sort
  wrapper.querySelectorAll('th[data-key]').forEach(th => {
    th.addEventListener('click', () => {
      const k = th.dataset.key;
      if (sortKey === k) sortDir = -sortDir; else { sortKey = k; sortDir = 1; }
      render();
    });
  });

  // Settings panel toggle
  document.getElementById('<?= $settingsBtnId ?>').addEventListener('click', () => {
    document.getElementById('<?= $settingsPanel ?>').classList.toggle('hidden');
  });

  // Rows-per-page change
  document.getElementById('<?= $rowsInputId ?>').addEventListener('input', e => {
    data.meta.pageRows = parseInt(e.target.value) || data.meta.pageRows;
    render();
  });

  // Checkbox column toggle
  document.querySelectorAll('#<?= $settingsPanel ?> input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', e => {
      const col = e.target.dataset.col;
      const idx = data.meta.columns.indexOf(col);
      if (idx === -1) return;
      if (e.target.checked === false) {
        data.meta.columns.splice(idx,1);
      } else {
        data.meta.columns.splice(idx,0,col);
      }
      render();
    });
  });

  // Initial render
  render();
})();
</script>

<?php } // end renderDataTable() ?>
