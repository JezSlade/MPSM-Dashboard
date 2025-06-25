<?php
require_once __DIR__ . '/../includes/api.php';
$customers = api_get_customers();
?>
<div id="CustomersCard" class="card" data-mode="min">
  <header>
    <h2>Customers (<?= count($customers) ?>)</h2>
    <button data-action="expand" aria-label="Expand">▼</button>
  </header>

  <div class="expanded" hidden>
    <table>
      <thead>
        <tr><th>Code</th><th>Description</th></tr>
      </thead>
      <tbody>
        <?php foreach ($customers as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['Code']        ?? '') ?></td>
          <td><?= htmlspecialchars($c['Description'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <button data-action="drill" aria-label="Drilldown">▶</button>
  </div>

  <div class="drilldown" hidden>
    <p>No further details configured yet.</p>
  </div>
</div>

<script>
// Toggle between min ↔ expanded ↔ drill modes
document.querySelector('#CustomersCard').addEventListener('click', e => {
  const card = e.currentTarget;

  if (e.target.dataset.action === 'expand') {
    const exp = card.querySelector('.expanded');
    exp.hidden = !exp.hidden;
    card.dataset.mode = exp.hidden ? 'min' : 'exp';
  }

  if (e.target.dataset.action === 'drill') {
    const drill = card.querySelector('.drilldown');
    drill.hidden = !drill.hidden;
    card.dataset.mode = drill.hidden ? 'exp' : 'drill';
  }
});
</script>
