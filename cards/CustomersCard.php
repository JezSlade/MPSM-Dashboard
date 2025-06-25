<?php
require_once __DIR__ . '/../includes/api.php';

// Pull the live list
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
        <tr><th>Code</th><th>Name</th></tr>
      </thead>
      <tbody>
        <?php foreach ($customers as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['CustomerCode'] ?? $c['Code']) ?></td>
          <td><?= htmlspecialchars($c['Name']     ?? $c['Description']) ?></td>
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
// Three‐state toggle: snapshot ↔ expanded ↔ drilldown
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
