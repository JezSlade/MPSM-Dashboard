<?php declare(strict_types=1); ?>
<!-- /components/preferences-modal.php -->
<?php
  // Ensure $cardFiles and $visibleCards are defined
  $cardFiles    = $cardFiles    ?? [];
  $visibleCards = $visibleCards ?? [];
  
  // Group cards by their prefix
  $groups = [];
  foreach ($cardFiles as $file) {
      if (preg_match('/^card_([^_]+)_/', $file, $m)) {
          $group = ucfirst($m[1]);
      } else {
          $group = 'Other';
      }
      $nameKey = preg_replace(['/^card_/', '/\.php$/'], '', $file);
      $display = ucfirst(str_replace('_', ' ', $nameKey));
      $groups[$group][] = ['file' => $file, 'name' => $display];
  }
?>
<div id="card-settings-modal" class="modal hidden">
  <div class="modal-overlay" onclick="togglePreferencesModal(false)"></div>
  <div class="modal-content">
    <h3>Select Cards to Display</h3>
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
  document.querySelector('.gear-icon').addEventListener('click', () => {
    togglePreferencesModal(true);
  });
  document.querySelector('.btn-save').addEventListener('click', () => {
    const selected = Array.from(
      document.querySelectorAll('input[name="cards[]"]:checked')
    ).map(cb => cb.value);
    document.cookie = 'visible_cards=' + selected.join(',') + '; path=/; max-age=31536000';
    location.reload();
  });
  document.querySelector('.btn-cancel').addEventListener('click', () => {
    togglePreferencesModal(false);
  });
});
</script>

<style>
.modal { /* ...same as before...*/ }
.modal.hidden { display: none; }
/* rest of your modal CSS */
</style>
