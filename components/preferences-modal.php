<?php declare(strict_types=1); ?>
<!-- /components/preferences-modal.php -->
<div id="card-settings-modal" class="modal hidden">
  <div class="modal-overlay" onclick="togglePreferencesModal(false)"></div>
  <div class="modal-content">
    <h3>Select Cards to Display</h3>
    <?php
      // Build groups from $cardFiles and $visibleCards (provided by dashboard.php)
      $groups = [];
      foreach ($cardFiles as $file) {
          // grouping by prefix after "card_"
          if (preg_match('/^card_([^_]+)_/', $file, $m)) {
              $group = ucfirst($m[1]);
          } else {
              $group = 'Other';
          }
          // human‐friendly name
          $nameKey = preg_replace(['/^card_/', '/\.php$/'], '', $file);
          $display = ucfirst(str_replace('_', ' ', $nameKey));
          $groups[$group][] = ['file' => $file, 'name' => $display];
      }
    ?>
    <?php foreach ($groups as $group => $items): ?>
      <h4><?= htmlspecialchars($group) ?></h4>
      <table class="preferences-table">
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td style="width:1.5em;">
              <input
                type="checkbox"
                name="cards[]"
                value="<?= htmlspecialchars($item['file']) ?>"
                <?= in_array($item['file'], $visibleCards) ? 'checked' : '' ?>
              >
            </td>
            <td><?= htmlspecialchars($item['name']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endforeach; ?>
    <div class="modal-actions">
      <button class="btn-save">Save</button>
      <button class="btn-cancel" onclick="togglePreferencesModal(false)">Cancel</button>
    </div>
  </div>
</div>

<script>
function togglePreferencesModal(show) {
  const modal = document.getElementById('card-settings-modal');
  modal.classList.toggle('hidden', !show);
}

document.addEventListener('DOMContentLoaded', () => {
  // Gear icon toggles the modal
  document.querySelector('.gear-icon').addEventListener('click', () => {
    togglePreferencesModal(true);
  });
  // Save preferences
  document.querySelector('.btn-save').addEventListener('click', () => {
    const selected = Array.from(
      document.querySelectorAll('input[name="cards[]"]:checked')
    ).map(cb => cb.value);
    document.cookie = 'visible_cards=' + selected.join(',') + '; path=/; max-age=31536000';
    location.reload();
  });
  // Cancel
  document.querySelector('.btn-cancel').addEventListener('click', () => {
    togglePreferencesModal(false);
  });
});
</script>

<style>
.modal {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
}
.modal.hidden { display: none; }
.modal-overlay {
  position: absolute; inset: 0;
  background: rgba(0,0,0,0.5);
}
.modal-content {
  position: relative;
  background: var(--bg-light);
  color: var(--text-light);
  padding: 1rem;
  border-radius: 8px;
  max-width: 90%;
  max-height: 80%;
  overflow-y: auto;
  box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}
.preferences-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
}
.preferences-table td {
  padding: 0.25rem 0.5rem;
}
.modal-actions {
  text-align: right;
  margin-top: 0.5rem;
}
.btn-save, .btn-cancel {
  margin-left: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.btn-save { background: var(--text-light); color: var(--bg-light); }
.btn-cancel { background: transparent; color: var(--text-light); }
