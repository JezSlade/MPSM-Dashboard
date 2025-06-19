<?php declare(strict_types=1);
// /components/preferences-modal.php

// 0) Don’t render for API or any non-index page
if (
    strpos($_SERVER['REQUEST_URI'], '/api/') === 0 ||
    basename($_SERVER['SCRIPT_NAME']) !== 'index.php'
) {
    return;
}

// 1) Scan only valid card_*.php files
$cardsDir    = __DIR__ . '/../cards/';
$allFiles    = scandir($cardsDir);
$cardFiles   = array_filter($allFiles, fn($f) =>
    str_starts_with($f, 'card_') && pathinfo($f, PATHINFO_EXTENSION)==='php'
);

// 2) Load user’s current visibility setting
$visibleCards = [];
if (!empty($_COOKIE['visible_cards'])) {
    $visibleCards = explode(',', $_COOKIE['visible_cards']);
}

// 3) Humanize names
$list = [];
foreach ($cardFiles as $file) {
    $key = preg_replace(['/^card_/', '/\.php$/'], '', $file);
    if (str_starts_with($key, 'get_')) {
        $key = substr($key, 4);
    }
    $name = ucfirst(str_replace('_', ' ', $key));
    $list[] = ['file'=>$file, 'name'=>$name];
}

// 4) Split into 3 columns
$total   = count($list);
$perCol  = (int) ceil($total / 3);
$columns = array_chunk($list, $perCol);
?>
<div id="preferences-modal" class="modal hidden">
  <div class="modal-backdrop" onclick="togglePreferencesModal(false)"></div>
  <div class="modal-dialog glass-card">
    <h3 style="color:var(--text-light);">Select Cards to Display</h3>

    <div class="modal-grid-multi">
      <?php foreach ($columns as $col): ?>
        <ul class="modal-column">
          <?php foreach ($col as $item): ?>
            <li>
              <label style="color:var(--text-light);">
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
      <!-- moved Select/Clear here -->
      <button id="select-all" class="btn small">Select All</button>
      <button id="clear-all"  class="btn small">Clear All</button>
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
document.addEventListener('DOMContentLoaded', ()=>{
  document.querySelector('.gear-icon')
          .addEventListener('click', ()=>togglePreferencesModal(true));
  document.getElementById('cancel-modal')
          .addEventListener('click', ()=>togglePreferencesModal(false));

  const checkboxes = ()=>Array.from(
    document.querySelectorAll('#preferences-modal input[name="cards[]"]')
  );
  document.getElementById('select-all')
          .addEventListener('click', ()=>checkboxes().forEach(cb=>cb.checked=true));
  document.getElementById('clear-all')
          .addEventListener('click', ()=>checkboxes().forEach(cb=>cb.checked=false));

  document.getElementById('save-modal')
          .addEventListener('click', ()=>{
    const sel = checkboxes().filter(cb=>cb.checked).map(cb=>cb.value);
    // always set cookie, even if empty
    document.cookie = 'visible_cards=' + sel.join(',') + '; path=/; max-age=31536000';
    togglePreferencesModal(false);
    location.reload();
  });
});
</script>

<style>
.modal {
  position: fixed; inset:0;
  display:flex; align-items:center; justify-content:center;
  z-index:10000;
}
.modal.hidden { display:none; }
.modal-backdrop {
  position:absolute; inset:0; background:rgba(0,0,0,0.5);
}
.modal-dialog {
  background:rgba(255,255,255,0.1);
  backdrop-filter:blur(10px);
  border-radius:12px;
  padding:1rem;
  width:90%; max-width:800px;
  max-height:80%; overflow:auto;
  box-shadow:0 8px 32px rgba(0,0,0,0.2);
}
.modal-grid-multi {
  display:flex; gap:1rem; margin-bottom:1rem;
}
.modal-column {
  list-style:none; padding:0; margin:0; flex:1;
}
.modal-column li { margin:0.25rem 0; }
.modal-actions {
  text-align:right; display:flex; gap:0.5rem; flex-wrap:wrap;
}
.btn.small {
  font-size:0.9rem; padding:0.25rem 0.5rem;
}
</style>
