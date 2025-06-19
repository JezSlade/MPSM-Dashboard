<?php declare(strict_types=1);
// /components/preferences-modal.php

// 0) Don’t render for API or sub‐requests
if (
    strpos($_SERVER['REQUEST_URI'], '/api/') === 0
    || basename($_SERVER['SCRIPT_NAME']) !== 'index.php'
) {
    return;
}

// 1) Prepare lists
$cardFiles    = $cardFiles    ?? [];
$visibleCards = $visibleCards ?? [];

// 2) Flatten & humanize names
$list = [];
foreach ($cardFiles as $file) {
    $nameKey = preg_replace(['/^card_/', '/\.php$/'], '', $file);
    if (strpos($nameKey, 'get_') === 0) {
        $nameKey = substr($nameKey, 4);
    }
    $display = ucfirst(str_replace('_', ' ', $nameKey));
    $list[]  = ['file'=>$file, 'name'=>$display];
}

// 3) Split into three columns
$total   = count($list);
$perCol  = (int) ceil($total / 3);
$columns = array_chunk($list, $perCol);
?>
<div id="preferences-modal" class="modal hidden">
  <div class="modal-backdrop" onclick="togglePreferencesModal(false)"></div>
  <div class="modal-dialog glass-card">
    <h3>Select Cards to Display</h3>

    <div class="modal-actions-top">
      <button id="select-all" class="btn small">Select All</button>
      <button id="clear-all"  class="btn small">Clear All</button>
    </div>

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

    <div class="modal-actions">
      <button id="save-modal"   class="btn">Save</button>
      <button id="cancel-modal" class="btn">Cancel</button>
    </div>
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
    if (sel.length) {
      document.cookie = 'visible_cards=' + sel.join(',') + '; path=/; max-age=31536000';
    }
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
  background: rgba(0,0,0,0.5);
}
.modal-dialog {
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 1rem;
  width: 90%; max-width: 800px;
  max-height: 80%; overflow-y: auto;
  box-shadow: 0 8px 32px rgba(0,0,0,0.2);
  color: var(--text-light);
}
.modal-actions-top {
  display: flex; gap: 0.5rem; margin-bottom: 1rem;
}
.btn.small {
  font-size: 0.9rem; padding: 0.25rem 0.5rem;
}
.modal-grid-multi {
  display: flex; gap: 1rem; margin-bottom: 1rem;
}
.modal-column {
  list-style: none; padding: 0; margin: 0; flex: 1;
}
.modal-column li {
  margin: 0.25rem 0;
}
.modal-actions {
  text-align: right;
}
.modal-actions .btn {
  margin-left: 0.5rem;
  padding: 0.5rem 1rem;
  border: none; border-radius: 4px;
  background: var(--accent); color: var(--bg-light);
  cursor: pointer;
}
</style>
