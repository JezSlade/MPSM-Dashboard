<?php declare(strict_types=1);
// includes/table_helper.php
// -------------------------------------------------------------------
// Renders a searchable, sortable, pageable data table with Tailwind.
// Optional row selection: clicking a row sets ?<param>=<value> and reloads.
// -------------------------------------------------------------------

function renderDataTable(array $data, array $options = []): void {
    if (empty($data)) {
        echo '<p class="text-gray-400">No data to display.</p>';
        return;
    }

    // 1. Columns & labels
    $first    = (array)$data[0];
    $columns  = $options['columns']
             ?? array_combine(array_keys($first), array_keys($first));
    $colKeys  = array_keys($columns);

    // 2. Row selection config
    $selectKey   = $options['rowSelectKey']   ?? null;
    $selectParam = $options['rowSelectParam']
                 ?? ($selectKey ? strtolower($selectKey) : null);

    // 3. Defaults & IDs
    $defaultVisibles = $options['defaultVisibleColumns'] ?? $colKeys;
    $defaultSort     = $options['defaultSort']           ?? $colKeys[0];
    $rowsPerPage     = (int)($options['rowsPerPage']     ?? 10);
    $searchable      = $options['searchable']            ?? true;
    $tableId         = uniqid('dt_');

    // 4. JSON encode
    $jsData = array_map(fn($r) => array_map(
        fn($c) => is_array($c) ? json_encode($c) : $c,
        (array)$r
    ), $data);
    $jsonData = json_encode($jsData, JSON_HEX_TAG|JSON_HEX_APOS);

    ?>
<div id="<?= $tableId ?>_wrapper" class="mb-6 bg-gray-800/50 p-4 rounded-lg border border-gray-600 backdrop-blur-md">
  <?php if ($searchable): ?>
    <input id="<?= $tableId ?>_search" type="text" placeholder="Search…"
           class="mb-3 w-full text-sm bg-gray-700 text-white border border-gray-600 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-cyan-500"/>
  <?php endif; ?>

  <div class="overflow-x-auto">
    <table id="<?= $tableId ?>" class="min-w-full divide-y divide-gray-600">
      <thead class="bg-gray-700">
        <tr>
          <?php foreach ($columns as $key => $label): ?>
            <?php $hide = in_array($key, $defaultVisibles, true) ? '' : 'hidden'; ?>
            <th data-key="<?= htmlspecialchars($key) ?>" class="<?= $hide ?> px-4 py-2 text-left text-sm font-semibold text-white cursor-pointer">
              <?= htmlspecialchars($label) ?><span class="ml-1 text-xs dt-sort-indicator"></span>
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
  let page          = 1;
  let sortKey       = <?= json_encode($defaultSort) ?>;
  let sortDir       = 1;
  let rpp           = <?= $rowsPerPage ?>;

  const searchBox   = document.getElementById('<?= $tableId ?>_search');
  const tblBody     = wrapper.querySelector('tbody');
  const ths         = wrapper.querySelectorAll('th[data-key]');
  const pager       = document.getElementById('<?= $tableId ?>_pager');

  function render(){
    // sort
    filtered.sort((a,b)=>{
      let v1=(a[sortKey]||'').toString().toLowerCase(),
          v2=(b[sortKey]||'').toString().toLowerCase();
      return v1>v2?sortDir:v1<v2?-sortDir:0;
    });
    // paginate
    const start=(page-1)*rpp,
          slice=filtered.slice(start, start+rpp);

    // build rows
    tblBody.innerHTML = slice.map((row,i)=>{
      const odd = i%2? 'bg-gray-700':'bg-gray-800';
      const hov = selectKey? ' hover:bg-gray-600 cursor-pointer':''; 
      const attr= selectKey && row[selectKey] != null
        ? ` data-select-value="${encodeURIComponent(row[selectKey])}"`
        : '';
      const tds = columns.map(k=>{
        const hid = <?= json_encode($colKeys) ?>.indexOf(k)>=0 && !<?= json_encode($defaultVisibles) ?>.includes(k)
          ? 'hidden':'';
        return `<td class="${hid} px-4 py-2 text-gray-100"${attr}>${row[k]||''}</td>`;
      }).join('');
      return `<tr class="${odd+hov}"${attr}>${tds}</tr>`;
    }).join('') || `<tr><td colspan="${columns.length}" class="px-4 py-2 text-center text-gray-400">No data</td></tr>`;

    attachClicks();
    renderPager();
    updateSort();
  }

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

  function renderPager(){
    const total = Math.ceil(filtered.length/rpp)||1;
    pager.innerHTML = Array.from({length:total}, (_,i)=>
      `<button data-page="${i+1}" class="px-2 py-1 rounded ${
        i+1===page ? 'bg-cyan-500 text-black':'bg-gray-700 hover:bg-gray-600 text-white'
      }">${i+1}</button>`
    ).join('');
    pager.querySelectorAll('button').forEach(b=>b.onclick=()=>{
      page = +b.dataset.page; render();
    });
  }

  function updateSort(){
    ths.forEach(th=>{
      const ind = th.querySelector('.dt-sort-indicator');
      if(th.dataset.key===sortKey) ind.textContent = sortDir===1?' ▲':' ▼';
      else ind.textContent = '';
    });
  }

  // sorting
  ths.forEach(th=> th.onclick=()=>{
    const k=th.dataset.key;
    if(sortKey===k) sortDir=-sortDir; else { sortKey=k; sortDir=1; }
    render();
  });

  // searching
  if(searchBox){
    searchBox.oninput = ()=>{
      const q=searchBox.value.toLowerCase();
      filtered = data.filter(r=> JSON.stringify(r).toLowerCase().includes(q) );
      page=1; render();
    };
  }

  // initial
  render();

})();
</script>
<?php } // end renderDataTable() ?>
