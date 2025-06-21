<?php declare(strict_types=1);
// /includes/table_helper.php

function renderDataTable(array $data, array $options = []): void {
    if (!$data) {
        echo '<p>No data to display.</p>';
        return;
    }
    $first   = (array)$data[0];
    $columns = $options['columns'] ?? array_combine(array_keys($first), array_keys($first));
    $keys    = array_keys($columns);
    $json    = json_encode($data);
    ?>
<div class="table-container">
  <input type="text" id="search" placeholder="Search…" />
  <table id="data-table"><thead><tr><?php foreach($columns as $k=>$h): ?><th><?php echo htmlspecialchars($h); ?></th><?php endforeach; ?></tr></thead><tbody></tbody></table>
</div>
<script>
(function(){
  const data = <?php echo $json;?>;
  const table = document.getElementById('data-table').getElementsByTagName('tbody')[0];
  data.forEach(row => {
    const tr = document.createElement('tr');
    <?php foreach($keys as $key): ?>
    let td = document.createElement('td');
    td.textContent = row['<?php echo $key;?>'];
    tr.appendChild(td);
    <?php endforeach;?>
    table.appendChild(tr);
  });
})();
</script>
<?php }