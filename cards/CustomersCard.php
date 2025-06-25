<?php
require_once __DIR__ . '/../includes/api.php';
$customers = api_get_customers();
?>
<div id="CustomersCard" class="card" data-mode="min">
  <header>
    <h2>Customers (<?= count($customers) ?>)</h2>
    <button data-action="expand">▼</button>
  </header>
  <div class="expanded" hidden>
    <table>
      <thead>
        <tr><th>ID</th><th>Name</th></tr>
      </thead>
      <tbody>
        <?php foreach ($customers as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['Id']) ?></td>
          <td><?= htmlspecialchars($c['Name']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <button data-action="drill">▶</button>
  </div>
  <div class="drilldown" hidden>
    <p>Drilldown details go here.</p>
  </div>
</div>
<script>
document.querySelector('#CustomersCard').addEventListener('click', e => {
  const card = e.currentTarget;
  // Toggle expanded view
  if (e.target.dataset.action === 'expand') {
    const exp = card.querySelector('.expanded');
    exp.hidden = !exp.hidden;
    card.dataset.mode = exp.hidden ? 'min' : 'exp';
  }
  // Toggle drilldown view
  if (e.target.dataset.action === 'drill') {
    const drill = card.querySelector('.drilldown');
    drill.hidden = !drill.hidden;
    card.dataset.mode = drill.hidden ? 'exp' : 'drill';
  }
});
</script>
