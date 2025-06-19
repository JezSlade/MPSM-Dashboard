<?php declare(strict_types=1);
// /components/preferences-modal.php

// 🛑 Prevent modal HTML from loading during API calls
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    return;
}

?>
<div id="preferences-modal" class="modal hidden">
  <div class="modal-backdrop" onclick="togglePreferencesModal(false)"></div>
  <div class="modal-dialog">
    <h3>Select Cards to Display</h3>
    <?php
      // Ensure $cardFiles and $visibleCards exist
      $cardFiles    = $cardFiles    ?? [];
      $visibleCards = $visibleCards ?? [];

      // Group cards by prefix
      $groups = [];
      foreach ($cardFiles as $file) {
          if (preg_match('/^card_([^_]+)_/', $file, $m)) {
              $group = ucfirst($m[1]);
          } else {
              $group = 'Other';
          }
          $nameKey = preg_replace(['/^card_/', '/\.php$/'], '', $file);
          $display = ucfirst(str_replace('_', ' ', $nameKey));
          $groups[$group][] = ['file'=>$file,'name'=>$display];
      }
    ?>
    <div class="modal-content">
      <?php foreach ($groups as $group => $items): ?>
        <h4><?= htmlspecialchars($group) ?></h4>
        <?php foreach ($items as $item): ?>
          <label class="modal-item">
            <input
              type="checkbox"
              name="cards[]"
              value="<?= htmlspecialchars($item['file']) ?>"
              <?= in_array($item['file'], $visibleCards) ? 'checked' : '' ?>
            >
            <?= htmlspecialchars($item['name']) ?>
          </label>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
    <div class="modal-actions">
      <button id="save-modal" class="btn">Save</button>
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
  document.getElementById('save-modal')
          .addEventListener('click', () => {
    const selected = Array.from(
      document.querySelectorAll(
        '#preferences-modal input[name="cards[]"]:checked'
      )
    ).map(cb => cb.value);
    document.cookie = 'visible_cards=' + selected.join(',') +
                      '; path=/; max-age=31536000';
    location.reload();
  });
  document.getElementById('cancel-modal')
          .addEventListener('click', () => togglePreferencesModal(false));
});
</script>

<style>
.modal { /* existing styles */ }
</style>
