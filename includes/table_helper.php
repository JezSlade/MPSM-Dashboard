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

    // 1. Columns
    $first     = (array)$data[0];
    $columns   = $options['columns'] 
               ?? array_combine(array_keys($first), array_keys($first));
    $colKeys   = array_keys($columns);

    // 2. Row selection config
    $selectKey   = $options['rowSelectKey']   ?? null;
    $selectParam = $options['rowSelectParam'] 
                 ?? ($selectKey ? strtolower($selectKey) : null);

    // 3. Defaults & IDs
    $defaultVisibles   = $options['defaultVisibleColumns'] ?? $colKeys;
    $defaultSort       = $options['defaultSort']          ?? $colKeys[0];
    $rowsPerPage       = (int)($options['rowsPerPage']    ?? 10);
    $searchable        = $options['searchable']           ?? true;
    $tableId           = uniqid('dt_');
    $settingsKey       = "{$tableId}_settings";

    // 4. JSON data
    $jsData = array_map(fn($r)=> array_map(fn($c)=>is_array($c)?json_encode($c):$c,(array)$r), $data);
    $jsonData = json_encode($jsData, JSON_HEX_TAG|JSON_HEX_APOS);
    ?>

<div id="<?= $tableId ?>_wrapper" class="data-table-container mb-4">
  <?php if ($searchable): ?>
    <input id="<?= $tableId ?>_search" type="text" placeholder="Search…"
           class="mb-2 w-full text-sm bg-gray-800 text-white border border-gray-700 rounded-md py-1 px-2"/>
  <?php endif; ?>

  <table id="<?= $tableId ?>" class="data-table w-full">
    <thead class="bg-gray-700">
      <tr>
        <?php foreach ($columns as $key => $label): ?>
          <th data-key="<?= htmlspecialchars($key) ?>"
              class="cursor-pointer px-4 py-2 text-left text-sm font-medium text-white">
            <?= htmlspecialchars($label) ?><span class="dt-sort-indicator">&nbsp;</span>
          </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody class="bg-gray-800"></tbody>
  </table>

  <div id="<?= $tableId ?>_pager" class="mt-2 flex justify-center gap-1 text-sm"></div>
</div>

<script>
(function(){
  const wrapper    = document.getElementById('<?= $tableId ?>_wrapper');
  const data       = <?= $jsonData ?>;
  const columns    = <?= json_encode($colKeys) ?>;
  const selectKey  = <?= json_encode($selectKey) ?>;
  const selectParam= <?= json_encode($selectParam) ?>;
  let filtered     = [...data];
  let currentPage  = 1;
  let sortKey      = <?= json_encode($defaultSort) ?>;
  let sortDir      = 1;
  const rpp        = <?= $rowsPerPage ?>;

  const searchBox  = document.getElementById('<?= $tableId ?>_search');
  const tblBody    = wrapper.querySelector('tbody');
  const ths        = wrapper.querySelectorAll('th[data-key]');
  const pager      = document.getElementById('<?= $tableId ?>_pager');

  function renderTable(){
    // sort
    filtered.sort((a,b)=>{
      const v1=(a[sortKey]||'').toString().toLowerCase();
      const v2=(b[sortKey]||'').toString().toLowerCase();
      return v1>v2?sortDir:v1<v2?-sortDir:0;
    });
    // paginate
    const start = (currentPage-1)*rpp;
    const page  = filtered.slice(start,start+rpp);

    // build rows
    tblBody.innerHTML = page.map(row=>{
      const selectVal = selectKey? row[selectKey]:null;
      const trClass = 'hover:bg-gray-700 cursor-'+(selectKey?'pointer':'default');
      const attrs   = selectKey && selectVal != null
        ? ` data-select-value="${encodeURIComponent(selectVal)}"`
        : '';
      const cells = columns.map(key=>
        `<td class="px-4 py-1 text-gray-100"${attrs}>${row[key]??''}</td>`
      ).join('');
      return `<tr class="${trClass}"${attrs}>${cells}</tr>`;
    }).join('') || `<tr><td colspan="${columns.length}" class="px-4 py-2 text-center text-gray-400">No data</td></tr>`;

    attachRowClicks();
    renderPager();
    updateSortIndicators();
  }

  function attachRowClicks(){
    if (!selectKey || !selectParam) return;
    wrapper.querySelectorAll('tr[data-select-value]').forEach(tr=>{
      tr.onclick = ()=>{
        const val = decodeURIComponent(tr.getAttribute('data-select-value'));
        const url = new URL(window.location.href);
        url.searchParams.set(selectParam, val);
        window.location.href = url;
      };
    });
  }

  function renderPager(){
    const total = Math.max(1, Math.ceil(filtered.length/rpp));
    pager.innerHTML = Array.from({length: total}, (_,i)=>
      `<button data-page="${i+1}"
               class="px-2 py-1 rounded ${
                 i+1===currentPage?'bg-cyan-500 text-black':'bg-gray-700 hover:bg-gray-600 text-white'
               }">${i+1}</button>`
    ).join('');
    pager.querySelectorAll('button').forEach(btn=>
      btn.addEventListener('click', ()=>{ currentPage=+btn.dataset.page; renderTable(); })
    );
  }

  function updateSortIndicators(){
    ths.forEach(th=>{
      const ind = th.querySelector('.dt-sort-indicator');
      ind.textContent = th.dataset.key===sortKey
        ? (sortDir===1?' ▲':' ▼')
        : '';
    });
  }

  // sorting
  ths.forEach(th=> th.addEventListener('click', ()=>{
    const k = th.dataset.key;
    if (sortKey===k) sortDir=-sortDir; else { sortKey=k; sortDir=1; }
    renderTable();
  }));

  // search
  if (searchBox){
    searchBox.oninput = ()=> {
      const q = searchBox.value.toLowerCase();
      filtered = data.filter(r=> JSON.stringify(r).toLowerCase().includes(q) );
      currentPage = 1;
      renderTable();
    };
  }

  // init
  renderTable();
})();
</script>
<?php } // end renderDataTable() ?>
