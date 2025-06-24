<?php declare(strict_types=1);
// includes/table_helper.php
// -------------------------------------------------------------------
// Renders a searchable, sortable, pageable data table with Tailwind.
// Uses renderSearchableDropdown() when $searchable is true.
// Supports associative columns=>labels, defaultVisibleColumns, and defaultSort.
// -------------------------------------------------------------------

// 0) Load the searchable dropdown helper so renderSearchableDropdown() is defined
require_once __DIR__ . '/searchable_dropdown.php';

function renderDataTable(array $data, array $options = []): void
{
    // If no data, show a placeholder
    if (empty($data)) {
        echo '<p class="text-gray-400">No data to display.</p>';
        return;
    }

    // 1) Columns & labels
    $first   = (array) $data[0];
    $rawCols = $options['columns'] ?? array_keys($first);

    // Detect associative [key=>label,...] vs. indexed list
    if (array_keys($rawCols) !== array_keys(array_values($rawCols))) {
        // associative: keys are column names, values are labels
        $columns = array_keys($rawCols);
        $labels  = $rawCols;
    } else {
        // indexed: column names only
        $columns = $rawCols;
        $labels  = $options['labels'] ?? array_combine($columns, $columns);
    }

    // Optionally restrict to a default visible subset
    if (!empty($options['defaultVisibleColumns'])) {
        $columns = array_values(
            array_intersect($options['defaultVisibleColumns'], $columns)
        );
    }

    $sortable       = $options['sortable']     ?? true;
    $searchable     = $options['searchable']   ?? true;
    $pageRows       = $options['rowsPerPage']  ?? 15;
    $rowSelectKey   = $options['rowSelectKey'] ?? null;
    $rowSelectParam = $options['rowSelectParam'] ?? $rowSelectKey;
    $defaultSort    = $options['defaultSort'] ?? null;

    // 2) Unique IDs for DOM elements
    $tableId       = 'tbl_' . preg_replace('/[^a-z0-9_]/i', '', uniqid());
    $wrapperId     = $tableId . '_wrapper';
    $settingsBtnId = $tableId . '_settings_btn';
    $settingsPanel = $tableId . '_settings_panel';
    $rowsInputId   = $tableId . '_rows_input';

    // 3) Prepare JSON payload for client-side rendering
    $jsRows = array_map(
        fn($row) => (object) array_intersect_key((array) $row, array_flip($columns)),
        $data
    );
    $jsMeta = (object) [
        'columns'        => $columns,
        'labels'         => $labels,
        'sortable'       => (bool) $sortable,
        'searchable'     => (bool) $searchable,
        'pageRows'       => (int) $pageRows,
        'rowSelectKey'   => $rowSelectKey,
        'rowSelectParam' => $rowSelectParam,
        'defaultSort'    => $defaultSort,
    ];
    $jsonData = json_encode((object)[
        'rows' => $jsRows,
        'meta' => $jsMeta
    ], JSON_HEX_TAG | JSON_HEX_APOS);

    // 4) Render HTML
    ?>
    <div id="<?= $wrapperId ?>" class="mb-6 bg-gray-800/50 p-4 rounded-lg border border-gray-600 backdrop-blur-md">
      <div class="flex justify-between items-center mb-2">
        <?php if ($searchable): ?>
          <?php
            // Replace plain text search with searchable dropdown
            renderSearchableDropdown(
              $tableId . '_search',      // input ID
              $tableId . '_datalist',    // datalist ID
              '/api/get_customers.php',  // endpoint URL
              'customer',                // cookie name
              'Search…',                 // placeholder text
              'w-1/2 text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500' // CSS classes
            );
          ?>
        <?php endif; ?>

        <!-- Settings button & panel -->
        <div class="relative inline-block">
          <button id="<?= $settingsBtnId ?>"
                  class="p-1 ml-2 rounded-md bg-gray-700 hover:bg-gray-600 transition"
                  aria-label="Table settings">
            <i data-feather="settings" class="text-yellow-400 h-4 w-4"></i>
          </button>
          <div id="<?= $settingsPanel ?>"
               class="hidden absolute right-0 mt-1 w-56 bg-gray-800 border border-gray-600 rounded-md shadow-lg p-3 z-20">
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
                  <input type="checkbox" data-col="<?= $col ?>" checked
                         class="mr-2 form-checkbox h-4 w-4 text-cyan-500">
                  <?= htmlspecialchars($labels[$col] ?? $col) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <div id="<?= $tableId ?>" class="overflow-auto">
        <table class="min-w-full divide-y divide-gray-700 text-sm">
          <thead>
            <tr>
              <?php foreach ($columns as $col): ?>
                <th data-key="<?= $col ?>"
                    class="px-4 py-2 text-left <?= $sortable ? 'cursor-pointer hover:text-cyan-400' : '' ?>">
                  <?= htmlspecialchars($labels[$col] ?? $col) ?>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="bg-gray-800 divide-y divide-gray-600">
            <!-- Rows will be populated by JavaScript -->
          </tbody>
        </table>
      </div>
    </div>

    <script>
    (function(){
      const wrapper = document.getElementById('<?= $wrapperId ?>');
      const data    = <?= $jsonData ?>;
      // Use defaultSort if provided, otherwise first column
      let sortKey = data.meta.sortable
        ? (data.meta.defaultSort || data.meta.columns[0])
        : null;
      let sortDir     = 1;
      let currentPage = 1;

      // Cookie utilities
      const Cookie = {
        get(name) {
          return document.cookie.split('; ').reduce((res, cookie) => {
            const [k, v] = cookie.split('=');
            return k === name ? decodeURIComponent(v) : res;
          }, null);
        },
        set(name, value) {
          document.cookie = `${name}=${encodeURIComponent(value)};path=/`;
        }
      };

      function render() {
        let rows = [...data.rows];

        // Apply search filter
        const searchInput = document.getElementById('<?= $tableId ?>_search');
        if (data.meta.searchable && searchInput?.value) {
          const term = searchInput.value.toLowerCase();
          rows = rows.filter(r =>
            data.meta.columns.some(col =>
              String(r[col]).toLowerCase().includes(term)
            )
          );
          Cookie.set(data.meta.rowSelectParam, searchInput.value);
        }

        // Sort
        if (data.meta.sortable && sortKey) {
          rows.sort((a, b) => {
            const va = a[sortKey], vb = b[sortKey];
            return ((va > vb) ? 1 : (va < vb) ? -1 : 0) * sortDir;
          });
        }

        // Paginate
        const totalPages = Math.ceil(rows.length / data.meta.pageRows);
        if (currentPage > totalPages) currentPage = totalPages || 1;
        const start = (currentPage - 1) * data.meta.pageRows;
        const page  = rows.slice(start, start + data.meta.pageRows);

        // Render rows
        const tbody = wrapper.querySelector('tbody');
        tbody.innerHTML = page.map(r => {
          const cells = data.meta.columns.map(col =>
            `<td class="px-4 py-2">${String(r[col])}</td>`
          ).join('');
          const attr = data.meta.rowSelectKey
            ? ` data-${data.meta.rowSelectParam}="${r[data.meta.rowSelectKey]}"`
            : '';
          return `<tr${attr}>${cells}</tr>`;
        }).join('');
      }

      // Column header clicks → sort
      wrapper.querySelectorAll('th[data-key]').forEach(th => {
        th.addEventListener('click', () => {
          const col = th.dataset.key;
          if (sortKey === col) sortDir = -sortDir;
          else { sortKey = col; sortDir = 1; }
          render();
        });
      });

      // Settings panel toggle
      document.getElementById('<?= $settingsBtnId ?>')
        .addEventListener('click', () =>
          document.getElementById('<?= $settingsPanel ?>').classList.toggle('hidden')
        );

      // Rows-per-page change
      document.getElementById('<?= $rowsInputId ?>')
        .addEventListener('input', e => {
          data.meta.pageRows = parseInt(e.target.value) || data.meta.pageRows;
          render();
        });

      // Column visibility toggle
      document.querySelectorAll('#<?= $settingsPanel ?> input[type="checkbox"]')
        .forEach(cb => cb.addEventListener('change', e => {
          const col = e.target.dataset.col;
          const idx = data.meta.columns.indexOf(col);
          if (idx === -1) return;
          if (!e.target.checked) data.meta.columns.splice(idx, 1);
          else data.meta.columns.splice(idx, 0, col);
          render();
        }));

      // Initial render
      render();

    })();
    </script>
    <?php
} // end function renderDataTable
