<?php declare(strict_types=1);
// /components/preferences-modal.php

// 0) Don’t render when serving API endpoints
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    return;
}

// 1) Ensure variables
$cardFiles    = $cardFiles    ?? [];
$visibleCards = $visibleCards ?? [];

// 2) Group cards by prefix
$groups = [];
foreach ($cardFiles as $file) {
    if (preg_match('/^card_([^_]+)_/', $file, $m)) {
        $group = ucfirst($m[1]);
    } else {
        $group = 'Other';
    }
    $nameKey = preg_replace(['/^card_/', '/\.php$/'], '', $file);
    if (strpos($nameKey, 'get_') === 0) {
        $nameKey = substr($nameKey, 4);
    }
    $display = ucfirst(str_replace('_', ' ', $nameKey));
    $groups[$group][] = ['file'=>$file,'name'=>$display];
}
?>
<div id="preferences-modal" class="modal hidden">
  <div class="modal-backdrop" onclick="togglePreferencesModal(false)"></div>
  <div class="modal-dialog glass-card">
    <h3>Select Cards to Display</h3>

    <!-- Select/Clear All -->
    <div class="modal-actions-top">
      <button id="select-all" class="btn small">Select All</button>
      <button id="clear-all"  class="btn small">Clear All</button>
    </div>

    <div class="modal-grid">
      <?php foreach ($groups as $group => $items): ?>
        <div class="group-block">
          <h4><?= htmlspecialchars($group) ?></h4>
          <table class="preferences-table">
            <tbody>
              <?php foreach ($items as $item): ?>
              <tr>
                <td><input type="checkbox" name="cards[]" value="<?= htmlspecialchars($item['file']) ?>" <?= in_array($item['file'], $visibleCards) ? 'checked' : '' ?>></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
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
  document.getElementById('preferences-modal').classList.toggle('hidden', !show);
}

document.addEventListener('DOMContentLoaded', () => {
  // open/close
  document.querySelector('.gear-icon').addEventListener('click', () => togglePreferencesModal(true));
  document.getElementById('cancel-modal').addEventListener('click', () => togglePreferencesModal(false));

  // select/clear all
  const checkboxes = () => Array.from(document.querySelectorAll('#preferences-modal input[name="cards[]"]'));
  document.getElementById('select-all').addEventListener('click', () => {
    checkboxes().forEach(cb => cb.checked = true);
  });
  document.getElementById('clear-all').addEventListener('click', () => {
    checkboxes().forEach(cb => cb.checked = false);
  });

  // save
  document.getElementById('save-modal').addEventListener('click', () => {
    const selected = checkboxes().filter(cb => cb.checked).map(cb => cb.value);
    document.cookie = 'visible_cards=' + selected.join(',') + '; path=/; max-age=31536000';
    location.reload();
  });
});
</script>

<style>
/* Modal backdrop */
.modal {
  position: fixed; inset: 0;
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
}
.modal.hidden { display: none; }
.modal-backdrop {
  position: absolute; inset: 0;
  background: rgba(0,0,0,0.5);
}

/* Glassmorphic dialog */
.modal-dialog {
  position: relative;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 1rem;
  width: 90%; max-width: 800px;
  max-height: 80%; overflow-y: auto;
  box-shadow: 0 8px 32px rgba(0,0,0,0.2);
  color: var(--text-light);
}

/* Top actions */
.modal-actions-top {
  display: flex; gap: 0.5rem; margin-bottom: 0.75rem;
}
.btn.small {
  font-size: 0.9rem; padding: 0.25rem 0.5rem;
}

/* Grid of groups */
.modal-grid {
  display: flex; flex-wrap: wrap; gap: 1rem;
}
.group-block {
  flex: 1 1 calc(33% - 1rem);
}
.group-block h4 {
  margin: 0.5rem 0 0.25rem;
  font-size: 1rem;
  color: var(--accent);
}
.preferences-table {
  width: 100%; border-spacing: 0.5rem;
}
.preferences-table td {
  padding: 0.25rem;
}

/* Bottom actions */
.modal-actions {
  text-align: right; margin-top: 1rem;
}
.modal-actions .btn {
  margin-left: 0.5rem;
  padding: 0.5rem 1rem;
  border: none; border-radius: 4px;
  background: var(--accent); color: var(--bg-light);
  cursor: pointer;
}
</style>
