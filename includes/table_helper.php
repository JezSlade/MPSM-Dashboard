<?php declare(strict_types=1);
// /includes/table_helper.php

/**
 * Render a searchable, sortable, pageable data table.
 *
 * @param array $data    Array of associative arrays (rows). Nested arrays get JSON-encoded.
 * @param array $options [
 *   'columns'     => [ 'key' => 'Header Label', ... ], // defaults to all keys
 *   'defaultSort' => 'key',                             // defaults to first column
 *   'rowsPerPage' => int,                               // default 10
 *   'searchable'  => bool,                              // default true
 * ]
 */
function renderDataTable(array $data, array $options = []): void {
    if (empty($data)) {
        echo '<p class="text-gray-400">No data to display.</p>';
        return;
    }

    // Determine columns
    $first = (array)$data[0];
    $columns = $options['columns']
        ?? array_combine(array_keys($first), array_keys($first));
    $colKeys     = array_keys($columns);
    $defaultSort = $options['defaultSort'] ?? $colKeys[0];
    $rowsPerPage = (int)($options['rowsPerPage'] ?? 10);
    $searchable  = $options['searchable']  ?? true;

    // Unique IDs
    $uid        = uniqid('dt_');
    $wrapperId  = $uid . '_wrapper';
    $searchId   = $uid . '_search';
    $colsId     = $uid . '_cols';
    $pagerId    = $uid . '_pager';

    // Prepare JSON-safe data
    $jsData = array_map(function($row) {
        return array_map(function($cell) {
            return is_array($cell) ? json_encode($cell) : $cell;
        }, (array)$row);
    }, $data);
    $json = json_encode($jsData, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
    ?>

<div id="<?= $wrapperId ?>" class="data-table-container mb-4">
  <?php if ($searchable): ?>
    <input
      type="text"
      id="<?= $searchId ?>"
      placeholder="Search…"
      class="mb-2 w-full text-sm bg-gray-800 text-white border border-gray-700 rounded-md py-1 px-2 focus:outline-none focus:ring-1 focus:ring-cyan-500"
    />
  <?php endif; ?>

  <div id="<?= $colsId ?>" class="mb-2 text-sm">
    <!-- Column visibility -->
    <?php foreach ($columns as $key => $label): ?>
      <label class="inline-flex items-center mr-4">
        <input
          type="checkbox"
          data-dt-col="<?= htmlspecialchars($key) ?>"
          checked
          class="mr-1 form-checkbox h-4 w-4 text-cyan-500"
        />
        <?= htmlspecialchars($label) ?>
      </label>
    <?php endforeach; ?>
  </div>

  <table class="data-table w-full">
    <thead>
      <tr>
        <?php foreach ($columns as $key => $label): ?>
          <th
            data-dt-key="<?= htmlspecialchars($key) ?>"
            class="cursor-pointer select-none px-2 py-1 text-left text-gray-200 bg-gray-800"
          >
            <?= htmlspecialchars($label) ?>
            <span class="dt-sort-indicator"></span>
          </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <div id="<?= $pagerId ?>" class="table-pagination mt-2 flex flex-wrap gap-1 text-sm"></div>
</div>

<script>
(function(){
  // Configuration
  const data      = <?= $json ?>;
  const columns   = <?= json_encode($colKeys) ?>;
  let filtered    = [...data];
  let currentPage = 1;
  let sortKey     = <?= json_encode($defaultSort) ?>;
  let sortDir     = 1;
  const rpp       = <?= $rowsPerPage ?>;

  // Elements
  const wrapper   = document.getElementById('<?= $wrapperId ?>');
  const tblBody   = wrapper.querySelector('tbody');
  const ths       = wrapper.querySelectorAll('th[data-dt-key]');
  const pager     = document.getElementById('<?= $pagerId ?>');
  const searchBox = document.getElementById('<?= $searchId ?>');
  const toggles   = wrapper.querySelectorAll('input[data-dt-col]');

  // Render table and pager
  function renderTable() {
    // Sort
    filtered.sort((a,b) => {
      const v1 = (a[sortKey]||'').toString().toLowerCase();
      const v2 = (b[sortKey]||'').toString().toLowerCase();
      return v1 > v2 ? sortDir : v1 < v2 ? -sortDir : 0;
    });
    // Paginate
    const start = (currentPage - 1) * rpp;
    const pageRows = filtered.slice(start, start + rpp);
    tblBody.innerHTML = pageRows.map(row => {
      const cells = columns.map(key =>
        `<td class="px-2 py-1">${row[key] ?? ''}</td>`
      );
      return `<tr>${cells.join('')}</tr>`;
    }).join('');
    renderPager();
  }

  function renderPager() {
    const total = Math.ceil(filtered.length / rpp) || 1;
    let html = '';
    for (let i = 1; i <= total; i++) {
      const cls = i === currentPage
        ? 'bg-cyan-500 text-black'
        : 'bg-gray-700 hover:bg-gray-600';
      html += `<button data-page="${i}" class="px-2 py-1 rounded ${cls}">${i}</button>`;
    }
    pager.innerHTML = html;
    pager.querySelectorAll('button').forEach(btn =>
      btn.addEventListener('click', () => {
        currentPage = Number(btn.dataset.page);
        renderTable();
      })
    );
  }

  // Sort event
  ths.forEach(th =>
    th.addEventListener('click', () => {
      const key = th.dataset.dtKey;
      if (sortKey === key) sortDir = -sortDir;
      else { sortKey = key; sortDir = 1; }
      updateSortIndicators();
      renderTable();
    })
  );

  function updateSortIndicators() {
    ths.forEach(th => {
      const indi = th.querySelector('.dt-sort-indicator');
      indi.textContent = (th.dataset.dtKey === sortKey)
        ? (sortDir === 1 ? ' ▲' : ' ▼')
        : '';
    });
  }

  // Search event
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

  // Column toggles
  toggles.forEach(cb =>
    cb.addEventListener('change', () => {
      const idx = columns.indexOf(cb.dataset.dtCol) + 1;
      const selector = `table th:nth-child(${idx}), table td:nth-child(${idx})`;
      wrapper.querySelectorAll(selector).forEach(c => {
        c.style.display = cb.checked ? '' : 'none';
      });
    })
  );

  // Initial draw
  updateSortIndicators();
  renderTable();
})();
</script>
