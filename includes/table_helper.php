<?php declare(strict_types=1);
// includes/table_helper.php
// -------------------------------------------------------------------
// Renders a searchable, sortable, pageable data table with Tailwind.
// Replaces the top search input with renderSearchableDropdown().
// Only “Description” column visible by default.
// Clicking a row sets the global “customer” cookie and reloads.
// -------------------------------------------------------------------

require_once __DIR__ . '/searchable_dropdown.php';

function renderDataTable(array $data, array $options = []): void
{
    if (empty($data)) {
        echo '<p class="text-gray-400">No data to display.</p>';
        return;
    }

    // 1) Columns & labels from first row
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
    $uid            = preg_replace('/[^a-z0-9_]/i','', uniqid());
    $wrapperId      = "tbl_{$uid}_wrapper";
    $settingsBtnId  = "tbl_{$uid}_settings_btn";
    $settingsPanel  = "tbl_{$uid}_settings_panel";
    $searchInputId  = "tbl_{$uid}_search";
    $datalistId     = "tbl_{$uid}_datalist";

    // 4) Prepare JSON
    $jsRows = array_map(
        fn($r) => (object) array_intersect_key((array)$r, array_flip($columns)),
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
    $jsonData = json_encode((object)[ 'rows'=>$jsRows,'meta'=>$jsMeta ], JSON_HEX_TAG|JSON_HEX_APOS);
    ?>

<div id="<?= $wrapperId ?>" class="mb-6 bg-gray-800/50 p-4 rounded-lg border border-gray-600 backdrop-blur-md">
  <div class="flex justify-between items-center mb-2">
    <?php if ($searchable): ?>
      <?php
        // ← REPLACE search input with searchable dropdown helper
        renderSearchableDropdown(
          $searchInputId,          // input ID
          $datalistId,             // datalist ID
          '/api/get_customers.php',// API endpoint for dropdown options
          'customer',              // cookie name to set
          'Filter…',               // placeholder
          'w-1/2 text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-1 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500' // CSS
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

  <div class="overflow-auto">
    <table class="min-w-full divide-y divide-gray-700 text-sm">
      <thead><tr><!-- populated by JS --></tr></thead>
      <tbody class="bg-gray-800 divide-y divide-gray-600"><!-- populated by JS --></tbody>
    </table>
  </div>
</div>

<script>
(function(){
  const wrapper = document.getElementById('<?= $wrapperId ?>');
  const data    = <?= $jsonData ?>;
  let sortKey   = data.meta.sortable ? data.meta.columns[0] : null;
  let sortDir   = 1;
  let currentPage = 1;
  const cookieName = data.meta.rowSelectParam;

  // Helpers
  const Cookie = { set(n,v){ document.cookie=`${n}=${encodeURIComponent(v)};path=/`; } };

  function renderHeader(visibleCols){
    const head = wrapper.querySelector('thead tr');
    head.innerHTML = visibleCols.map(col=>
      `<th data-key="${col}" class="px-4 py-2 text-left ${data.meta.sortable?'cursor-pointer hover:text-cyan-400':''}">
         ${data.meta.labels[col]}
       </th>`
    ).join('');
    head.querySelectorAll('th[data-key]').forEach(th=>
      th.addEventListener('click',()=>{
        const c=th.dataset.key;
        sortKey===c?sortDir=-sortDir:(sortKey=c,sortDir=1);
        render();
      })
    );
  }

  function render(){
    // Columns selected
    const visible = Array.from(
      document.querySelectorAll('#<?= $settingsPanel ?> input:checked')
    ).map(cb=>cb.dataset.col);

    renderHeader(visible);

    // Sort rows
    let rows=[...data.rows];
    if(data.meta.sortable&&sortKey) rows.sort((a,b)=>
      ((a[sortKey]>b[sortKey])?1:(a[sortKey]<b[sortKey])?-1:0)*sortDir
    );

    // Paginate
    const total=Math.ceil(rows.length/data.meta.pageRows)||1;
    if(currentPage>total) currentPage=total;
    const page=rows.slice((currentPage-1)*data.meta.pageRows,currentPage*data.meta.pageRows);

    // Body
    const body=wrapper.querySelector('tbody');
    body.innerHTML=page.map(r=>{
      const cells=visible.map(col=>`<td class="px-4 py-2">${r[col]||''}</td>`).join('');
      const code=r[data.meta.rowSelectKey]||'';
      return `<tr class="cursor-pointer" data-customer="${code}">${cells}</tr>`;
    }).join('');
  }

  // Toggle settings
  document.getElementById('<?= $settingsBtnId ?>')
    .addEventListener('click',()=>wrapper.querySelector('#<?= $settingsPanel ?>').classList.toggle('hidden'));

  // Column toggle
  wrapper.querySelectorAll('#<?= $settingsPanel ?> input[type="checkbox"]')
    .forEach(cb=>cb.addEventListener('change',render));

  // Row-click: set cookie + reload
  wrapper.querySelector('tbody')
    .addEventListener('click',e=>{
      const tr=e.target.closest('tr[data-customer]');
      if(!tr)return;
      Cookie.set(cookieName,tr.dataset.customer);
      window.location.reload();
    });

  // Initial draw
  render();
})();
</script>
<?php
} // end renderDataTable()
