<?php declare(strict_types=1);
// /components/preferences-modal.php

// 0) Don’t render for API or non-index page
if (
    strpos($_SERVER['REQUEST_URI'], '/api/') === 0 ||
    basename($_SERVER['SCRIPT_NAME']) !== 'index.php'
) {
    return;
}

// 1) Gather exactly the cards your dashboard view passed in
$cardFiles    = $cardFiles    ?? [];
$visibleCards = $visibleCards ?? [];

// 2) Flatten & humanize names
$list = [];
foreach ($cardFiles as $file) {
    $key = preg_replace(['/^card_/', '/\.php$/'], '', $file);
    if (str_starts_with($key, 'get_')) {
        $key = substr($key, 4);
    }
    $list[] = [
        'file' => $file,
        'name' => ucfirst(str_replace('_', ' ', $key))
    ];
}

// 3) Split into 3 columns (avoid zero-length chunk)
$total   = count($list);
$perCol  = $total > 0
    ? (int) ceil($total / 3)
    : 1;
$columns = array_chunk($list, $perCol);
?>
<div id="preferences-modal" class="modal hidden">
  <div class="modal-backdrop" onclick="togglePreferencesModal(false)"></div>
  <div class="modal-dialog glass-card">

    <!-- HEADER -->
    <header class="modal-header">
      <h3>Select Cards to Display</h3>
    </header>

    <!-- GRID: three columns -->
    <div class="modal-grid-multi">
      <?php foreach ($columns as $col): ?>
        <ul class="modal-column">
          <?php foreach ($col as $item): ?>
            <li>
              <label>
                <input
                  type="checkbox"
                  name="cards[]"
                  value="<?= htmlspecialchars($item['file']) ?>"
                  <?= in_array($item['file'], $visibleCards) ? 'checked' : '' ?>
                >
                <?= htmlspecialchars($item['name']) ?>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
    </div>

    <!-- FOOTER ACTIONS -->
    <footer class="modal-actions">
      <button id="select-all" class="btn small">Select All</button>
      <button id="clear-all"  class="btn small">Clear All</button>
      <button id="save-modal" class="btn">Save</button>
      <button id="cancel-modal" class="btn">Cancel</button>
    </footer>
  </div>
</div>

<script>
function togglePreferencesModal(show) {
  document.getElementById('preferences-modal')
          .classList.toggle('hidden', !show);
}
document.addEventListener('DOMContentLoaded', () => {
  document.querySelector('.gear-icon')
          .addEventListener('click', () => togglePreferencesModal(true));
  document.getElementById('cancel-modal')
          .addEventListener('click', () => togglePreferencesModal(false));

  const cbs = () => Array.from(
    document.querySelectorAll('#preferences-modal input[name="cards[]"]')
  );
  document.getElementById('select-all')
          .addEventListener('click', () => cbs().forEach(cb => cb.checked = true));
  document.getElementById('clear-all')
          .addEventListener('click', () => cbs().forEach(cb => cb.checked = false));

  document.getElementById('save-modal')
          .addEventListener('click', () => {
    const sel = cbs().filter(cb => cb.checked).map(cb => cb.value);
    document.cookie = 'visible_cards=' + sel.join(',') + '; path=/; max-age=31536000';
    togglePreferencesModal(false);
    location.reload();
  });
});
</script>

<style>
.modal {
  position: fixed; inset: 0;
  display: flex; align-items: center; justify-content: center;
  z-index: 10000;
}
.modal.hidden { display: none; }
.modal-backdrop {
  position: absolute; inset: 0;
  background: rgba(0,0,0,0.6);
}
.modal-dialog {
  background: rgba(30,30,30,0.8);
  backdrop-filter: blur(12px);
  border-radius: 12px;
  padding: 1rem;
  width: 80%; max-width: 800px;
  max-height: 80vh; overflow-y: auto;
  box-shadow: 0 8px 32px rgba(0,0,0,0.5);
  color: var(--text-light);
  display: flex; flex-direction: column;
}
.modal-header {
  margin-bottom: 0.75rem;
  border-bottom: 1px solid rgba(255,255,255,0.2);
}
.modal-header h3 {
  margin: 0;
  color: var(--text-light);
  font-size: 1.25rem;
}
.modal-grid-multi {
  display: flex; gap: 1rem;
  margin-bottom: 1rem;
}
.modal-column {
  list-style: none; padding: 0; margin: 0; flex: 1;
}
.modal-column li {
  margin: 0.25rem 0;
}
.modal-column label {
  cursor: pointer;
  color: var(--text-light);
}
.modal-actions {
  display: flex; gap: 0.5rem;
  justify-content: flex-end;
}
.btn.small {
  font-size: 0.85rem; padding: 0.25rem 0.5rem;
}
.btn {
  padding: 0.5rem 1rem;
  background: var(--accent);
  color: var(--bg-light);
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.btn:hover {
  opacity: 0.9;
}
</style>
