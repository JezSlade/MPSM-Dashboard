<?php declare(strict_types=1);
// /views/view-preferences-card.php

// 1) Scan card files
$files = glob(__DIR__ . '/../cards/card_*.php');
$cards = [];
foreach ($files as $file) {
    $fname = basename($file);
    // derive display name from filename
    $name = str_replace(['card_','.php','_'], ['','',' '], $fname);
    $cards[] = ['file' => $fname, 'name' => ucfirst($name)];
}

// 2) Layout in a 3-column grid so scrolling is minimized
$cols = 3;
$chunks = array_chunk($cards, $cols);
?>
<div class="modal-content">
  <h3>Select Cards to Display</h3>
  <table class="preferences-table" style="width:100%; border-spacing:1rem;">
    <tbody>
      <?php foreach ($chunks as $row): ?>
        <tr>
          <?php foreach ($row as $item): ?>
            <td>
              <label style="display:block; user-select:none;">
                <input
                  type="checkbox"
                  name="cards[]"
                  value="<?= htmlspecialchars($item['file']) ?>"
                >
                <?= htmlspecialchars($item['name']) ?>
              </label>
            </td>
          <?php endforeach; ?>
          <?php for ($i = count($row); $i < $cols; $i++): ?>
            <td></td>
          <?php endfor; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <button class="btn-save">Save Preferences</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // 3) Load existing prefs from localStorage
  const stored = JSON.parse(localStorage.getItem('viewPreferences') || '[]');
  document.querySelectorAll('input[name="cards[]"]').forEach(chk => {
    if (stored.includes(chk.value)) chk.checked = true;
  });

  // 4) Save button behavior
  document.querySelector('.btn-save').addEventListener('click', () => {
    const selected = Array.from(
      document.querySelectorAll('input[name="cards[]"]:checked')
    ).map(chk => chk.value);
    localStorage.setItem('viewPreferences', JSON.stringify(selected));
    location.reload();
  });
});
</script>
